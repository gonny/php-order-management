# Application
- Laravel 12 + Inertia 2 + Svelte 5 with runes and correct reactivity patterns + TailwindCSS 4
- NodeJS 22 with Yarn 1
- PHP >=8.3
- MySQL >=5.6

## How to run in dev mode
- Application frontend is secured by Laravel Sanctum. You have to have allowed domain in `SANCTUM_STATEFUL_DOMAINS`. Eg: `localhost:8080,localhost:5173`.
- Run database migrations and seeders: `php artisan migrate:fresh --seed`. This will also create test user 'text@example.com' with password 'Passw0rd1!'.
- Run application backend: `php artisan serve -host=127.0.0.1  --port=8000`
- Run application frontend: `npm run dev --host`

# Testing and code style
## Testing and codestyle stack
- Backend: `PHPUnit`, `Laravel Pint`
- Frontend: `Vitest`, `svelte-check`, `eslint`, `playwright`

## Backend testing standard instructions
- Every PR should have tests for new features and bug fixes.
- Backend uses `PHPUnit` for testing.
- Backend uses `Laravel Pint` for code style.
- Run tests: `php artisan test`
- Run code style check: `./vendor/bin/pint --test`

## Frontend testing standard instructions
- Every PR should have tests for new features and bug fixes.
- Frontend uses `Vitest` for unit testing.
- Frontend uses `Playwright` for end-to-end testing.
- Frontend uses `svelte-check` for Svelte specific code analysis.
- Frontend uses `eslint` for code style.
- Run unit tests: `npm run test:run`
- Run end-to-end tests: `npm run test:e2e`
- Run Svelte code analysis: `npm svelte-check`
- Run eslint code style check: `npm run lint`

# Naming conventions
- Name all functions and methods so it is clear what they do.
- Name boolean functions and methods so it is clear they return boolean. Eg: `isUser`, `hasItems`, `canEditPost`, `showMenu`
