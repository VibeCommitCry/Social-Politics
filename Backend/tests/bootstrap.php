<?php

declare(strict_types=1);

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

new Dotenv()->bootEnv(dirname(__DIR__) . '/.env');

if ($_SERVER['APP_DEBUG']) {
    umask(0o000);
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

    echo "[1/3] Dropping test database...\n";
    runCommand($application, 'doctrine:database:drop', ['--force' => true, '--if-exists' => true]);
    echo "      Done.\n";

    echo "[2/3] Creating fresh test database...\n";
    runCommand($application, 'doctrine:database:create', []);
    echo "      Done.\n";

    echo "[3/3] Creating schema...\n";
    runCommand($application, 'doctrine:schema:create', []);
    echo "      Done.\n";

    echo "=== Bootstrap complete, running tests ===\n\n";

    $kernel->shutdown();
}

/**
 * @param array<string, mixed> $params
 */
function runCommand(Application $application, string $name, array $params): void
{
    $input = new ArrayInput(array_merge(['command' => $name], $params));
    $output = new BufferedOutput();

    $exitCode = $application->run($input, $output);

    if (0 !== $exitCode) {
        throw new \RuntimeException(sprintf('Command "%s" failed: %s', $name, $output->fetch()));
    }
}
