# 🚀 Production Deployment Guide — Laravel Reverb (Real-time Chat)

Complete guide to deploy the real-time chat feature (assignment discussion) using Laravel Reverb on a production server.

---

## Prerequisites

- VPS / Cloud Server (Ubuntu 20.04+ recommended)
- PHP 8.2+
- Nginx / Apache
- MySQL
- Node.js 18+
- Supervisor (for daemon processes)

---

## 1. Environment Configuration (`.env`)

```env
# Broadcast driver MUST be reverb
BROADCAST_CONNECTION=reverb

# Queue MUST be active (events are dispatched via queue)
QUEUE_CONNECTION=database

# Reverb Configuration
REVERB_APP_ID=laravel-elearning
REVERB_APP_KEY=your-random-secure-key         # Replace with a random key!
REVERB_APP_SECRET=your-random-secure-secret   # Replace with a random secret!
REVERB_HOST="0.0.0.0"
REVERB_PORT=8080
REVERB_SCHEME=https

# Vite (already built, but MUST be present during build time)
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="yourdomain.com"             # Your production domain
VITE_REVERB_PORT=443                          # Client-facing port (via Nginx proxy)
VITE_REVERB_SCHEME="https"
```

> **⚠️ IMPORTANT:**
> - Generate `REVERB_APP_KEY` and `REVERB_APP_SECRET` with secure random strings
> - `VITE_REVERB_HOST` must be set to your production domain (not `localhost`)
> - `VITE_REVERB_PORT` = `443` when using Nginx reverse proxy with SSL

---

## 2. Build Frontend Assets

```bash
npm install
npm run build
```

> `VITE_REVERB_*` variables **must be correctly set** before building, as their values are embedded into the JavaScript bundle at build time.

---

## 3. Supervisor Configuration

Create 2 Supervisor configuration files:

### a. Queue Worker (`/etc/supervisor/conf.d/laravel-queue.conf`)

```ini
[program:laravel-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/laravel-elearning/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/laravel-elearning/storage/logs/queue.log
stopwaitsecs=3600
```

### b. Reverb WebSocket Server (`/etc/supervisor/conf.d/laravel-reverb.conf`)

```ini
[program:laravel-reverb]
process_name=%(program_name)s
command=php /path/to/laravel-elearning/artisan reverb:start --host=0.0.0.0 --port=8080
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/laravel-elearning/storage/logs/reverb.log
stopwaitsecs=10
```

### Enable Supervisor

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-queue:*
sudo supervisorctl start laravel-reverb
```

### Check Status

```bash
sudo supervisorctl status
```

Expected output:
```
laravel-queue:laravel-queue_00   RUNNING   pid 12345, uptime 0:05:00
laravel-reverb                   RUNNING   pid 12346, uptime 0:05:00
```

---

## 4. Nginx Reverse Proxy (WebSocket)

Add this configuration to your Nginx server block to **proxy WebSocket connections**:

```nginx
server {
    listen 443 ssl;
    server_name yourdomain.com;

    # ... existing SSL & Laravel config ...

    # WebSocket Reverse Proxy for Reverb
    location /app {
        proxy_http_version 1.1;
        proxy_set_header Host $http_host;
        proxy_set_header Scheme $scheme;
        proxy_set_header SERVER_PORT $server_port;
        proxy_set_header REMOTE_ADDR $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";

        proxy_pass http://127.0.0.1:8080;
    }

    # Broadcasting Auth Endpoint (handled by Laravel)
    # Make sure /broadcasting/auth is accessible (already covered by standard Laravel config)
}
```

Then restart Nginx:

```bash
sudo nginx -t
sudo systemctl reload nginx
```

---

## 5. Deploy Checklist ✅

On every deployment, run these commands in order:

```bash
# 1. Pull latest code
git pull origin main

# 2. Install PHP dependencies
composer install --no-dev --optimize-autoloader

# 3. Install & build frontend (MAKE SURE .env VITE_REVERB_* values are correct)
npm install
npm run build

# 4. Laravel optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 5. Migrate database (if applicable)
php artisan migrate --force

# 6. Restart queue & reverb
sudo supervisorctl restart laravel-queue:*
sudo supervisorctl restart laravel-reverb
```

---

## 6. Monitoring & Troubleshooting

### Check Reverb Logs

```bash
tail -f /path/to/laravel-elearning/storage/logs/reverb.log
```

### Check Queue Logs

```bash
tail -f /path/to/laravel-elearning/storage/logs/queue.log
```

### Check if Reverb is Running

```bash
sudo supervisorctl status laravel-reverb
# or
ss -tlnp | grep 8080
```

### Common Issues

| Symptom | Cause | Solution |
|---------|-------|----------|
| Chat is not real-time | Queue worker is not running | `supervisorctl restart laravel-queue:*` |
| WebSocket error in browser console | Reverb is not running or Nginx proxy not configured | Check `supervisorctl status` + Nginx config |
| 403 on channel auth | User is not authorized | Check logic in `routes/channels.php` |
| Message appears for sender but not receiver | Queue worker is not processing events | Check `storage/logs/queue.log` |

---

## Required Processes in Production

| # | Process | Command | Managed By |
|---|---------|---------|------------|
| 1 | **Web Server** | — | Nginx + PHP-FPM |
| 2 | **Queue Worker** | `php artisan queue:work` | Supervisor |
| 3 | **Reverb WebSocket** | `php artisan reverb:start` | Supervisor |

> ⚠️ Without a **Queue Worker**, broadcast events will not be sent.
> Without **Reverb**, WebSocket will not be available and chat will not work in real-time.
