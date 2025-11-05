<?php
declare(strict_types=1);

namespace App;

use PDO;

final class DW
{
    private PDO $pdo;

    public function __construct()
    {
        $host = getenv('DW_HOST') ?: 'postgres-dw';
        $port = getenv('DW_PORT') ?: '5432';
        $db   = getenv('DW_DB') ?: 'BALONCESTO_DW';
        $user = getenv('DW_USER') ?: 'dw_report';
        $pass = getenv('DW_PASSWORD') ?: 'dw_report_2025!';

        $dsn = "pgsql:host=$host;port=$port;dbname=$db";
        $this->pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    public function equipos(): array
    {
        $sql = "SELECT sk_equipo, nk_equipo_id, nombre, ciudad, codigo, activo
                FROM dw.dim_equipo WHERE activo = TRUE ORDER BY nombre";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function jugadoresPorEquipo(int $nk_equipo_id): array
    {
        $sql = "SELECT j.sk_jugador, j.nk_jugador_id, j.nombre, j.apellido, j.numero, j.posicion,
                       j.edad, j.estatura_cm, j.nacionalidad, j.equipo_actual
                FROM dw.dim_jugador j
                JOIN dw.dim_equipo e ON e.nombre = j.equipo_actual
                WHERE e.nk_equipo_id = :nk
                ORDER BY j.apellido, j.nombre";
        $st = $this->pdo->prepare($sql);
        $st->execute([':nk' => $nk_equipo_id]);
        return $st->fetchAll();
    }

    public function historialPartidos(?string $desde, ?string $hasta): array
    {
        $sql = "SELECT dp.sk_partido, dp.nk_partido_id, dp.fecha_hora, dp.equipo_local, dp.equipo_visita,
                       dp.localidad, dp.torneo, dp.temporada,
                       COALESCE(SUM(CASE WHEN de.nombre = dp.equipo_local  THEN fa.puntos END),0) AS puntos_local,
                       COALESCE(SUM(CASE WHEN de.nombre = dp.equipo_visita THEN fa.puntos END),0) AS puntos_visita
                FROM dw.dim_partido dp
                LEFT JOIN dw.fact_anotacion fa ON fa.sk_partido = dp.sk_partido
                LEFT JOIN dw.dim_equipo de     ON de.sk_equipo   = fa.sk_equipo
                WHERE (:d IS NULL OR dp.fecha_hora >= :d) AND (:h IS NULL OR dp.fecha_hora <= :h)
                GROUP BY dp.sk_partido, dp.nk_partido_id, dp.fecha_hora, dp.equipo_local, dp.equipo_visita,
                         dp.localidad, dp.torneo, dp.temporada
                ORDER BY dp.fecha_hora DESC";
        $st = $this->pdo->prepare($sql);
        $st->execute([':d' => $desde, ':h' => $hasta]);
        return $st->fetchAll();
    }

    public function rosterPorPartido(int $nk_partido_id): array
    {
        $sql = "WITH part AS (
                  SELECT sk_partido FROM dw.dim_partido WHERE nk_partido_id = :nk
                ), jugadores_invol AS (
                  SELECT DISTINCT dj.sk_jugador, dj.nombre, dj.apellido, de.nombre AS equipo
                  FROM dw.fact_anotacion fa
                  JOIN part p ON p.sk_partido = fa.sk_partido
                  JOIN dw.dim_jugador dj ON dj.sk_jugador = fa.sk_jugador
                  JOIN dw.dim_equipo de  ON de.sk_equipo  = fa.sk_equipo
                  UNION
                  SELECT DISTINCT dj.sk_jugador, dj.nombre, dj.apellido, de.nombre AS equipo
                  FROM dw.fact_falta ff
                  JOIN part p ON p.sk_partido = ff.sk_partido
                  JOIN dw.dim_jugador dj ON dj.sk_jugador = ff.sk_jugador
                  JOIN dw.dim_equipo de  ON de.sk_equipo  = ff.sk_equipo
                )
                SELECT * FROM jugadores_invol ORDER BY equipo, apellido, nombre";
        $st = $this->pdo->prepare($sql);
        $st->execute([':nk' => $nk_partido_id]);
        return $st->fetchAll();
    }
}
