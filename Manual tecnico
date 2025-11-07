### Manual Tecnico
### Tablero Report Service (PHP) 
Tablero Report Service es un microservicio en PHP 8+ que genera reportes obtenidos desde el Data Warehouse (DW) cargado por el sistema ETL.
Su función principal es:
    • Leer datos desde PostgreSQL (DW).
    • Aplicar filtros de fechas, equipos, localidades, etc.
    • Cachear resultados en Redis para acelerar la reportería.
    • Servir endpoints consumidos por el Front y el Admin Panel.
    • Exponerse detrás de Caddy con TLS automático (Let’s Encrypt).
    • Este servicio forma parte del ecosistema de reporterías y analítica del proyecto.

### Estructura del Repositorio
tablero-report-service/
├── public/
│   └── index.php             # Punto de entrada principal (Front Controller)
│
├── src/
│   ├── App.php               # Clase central del servicio
│   ├── Auth.php              # Autenticación interna / tokens
│   ├── Cache.php             # Integración con Redis
│   ├── DW.php                # Conexión y consultas al Data Warehouse (PostgreSQL)
│   └── Routes.php            # Rutas expuestas por el microservicio
│
├── .env                      # Variables de entorno del servicio
├── .env.example              # Plantilla de configuración
│
├── Dockerfile                # Imagen Docker del microservicio
├── docker-compose.yml        # Orquestación local
│
├── composer.json             # Dependencias PHP
└── README.md                 # Documentación del proyecto

### Diagrama de arquitectura
flowchart LR
    Client[Frontend / Admin] --> PHP[Tablero Report Service (PHP)]
    PHP --> Cache[Redis Cache]
    PHP --> DW[(PostgreSQL Data Warehouse)]
    DW --> ETL[ETL Stack<br/>Replicator + Transform]
    PHP --> Caddy[Caddy Reverse Proxy<br/>TLS]

### Detalle de microservicios y lenguajes
Lenguaje
    • PHP 8+
Infraestructura
    • Docker
    • Docker Compose
    • Caddy Server (TLS automático)
    • Redis (Cache)
    • PostgreSQL (DW — Data Warehouse)
Principales librerías (composer.json)
| Librería                     | Uso                   |
| ---------------------------- | --------------------- |
| `ext-pdo`                    | Conexión a PostgreSQL |
| `predis/predis`              | Cliente Redis         |
| `vlucas/phpdotenv`           | Manejo de `.env`      |
| `nikic/fast-route` o similar | Ruteo rápido          |
| Utilidades internas          | App, Auth, DW, Cache  |

### Cómo levantar el sistema localmente 
- Requisitos previos
Docker
Red compartida: analytics-net
ETL Stack levantado (Postgres DW + Redis)
- Crear red necesaria
docker network create analytics-net || true
- Levantar el servicio
docker compose up -d --build
- Ver logs
docker logs -f report-service

### Especificación de endpoints por microservicio
- POST /Reporte/HistorialPartidos
Obtiene historial de partidos dentro de un rango de fechas.
- Request
{
  "desde": "2025-10-01 00:00:00",
  "hasta": "2025-10-29 23:59:59"
}
- Curl
curl -X POST http://localhost:5001/Reporte/HistorialPartidos \
-H "Content-Type: application/json" \
-d '{"desde":"2025-10-01 00:00:00","hasta":"2025-10-29 23:59:59"}'
- Respuesta
JSON con KPIs del partido
Resultados cacheados en Redis
- GET /health
Devuelve estado de salud del servicio.
- Internos (
/Reporte/EquipoEstadisticas
/Reporte/LocalidadResumen
/Reporte/PartidosEnVivo
/Reporte/Partido/{id}

## Flujo de Funcionamiento
 - Cliente Front/Admin hace petición a /Reporte/....
 - Routes.php identifica la ruta.
 - App.php resuelve dependencias y despacha la acción.
 - Auth.php valida (si aplica) token API o JWT.
 - Cache.php revisa Redis:
        Si existe → retorna datos cacheados.
        Si no existe → consulta al DW.
 - DW.php ejecuta la query en PostgreSQL.
 - El resultado:
        Se guarda en Redis.
        Se devuelve al cliente.

### Seguridad
Caddy proporciona:
    • TLS Let's Encrypt
    • HSTS
    • CSP
    • X-Forwarded-Proto
    • Los microservicios internos usan red segura analytics-net.
    • Redis no expone puertos a Internet.
    • DW solo acepta conexiones desde contenedores autorizados.

### Posibles errores y soluciones
-Error: "No connection to PostgreSQL"
-Solución:
  Revisar DW_HOST, DW_USER, DW_PASSWORD
  Verificar que el etl-postgres está activo
-Error: “Redis connection refused”
-Solución:
  Asegurar que Redis esté dentro de analytics-net
  Verificar puerto: 6379
-Error: Respuesta muy lenta
-Posibles causas:
  Faltan índices en DW
  Cache no habilitado
  Muchos registros sin filtros
-Error: “Route not found”
-Solución:
  Revisar Routes.php
  Confirmar método POST / GET correcto
-Error: “Invalid date format”
-Solución:
  Enviar fechas con formato:
  YYYY-MM-DD HH:MM:SS

## Producción (VPS)
cd vps
cp .env.vps.example .env.vps
docker compose --env-file .env.vps up -d
-Se recomienda usar:
Fail2ban
Backups automáticos con cron
Logs rotados
