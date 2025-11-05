# Tablero Report Service (PHP)

Lee del DW (PostgreSQL) y cachea en Redis. Expuesto por Caddy con TLS.

## Requisitos
- Red Docker externa: `analytics-net`
- `etl-stack` levantado (Postgres DW + Redis + Replicator)

## Desarrollo local
docker network create analytics-net || true
docker compose up -d --build

# Probar
curl -X POST http://localhost:5001/Reporte/HistorialPartidos \
  -H "Content-Type: application/json" \
  -d '{"desde":"2025-10-01 00:00:00","hasta":"2025-10-29 23:59:59"}'

## Producción (VPS)
cd vps
cp .env.vps.example .env.vps # (si tuvieras example)
docker compose --env-file .env.vps up -d
