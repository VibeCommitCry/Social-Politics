<?php

declare(strict_types=1);

// Gives PHPStan's Doctrine extension access to the entity metadata.

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

new Dotenv()->bootEnv(dirname(__DIR__) . '/.env');

/** @var string $environment */
$environment = $_SERVER['APP_ENV'];
$kernel = new Kernel($environment, (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

return $kernel->getContainer()->get('doctrine')->getManager();
