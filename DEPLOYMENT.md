# Noras Deployment Guide

## Prerequisites

- Frontend: Vercel account
- Backend: cPanel hosting with PHP 8.2+, MySQL 8.0+, Composer
- Domain names configured

## Environment Variables

### Frontend (.env.production)

```
VITE_API_BASE_URL=https://api.noras.example.com/api/v1
```

### Backend (.env)

**Required variables:**
- `APP_KEY` - Generate with `php artisan key:generate`
- `APP_URL` - Your backend domain
- `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` - Database credentials
- `SANCTUM_STATEFUL_DOMAINS` - Your Vercel frontend domain
- `SESSION_DOMAIN` - Your backend domain
- `GEMINI_API_KEY` or `GROQ_API_KEY` - AI provider credentials

## Frontend Deployment (Vercel)

1. **Connect Repository:**
   ```bash
   cd Client
   vercel login
   vercel link
   ```

2. **Configure Build Settings:**
   - Framework Preset: Vite
   - Build Command: `npm run build`
   - Output Directory: `dist`
   - Install Command: `npm install`

3. **Set Environment Variables:**
   - Go to Project Settings → Environment Variables
   - Add `VITE_API_BASE_URL` with your backend API URL

4. **Deploy:**
   ```bash
   vercel --prod
   ```

## Backend Deployment (cPanel)

### 1. Upload Files

Upload the entire `Server` directory via FTP/SFTP or File Manager.

### 2. Configure Environment

```bash
cd /path/to/Server
cp .env.example .env
nano .env
```

Update these critical settings:
- Set `APP_ENV=production`
- Set `APP_DEBUG=false`
- Configure database credentials
- Set `APP_URL` to your domain
- Configure `SANCTUM_STATEFUL_DOMAINS`
- Add AI provider keys

### 3. Install Dependencies

```bash
composer install --optimize-autoloader --no-dev
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Run Migrations

```bash
php artisan migrate --force
```

### 6. Optimize for Production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 7. Set Permissions

```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 8. Configure Web Server

**Apache (.htaccess already included)**

Point document root to `/public` directory.

**Nginx:**
```nginx
server {
    listen 80;
    server_name api.noras.example.com;
    root /path/to/Server/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \\.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\\.(?!well-known).* {
        deny all;
    }
}
```

## Post-Deployment Verification

### 1. Health Check

```bash
curl https://api.noras.example.com/api/v1/health
```

Expected response:
```json
{
  "status": "ok",
  "message": "Application is running",
  "database": "connected"
}
```

### 2. Create Admin User

```bash
php artisan tinker
```

```php
$admin = User::create([
    'name' => 'Admin User',
    'email' => 'admin@noras.example.com',
    'password' => Hash::make('secure-password-here'),
    'student_id' => 'ADMIN001',
    'role' => 'admin',
    'account_status' => 'approved',
    'approved_at' => now(),
]);
```

### 3. Test Registration

Visit your frontend at `https://your-app.vercel.app/register` and create a test student account.

### 4. Test Login

1. Login as admin
2. Approve the pending student
3. Login as the approved student
4. Verify dashboard access

## Security Checklist

- [ ] `APP_DEBUG=false` in production
- [ ] Strong `APP_KEY` generated
- [ ] HTTPS enabled on both domains
- [ ] Database credentials secured
- [ ] `.env` file not publicly accessible
- [ ] File upload validation enabled
- [ ] Rate limiting configured
- [ ] CORS properly configured
- [ ] Session cookies secure (HTTPS only)
- [ ] Regular database backups scheduled

## Troubleshooting

### CORS Issues

If frontend cannot connect to backend:
1. Check `SANCTUM_STATEFUL_DOMAINS` includes your Vercel domain
2. Clear config cache: `php artisan config:clear`
3. Verify HTTPS is enabled

### 401 Unauthorized

- Clear browser cookies/localStorage
- Verify token is being sent in Authorization header
- Check API_BASE_URL in frontend .env

### 500 Server Errors

1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify permissions on storage directory
3. Clear all caches and regenerate

### Database Connection Failed

- Verify database credentials in `.env`
- Ensure database exists
- Check database user permissions

## Maintenance

### Clear Caches

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Update Dependencies

```bash
composer update --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
```

### Database Backup

```bash
php artisan backup:run
```

Or manually:
```bash
mysqldump -u username -p database_name > backup.sql
```

## Rollback

If deployment fails:

1. **Frontend (Vercel):** Use Vercel dashboard to rollback to previous deployment
2. **Backend:**
   ```bash
   php artisan migrate:rollback
   git checkout previous-commit
   composer install --no-dev
   php artisan config:cache
   ```
