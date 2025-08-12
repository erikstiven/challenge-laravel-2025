# OlaClick Challenge Laravel 2025

Este repositorio contiene la implementación del reto técnico **OlaClick Challenge Laravel 2025**, desarrollado en **Laravel 11** y ejecutado en contenedores **Docker**.  
La solución incluye:

- Endpoints solicitados para gestión de pedidos.
- Caché de listado con TTL de **30 segundos**.
- Migraciones y seeders para datos iniciales.
- Pruebas automáticas (PHPUnit/Pest).
- Pruebas manuales realizadas en **Postman**.

---

## 🚀 Instalación y Puesta en Marcha

Sigue estos pasos para levantar el proyecto en tu entorno local usando **Docker**:

```bash
# 1) Clonar este repositorio
git clone https://github.com/erikstiven/challenge-laravel-2025.git
cd challenge-laravel-2025

# 2) Preparar el archivo de entorno
cp .env.example .env

# 3) Construir y levantar contenedores
docker compose up -d --build

# 4) Instalar dependencias dentro del contenedor
docker exec -it olaclick-api composer install

# 5) Generar key de Laravel y ejecutar migraciones + seeders
docker exec -it olaclick-api php artisan key:generate
docker exec -it olaclick-api php artisan migrate --force
docker exec -it olaclick-api php artisan db:seed --force



🧪 Ejecución de pruebas automáticas
Este proyecto incluye pruebas automáticas con Pest/PHPUnit.
Para ejecutarlas dentro del contenedor:
    docker exec -it olaclick-api php artisan test



📌 Pruebas manuales con Postman
En la carpeta postman/ se incluye la colección:

    postman/OlaClickChallenge.postman_collection.json

Pasos para usarla:

Abrir Postman.

Importar la colección (Import → seleccionar el archivo).

Configurar la variable de entorno {{base_url}} como:

    http://localhost:8000

Ejecución sugerida de endpoints:

POST /api/orders → Crear pedido.

GET /api/orders/{order_id} → Consultar pedido.

GET /api/orders → Listar pedidos.

POST /api/orders/{order_id}/advance → Avanzar estado del pedido.

Probar la cache y la eliminación tras el estado delivered.



❓ Preguntas opcionales (razonamiento)
1. ¿Cómo asegurarías que esta API escale ante alta concurrencia?
Implementaría caché distribuido (por ejemplo Redis en clúster), colas para operaciones pesadas, y balanceadores de carga horizontales para repartir peticiones.

2. ¿Qué estrategia seguirías para desacoplar la lógica del dominio de Laravel/Eloquent?
Usar patrones como Repository o Service Layer, de modo que el dominio no dependa directamente de Eloquent. Esto facilita cambios de ORM o incluso migrar a otra arquitectura.

3. ¿Cómo manejarías versiones de la API en producción?
Versionar en las rutas (/api/v1/...), mantener soporte a versiones anteriores por un tiempo definido y documentar claramente los cambios para clientes.

