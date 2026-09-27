<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

if (($_SERVER['APP_ENV'] ?? null) === 'test') {
    refreshTestDatabase();
}

function refreshTestDatabase(): void
{
    echo "\n=== Test database bootstrap ===\n";

    $kernel = new \App\Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
    $kernel->boot();

    $application = new Application($kernel);
    $application->setAutoExit(false);

    echo "[1/4] Dropping test database...\n";
    runCommand($application, 'doctrine:database:drop', ['--force' => true, '--if-exists' => true]);
    echo "      Done.\n";

    echo "[2/4] Creating fresh test database...\n";
    runCommand($application, 'doctrine:database:create', []);
    echo "      Done.\n";

    echo "[3/4] Creating schema...\n";
    runCommand($application, 'doctrine:schema:create', []);
    echo "      Done.\n";

    echo "[4/4] Copying data from source...\n";
    copyDataFromSource($kernel);
    echo "      Done.\n";

    echo "=== Bootstrap complete, running tests ===\n\n";

    $kernel->shutdown();
}

function runCommand(Application $application, string $name, array $params): void
{
    $input = new ArrayInput(array_merge(['command' => $name], $params));
    $output = new BufferedOutput();

    $exitCode = $application->run($input, $output);

    if (0 !== $exitCode) {
        throw new \RuntimeException(sprintf('Command "%s" failed: %s', $name, $output->fetch()));
    }
}

function copyDataFromSource(\App\Kernel $kernel): void
{
    $sourceUrl = $_SERVER['SOURCE_DATABASE_URL'] ?? null;

    if ($sourceUrl === null) {
        echo "      No SOURCE_DATABASE_URL configured, skipping copy.\n";
        return;
    }

    /** @var \Doctrine\ORM\EntityManagerInterface $entityManager */
    $entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    $targetConnection = $entityManager->getConnection();

    $dsnParser = new DsnParser(['mysql' => 'pdo_mysql']);
    $sourceParams = $dsnParser->parse($sourceUrl);
    $sourceConnection = DriverManager::getConnection($sourceParams);

    $tables = $targetConnection->createSchemaManager()->listTableNames();

    $targetConnection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');

    foreach ($tables as $table) {
        if ($table === 'doctrine_migration_versions') {
            continue;
        }

        $targetConnection->executeStatement(sprintf('TRUNCATE TABLE `%s`', $table));

        $rows = $sourceConnection->fetchAllAssociative(sprintf('SELECT * FROM `%s`', $table));

        foreach ($rows as $row) {
            $targetConnection->insert($table, $row);
        }

        echo sprintf("      %-30s %d row(s)\n", $table, count($rows));
    }

    $targetConnection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
}