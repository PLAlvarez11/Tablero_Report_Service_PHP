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

        $allowed = explode(',', getenv('CORS_ALLOWED_ORIGINS') ?: 'preguntarVictor por el dominio');
        $app->add(function ($req, $handler) use ($allowed) {
            $origin = $req->getHeaderLine('Origin');
            $res = $handler->handle($req);
            if ($origin && in_array($origin, $allowed, true)) {
                return $res
                    ->withHeader('Access-Control-Allow-Origin', $origin)
                    ->withHeader('Vary', 'Origin')
                    ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
                    ->withHeader('Access-Control-Allow-Methods', 'POST,GET,OPTIONS');
            }
            return $res;
        });

        (new Routes())->register($app);
        return $app;
    }
}
