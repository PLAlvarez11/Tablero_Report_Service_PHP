<?php
declare(strict_types=1);

namespace App;

use Slim\Factory\AppFactory;
use Dotenv\Dotenv;

final class App
{
    public function bootstrap(): \Slim\App
    {
        if (file_exists(__DIR__ . '/../.env')) {
            Dotenv::createImmutable(__DIR__ . '/..')->load();
        }

        $app = AppFactory::create();
        // CORS sencillo para pruebeseillas
        $app->add(function ($req, $handler) {
            $res = $handler->handle($req);
            return $res
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
                ->withHeader('Access-Control-Allow-Methods', 'GET,POST,OPTIONS');
        });

        (new Routes())->register($app);
        return $app;
    }
}
