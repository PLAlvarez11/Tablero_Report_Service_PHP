<?php
declare(strict_types=1);
namespace App;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Resp;
use Psr\Http\Message\ServerRequestInterface as Req;

final class Auth {
  private string $secret;
  private array $roles;

  public function __construct(array $roles = []) {
    $this->secret = getenv('JWT_SECRET') ?: 'change_me';
    $this->roles = $roles;
  }

  public function __invoke(Req $req, Resp $res, callable $next): Resp {
    $auth = $req->getHeaderLine('Authorization');
    if (!str_starts_with($auth, 'Bearer ')) {
      $res->getBody()->write(json_encode(['error'=>'Missing bearer token']));
      return $res->withStatus(401)->withHeader('Content-Type','application/json');
    }
    $token = substr($auth, 7);
    try {
      $payload = JWT::decode($token, new Key($this->secret, 'HS256'));
      if (!empty($this->roles)) {
        $userRoles = $payload->roles ?? [];
        if (count(array_intersect($this->roles, $userRoles)) === 0) {
          $res->getBody()->write(json_encode(['error'=>'Forbidden']));
          return $res->withStatus(403)->withHeader('Content-Type','application/json');
        }
      }
      $req = $req->withAttribute('jwt', $payload);
      return $next($req, $res);
    } catch (\Throwable $e) {
      $res->getBody()->write(json_encode(['error'=>'Invalid token']));
      return $res->withStatus(401)->withHeader('Content-Type','application/json');
    }
  }
}
