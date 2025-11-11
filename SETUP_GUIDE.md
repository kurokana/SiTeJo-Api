# 🚀 Quick Start Guide - Ticketing System Backend

## Langkah-langkah Setup

### 1. Install Laravel Sanctum
```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### 2. Setup Database
Edit file `.env` dan sesuaikan konfigurasi database:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ticketing_system
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Generate Application Key
```bash
php artisan key:generate
```

### 4. Run Migrations
```bash
php artisan migrate
```

### 5. Seed Database dengan Data Dummy
```bash
php artisan db:seed
```

### 6. Create Storage Link
```bash
php artisan storage:link
```

### 7. Run Development Server
```bash
php artisan serve
```

Server akan berjalan di: **http://localhost:8000**

## 📝 Akun Default untuk Testing

Setelah menjalankan seeder, gunakan akun berikut:

### Admin
- Email: `admin@example.com`
- Password: `password`

### Dosen
- Email: `ahmad.sudirman@example.com`
- Password: `password`

### Mahasiswa
- Email: `andi.pratama@student.example.com`
- Password: `password`

## 🧪 Testing API

### Menggunakan Postman
1. Import file `Ticketing_System_API.postman_collection.json`
2. Test endpoint Login untuk mendapatkan token
3. Token akan otomatis tersimpan di collection variable
4. Test endpoint lainnya

### Menggunakan curl

**Login:**
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"andi.pratama@student.example.com\",\"password\":\"password\"}"
```

**Get Tickets:**
```bash
curl -X GET http://localhost:8000/api/tickets \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## 📋 Structure Overview

```
database/migrations/
├── 0001_01_01_000000_create_users_table.php (updated)
├── 2024_11_11_000001_create_tickets_table.php
├── 2024_11_11_000002_create_documents_table.php
└── 2024_11_11_000003_create_ticket_histories_table.php

app/Models/
├── User.php (updated with relationships)
├── Ticket.php
├── Document.php
└── TicketHistory.php

app/Http/Controllers/Api/
├── AuthController.php
├── TicketController.php
└── DocumentController.php

app/Http/Middleware/
└── CheckRole.php

routes/
└── api.php

config/
├── cors.php
└── sanctum.php
```

## 🔗 Next Steps

1. **Testing**: Test semua endpoint menggunakan Postman
2. **React Integration**: Lihat file `REACT_INTEGRATION.md` untuk integrasi dengan React
3. **API Documentation**: Lihat file `API_DOCUMENTATION.md` untuk dokumentasi lengkap

## ⚙️ Configuration Notes

- **CORS**: Sudah dikonfigurasi untuk accept all origins (ubah di production)
- **Sanctum**: Token-based authentication
- **File Upload**: Max 10MB, tersimpan di `storage/app/public/documents`
- **Pagination**: Default 15 items per page

## 🐛 Troubleshooting

### Error: "Base table or view not found"
```bash
php artisan migrate:fresh --seed
```

### Error: "The storage link already exists"
```bash
# Hapus link yang ada terlebih dahulu
rm public/storage
php artisan storage:link
```

### Error: "Class 'Laravel\Sanctum\...' not found"
```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### CORS Error dari React
Pastikan file `config/cors.php` sudah benar dan tambahkan:
```env
SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000
```

## 📚 Documentation Files

- `API_DOCUMENTATION.md` - Complete API documentation
- `REACT_INTEGRATION.md` - React integration guide
- `Ticketing_System_API.postman_collection.json` - Postman collection

Selamat mengoding! 🎉
