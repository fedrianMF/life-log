# Despliegue en Dokploy

La imagen contiene Laravel, Nginx y PHP-FPM. PostgreSQL se crea como un servicio **Database** separado en Dokploy, con volumen y copias de seguridad. Se recomienda nombrar ese servicio `life-log-postgres`; ese es el nombre interno usado en `DB_HOST`.

Configurar estas variables en la aplicación (sustituir dominio, credenciales y secretos por sus valores reales):

Generar `APP_KEY` con `php artisan key:generate --show` y pegar el valor `base64:...` completo.

```dotenv
APP_NAME=Life-Log
APP_ENV=production
APP_KEY=base64:<clave-generada>
APP_DEBUG=false
APP_URL=https://<domain>
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US
LOG_CHANNEL=stderr
LOG_LEVEL=warning
DB_CONNECTION=pgsql
DB_HOST=life-log-postgres
DB_PORT=5432
DB_DATABASE=Life-Log_database
DB_USERNAME=life_log
DB_PASSWORD=<contraseña-del-servicio-postgres>
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
GOOGLE_CLIENT_ID=<id-del-cliente-google-de-produccion>
GOOGLE_CLIENT_SECRET=<secreto-del-cliente-google-de-produccion>
GOOGLE_REDIRECT_URI=https://<domain>/auth/google/callback
ALLOWED_EMAILS=<correos-permitidos-separados-por-comas>
```

Dokploy no interpola referencias como `${APP_URL}`: escribir siempre el valor HTTPS completo, especialmente en `GOOGLE_REDIRECT_URI`. Después de cambiar variables, pulsar **Redeploy** para recrear el contenedor.

En el cliente OAuth de Google de producción, registrar exactamente `https://<domain>/auth/google/callback` como URI de redirección autorizada.
