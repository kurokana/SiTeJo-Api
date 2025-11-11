# 🔧 Environment Configuration Guide

## Current Configuration (Development)

Saat ini sistem menggunakan **SQLite** untuk database (untuk kemudahan development).

## Configuration untuk Production dengan MySQL

Jika ingin menggunakan MySQL (recommended untuk production), ubah file `.env`:

### 1. Edit `.env`

```env
APP_NAME="Ticketing System"
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta
APP_URL=http://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ticketing_system
DB_USERNAME=root
DB_PASSWORD=your_password

# Sanctum Configuration
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000,your-react-domain.com

# Frontend URL (React App)
FRONTEND_URL=http://localhost:3000
```

### 2. Create Database

```sql
CREATE DATABASE ticketing_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Run Migrations

```bash
php artisan migrate:fresh --seed
```

## Environment Variables Explanation

### App Configuration
- `APP_NAME` - Nama aplikasi
- `APP_ENV` - Environment (local, staging, production)
- `APP_DEBUG` - Debug mode (true untuk development, false untuk production)
- `APP_TIMEZONE` - Timezone aplikasi
- `APP_URL` - Base URL aplikasi

### Database Configuration
- `DB_CONNECTION` - Database driver (mysql, sqlite, pgsql)
- `DB_HOST` - Database host
- `DB_PORT` - Database port (default MySQL: 3306)
- `DB_DATABASE` - Database name
- `DB_USERNAME` - Database username
- `DB_PASSWORD` - Database password

### Sanctum Configuration
- `SANCTUM_STATEFUL_DOMAINS` - Domains yang diizinkan untuk stateful authentication
  - Pisahkan dengan koma
  - Tambahkan domain React app Anda

### Frontend Configuration
- `FRONTEND_URL` - URL dari React frontend
  - Digunakan untuk CORS dan redirect URLs

## File Upload Configuration

Jika ingin mengubah max file upload size, edit `php.ini`:

```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
```

Dan restart web server Anda.

## CORS Configuration

File: `config/cors.php`

Untuk production, ubah:

```php
'allowed_origins' => [
    'http://localhost:3000',
    'https://your-react-domain.com',
],
```

Atau tetap gunakan wildcard `['*']` jika API akan diakses dari berbagai domain.

## Security Checklist untuk Production

- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_ENV=production`
- [ ] Gunakan HTTPS
- [ ] Update `SANCTUM_STATEFUL_DOMAINS` dengan domain yang benar
- [ ] Set `allowed_origins` di CORS config dengan domain spesifik
- [ ] Gunakan strong password untuk database
- [ ] Aktifkan firewall
- [ ] Regular backup database
- [ ] Set proper file permissions:
  ```bash
  chmod -R 755 storage bootstrap/cache
  ```

## Cache Configuration (Production)

Untuk performa lebih baik di production:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Jika ada perubahan config, clear cache:

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

## Queue Configuration (Optional)

Jika ingin menggunakan queue untuk email notifications atau background jobs:

```env
QUEUE_CONNECTION=database
```

Jalankan queue worker:

```bash
php artisan queue:work
```

## Current Development Setup

Untuk development saat ini (SQLite):

```env
DB_CONNECTION=sqlite
```

Database file location: `database/database.sqlite`

Ini sudah berjalan dengan baik untuk development dan testing.

## Migration Between SQLite and MySQL

Jika ingin pindah dari SQLite ke MySQL:

1. **Export data dari SQLite** (jika ada data penting)

2. **Update .env** dengan MySQL credentials

3. **Create MySQL database**
   ```bash
   mysql -u root -p
   CREATE DATABASE ticketing_system;
   exit
   ```

4. **Run fresh migrations**
   ```bash
   php artisan migrate:fresh --seed
   ```

5. **Import data** (jika ada) atau biarkan dengan seed data

## Troubleshooting

### Error: "Access denied for user"
- Periksa DB_USERNAME dan DB_PASSWORD di `.env`
- Pastikan user memiliki akses ke database

### Error: "Unknown database"
- Create database terlebih dahulu
- Pastikan DB_DATABASE di `.env` sesuai

### Error: "SQLSTATE[HY000] [2002] Connection refused"
- Pastikan MySQL service running
- Periksa DB_HOST dan DB_PORT

### CORS errors
- Periksa `config/cors.php`
- Pastikan SANCTUM_STATEFUL_DOMAINS sudah benar
- Clear config cache: `php artisan config:clear`

---

**Note**: Untuk development, SQLite sudah cukup. MySQL direkomendasikan untuk production atau jika Anda memerlukan fitur advanced database.
