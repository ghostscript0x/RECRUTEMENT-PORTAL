# FINTEC Digital Skills Academy Recruitment Portal

A Laravel application for public vacancy listings, multi-step applications, recruiter review, interview invitations, applicant email, and administrative reporting.

## Local development

Requirements: PHP 8.3+, Composer, Node.js, and npm.

1. Install PHP dependencies with `composer install`.
2. Copy `.env.example` to `.env`, then set `APP_KEY` with `php artisan key:generate`.
3. Configure the mail and Redis values if you want to test queued email delivery.
4. Run `php artisan migrate --seed`.
5. Build frontend assets with `npm ci` and `npm run build`.
6. Start the development services with `composer run dev`.

The development seeder creates reference data, demo vacancies, and a first administrator. If `ADMIN_PASSWORD` is empty outside production, it generates a random password and displays it once in the command output.

## Docker deployment

Docker Compose runs PHP-FPM, unprivileged Nginx, PostgreSQL, Redis, and a dedicated email queue worker. PostgreSQL, Redis, and uploaded files use named persistent volumes. The database and Redis ports are not published to the host.

1. Copy `.env.example` to `.env`.
2. Generate an application key with `php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"` and set `APP_KEY`.
3. Set unique `POSTGRES_PASSWORD` and `REDIS_PASSWORD` values, plus `ADMIN_EMAIL` and a strong `ADMIN_PASSWORD`.
4. Set the public `APP_URL`, mail provider credentials, and `RECRUITMENT_TIMEZONE`. Keep `SESSION_SECURE_COOKIE=true` when serving the site over HTTPS.
5. Validate the Compose configuration without printing interpolated secrets:

   ```sh
   docker compose config --quiet
   ```

6. Build and start the services:

   ```sh
   docker compose up -d --build
   ```

7. Apply schema changes explicitly; the application does not run production migrations automatically:

   ```sh
   docker compose exec app php artisan migrate --force
   docker compose exec app php artisan db:seed --force
   ```

   Production seeding creates roles, geography, email templates, and the initial administrator; it does not create sample vacancies. The first production administrator requires `ADMIN_EMAIL` and `ADMIN_PASSWORD`.

8. Check service status and the readiness endpoint:

   ```sh
   docker compose ps
   curl --fail http://localhost:8000/up
   ```

The `/up` readiness endpoint checks that Laravel has booted and that the configured database and Redis connections can be reached. The queue worker processes the `emails` queue with a 60-second timeout; the Redis retry interval is longer to avoid simultaneous job reservations.

Place the web service behind a TLS-terminating reverse proxy for public production use. Configure the proxy to forward the original scheme and client address, allow only the proxy to reach the application, and set HSTS at the TLS edge. Do not expose PostgreSQL, Redis, or PHP-FPM directly to the internet.

### Environment variables

| Variable | Purpose |
| --- | --- |
| `APP_KEY` | Laravel encryption and session key; generate a unique key and keep it stable across deployments. |
| `APP_URL` | Canonical public URL used for generated links and email. |
| `POSTGRES_DB`, `POSTGRES_USER`, `POSTGRES_PASSWORD` | Credentials for the Compose PostgreSQL service. |
| `REDIS_PASSWORD` | Password for the private Compose Redis service. |
| `ADMIN_EMAIL`, `ADMIN_PASSWORD` | Credentials for creating the first production administrator. They are only used when no administrator with that email exists. |
| `GMAIL_USERNAME`, `GMAIL_APP_PASSWORD` | Gmail SMTP credentials when `MAIL_MAILER=gmail`; use a Google App Password, not the account password. |
| `MAIL_MAILER`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` | Email transport and sender identity. |
| `RECRUITMENT_TIMEZONE` | Timezone used for interview scheduling and display; defaults to `Africa/Lagos`. |
| `SESSION_SECURE_COOKIE` | Set to `true` for HTTPS production deployments. |
| `APP_PORT` | Optional host port for the Nginx service; defaults to `8000`. |

`.env` is ignored by Git and excluded from Docker build contexts. Never pass production secrets as image build arguments or commit populated environment files.

### Backups and restore

Back up the PostgreSQL database, the `storage` volume (candidate documents), and the environment/secrets in a restricted secret manager. Keep backups encrypted and test restore procedures before relying on them.

Create a database backup:

```sh
docker compose exec -T db sh -c 'pg_dump -U "$POSTGRES_USER" "$POSTGRES_DB"' > database-backup.sql
```

Copy uploaded documents from the storage volume to the host:

```sh
docker compose cp app:/var/www/html/storage/app ./storage-app-backup
```

Restore a database backup only into the intended database:

```sh
docker compose exec -T db sh -c 'psql -U "$POSTGRES_USER" "$POSTGRES_DB"' < database-backup.sql
```

Use `docker compose cp` to restore `storage-app-backup` to `/var/www/html/storage/app` in the `app` service, then restart the app and worker. Do not use `docker compose down --volumes` for routine deployment or cleanup; it deletes persistent database, Redis, and document data.

## Verification

Run the automated suite with `php artisan test --compact` and create production assets with `npm run build`. Review container logs with `docker compose logs --tail=100 app worker web db redis`.
