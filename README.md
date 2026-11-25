# Life Log

Esqueleto de una aplicación personal para registrar gastos, caminatas, pensamientos y estado diario con captura rápida y estadísticas posteriores.

Las reglas de desarrollo están en [`AGENTS.md`](AGENTS.md). Las notas técnicas están en [`Docs/`](Docs/).

## Stack

- Laravel 13 y PHP 8.4.
- Inertia 3 y Vue 3.
- CoreUI Free 5.
- Vite 8.
- SQLite en desarrollo.

## Instalación

La sesión de creación no tuvo acceso de red. La primera instalación generará `composer.lock` y `package-lock.json`; ambos deben versionarse.

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install
npm run build
```

## Desarrollo

```bash
composer run dev
```

## Estado

Solo existe la infraestructura y una página de referencia. No hay lógica de negocio ni datos reales.
