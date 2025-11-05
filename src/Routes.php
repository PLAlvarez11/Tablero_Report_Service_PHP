<?php
declare(strict_types=1);

namespace App;

use Slim\App as SlimApp;
use Psr\Http\Message\ResponseInterface as Resp;
use Psr\Http\Message\ServerRequestInterface as Req;

final class Routes
{
    public function register(SlimApp $app): void
    {
        $app->get('/', function(Req $req, Resp $res) {
            $res->getBody()->write(json_encode(["ok"=>true,"service"=>"report-service-php","data_source"=>"DW"]));
            return $res->withHeader('Content-Type','application/json');
        });

        $app->post('/Reporte/Equipos', function(Req $req, Resp $res){
            $payload = (array) json_decode((string)$req->getBody(), true) ?: [];
            $cache = new Cache();
            if ($hit = $cache->get('pdf:reporte_equipos', $payload)) {
                $res->getBody()->write(json_encode(["cached"=>true,"data"=>$hit]));
                return $res->withHeader('Content-Type','application/json');
            }
            $dw = new DW();
            $data = $dw->equipos();
            $cache->set('pdf:reporte_equipos', $payload, $data);
            $res->getBody()->write(json_encode(["cached"=>false,"data"=>$data]));
            return $res->withHeader('Content-Type','application/json');
        });

        $app->post('/Reporte/JugadoresPorEquipo', function(Req $req, Resp $res){
            $payload = (array) json_decode((string)$req->getBody(), true) ?: [];
            $nk = (int)($payload['nk_equipo_id'] ?? 0);
            $cache = new Cache();
            if ($hit = $cache->get('pdf:jugadores_equipo', $payload)) {
                $res->getBody()->write(json_encode(["cached"=>true,"data"=>$hit]));
                return $res->withHeader('Content-Type','application/json');
            }
            $dw = new DW();
            $data = $dw->jugadoresPorEquipo($nk);
            $cache->set('pdf:jugadores_equipo', $payload, $data);
            $res->getBody()->write(json_encode(["cached"=>false,"data"=>$data]));
            return $res->withHeader('Content-Type','application/json');
        });

        $app->post('/Reporte/HistorialPartidos', function(Req $req, Resp $res){
            $payload = (array) json_decode((string)$req->getBody(), true) ?: [];
            $cache = new Cache();
            if ($hit = $cache->get('pdf:historial_partidos', $payload)) {
                $res->getBody()->write(json_encode(["cached"=>true,"data"=>$hit]));
                return $res->withHeader('Content-Type','application/json');
            }
            $dw = new DW();
            $data = $dw->historialPartidos($payload['desde'] ?? null, $payload['hasta'] ?? null);
            $cache->set('pdf:historial_partidos', $payload, $data);
            $res->getBody()->write(json_encode(["cached"=>false,"data"=>$data]));
            return $res->withHeader('Content-Type','application/json');
        });

        $app->post('/Reporte/RosterPorPartido', function(Req $req, Resp $res){
            $payload = (array) json_decode((string)$req->getBody(), true) ?: [];
            $nk = (int)($payload['nk_partido_id'] ?? 0);
            $cache = new Cache();
            if ($hit = $cache->get('pdf:roster_partido', $payload)) {
                $res->getBody()->write(json_encode(["cached"=>true,"data"=>$hit]));
                return $res->withHeader('Content-Type','application/json');
            }
            $dw = new DW();
            $data = $dw->rosterPorPartido($nk);
            $cache->set('pdf:roster_partido', $payload, $data);
            $res->getBody()->write(json_encode(["cached"=>false,"data"=>$data]));
            return $res->withHeader('Content-Type','application/json');
        });
    }
}
