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
        return $this->pdo->query("SELECT * FROM dw.v_equipo ORDER BY nombre")->fetchAll();
    }

    public function jugadoresPorEquipo(int $nk_equipo_id): array
    {
        $st = $this->pdo->prepare("SELECT * FROM dw.v_jugadores_por_equipo WHERE nk_equipo_id = :nk ORDER BY apellido, nombre");
        $st->execute([':nk' => $nk_equipo_id]);
        return $st->fetchAll();
    }

    public function historialPartidos(?string $desde, ?string $hasta): array
    {
        $sql = "SELECT * FROM dw.v_historial_partidos
                WHERE (:d IS NULL OR fecha_hora >= :d)
                  AND (:h IS NULL OR fecha_hora <= :h)
                ORDER BY fecha_hora DESC";
        $st = $this->pdo->prepare($sql);
        $st->execute([':d' => $desde, ':h' => $hasta]);
        return $st->fetchAll();
    }

    public function rosterPorPartido(int $nk_partido_id): array
    {
        $st = $this->pdo->prepare("SELECT * FROM dw.v_roster_por_partido WHERE nk_partido_id = :nk ORDER BY equipo, apellido, nombre");
        $st->execute([':nk' => $nk_partido_id]);
        return $st->fetchAll();
    }

    public function kpiTopAnotadores(?string $temporada): array
    {
        $sql = "SELECT * FROM dw.v_kpi_top_anotadores WHERE (:t IS NULL OR temporada = :t) ORDER BY total_puntos DESC";
        $st = $this->pdo->prepare($sql);
        $st->execute([':t' => $temporada]);
        return $st->fetchAll();
    }
}
