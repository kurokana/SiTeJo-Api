# Sistem Ticketing Pengajuan Tanda Tangan - Backend API

Backend API untuk sistem ticketing pengajuan tanda tangan dengan 3 role: **Mahasiswa**, **Dosen**, dan **Admin**.

## 🚀 Fitur Utama

### Role & Permissions

#### 👨‍🎓 Mahasiswa
- Register dan login
- Membuat ticket pengajuan baru
- Melihat daftar ticket milik sendiri
- Update ticket yang masih pending
- Upload dokumen lampiran
- Melihat status dan history ticket

#### 👨‍🏫 Dosen
- Login ke sistem
- Melihat ticket yang di-assign ke dosen tersebut
- Review ticket (ubah status ke in_review)
- Approve atau Reject ticket
- Menambahkan catatan untuk mahasiswa
- Upload dokumen yang sudah ditandatangani

#### 👨‍💼 Admin
- Login ke sistem
- Melihat semua ticket
- Mark ticket sebagai completed
- Menghapus ticket
- Mengelola semua dokumen

## 📋 Database Schema

### Tables
1. **users** - Data pengguna (mahasiswa, dosen, admin)
2. **tickets** - Data ticket pengajuan
3. **documents** - File attachment dan dokumen bertanda tangan
4. **ticket_histories** - Log aktivitas pada ticket

## 🛠️ Tech Stack

- **Framework**: Laravel 11
- **Authentication**: Laravel Sanctum (Token-based)
- **Database**: MySQL
- **Storage**: Laravel File Storage (public disk)

## 📦 Installation

### Prerequisites
- PHP >= 8.2
- Composer
- MySQL
- Node.js & NPM (untuk Vite)

### Setup Steps

1. **Clone repository**
```bash
git clone <repository-url>
cd Web-BE
```

2. **Install dependencies**
```bash
composer install
```

3. **Setup environment**
```bash
copy .env.example .env
```

4. **Configure database di `.env`**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ticketing_system
DB_USERNAME=root
DB_PASSWORD=
```

5. **Generate application key**
```bash
php artisan key:generate
```

6. **Install Sanctum**
```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

7. **Run migrations**
```bash
php artisan migrate
```

8. **Seed database dengan data dummy**
```bash
php artisan db:seed
```

9. **Create storage link**
```bash
php artisan storage:link
```

10. **Run development server**
```bash
php artisan serve
```

API akan berjalan di: `http://localhost:8000`

## 🔑 Default Users (Setelah Seeding)

### Admin
- Email: `admin@example.com`
- Password: `password`

### Dosen
- Email: `ahmad.sudirman@example.com`
- Password: `password`

- Email: `siti.nurhaliza@example.com`
- Password: `password`

- Email: `budi.santoso@example.com`
- Password: `password`

### Mahasiswa
- Email: `andi.pratama@student.example.com`
- Password: `password`

- Email: `dewi.lestari@student.example.com`
- Password: `password`

(Dan mahasiswa lainnya dengan password yang sama)

## 📚 API Documentation

Base URL: `http://localhost:8000/api`

### Authentication Endpoints

#### Register
```http
POST /api/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "nim_nip": "2021110001",
  "role": "mahasiswa",
  "phone": "081234567890",
  "password": "password",
  "password_confirmation": "password"
}
```

#### Login
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password"
}

Response:
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {...},
    "token": "1|xxxxxxxxxxxxx",
    "token_type": "Bearer"
  }
}
```

#### Get Current User
```http
GET /api/auth/me
Authorization: Bearer {token}
```

#### Update Profile
```http
PUT /api/auth/profile
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "John Updated",
  "phone": "081234567890"
}
```

#### Change Password
```http
PUT /api/auth/change-password
Authorization: Bearer {token}
Content-Type: application/json

{
  "current_password": "password",
  "password": "newpassword",
  "password_confirmation": "newpassword"
}
```

#### Logout
```http
POST /api/auth/logout
Authorization: Bearer {token}
```

### Ticket Endpoints

#### Get All Tickets (with filters)
```http
GET /api/tickets?status=pending&priority=high&search=keyword
Authorization: Bearer {token}
```

#### Get Ticket Statistics
```http
GET /api/tickets/statistics
Authorization: Bearer {token}

Response:
{
  "success": true,
  "data": {
    "total": 10,
    "pending": 3,
    "in_review": 2,
    "approved": 3,
    "rejected": 1,
    "completed": 1
  }
}
```

#### Get Lecturers List
```http
GET /api/tickets/lecturers
Authorization: Bearer {token}
```

#### Create Ticket (Mahasiswa only)
```http
POST /api/tickets
Authorization: Bearer {token}
Content-Type: application/json

{
  "lecturer_id": 2,
  "title": "Pengajuan Surat Rekomendasi",
  "description": "Saya memerlukan surat rekomendasi untuk...",
  "type": "surat_rekomendasi",
  "priority": "high"
}
```

**Types**: `surat_keterangan`, `surat_rekomendasi`, `ijin`, `lainnya`
**Priority**: `low`, `medium`, `high`

#### Get Ticket Detail
```http
GET /api/tickets/{id}
Authorization: Bearer {token}
```

#### Update Ticket (Mahasiswa, pending only)
```http
PUT /api/tickets/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "Updated Title",
  "description": "Updated description",
  "priority": "medium"
}
```

#### Review Ticket (Dosen only)
```http
POST /api/tickets/{id}/review
Authorization: Bearer {token}
Content-Type: application/json

{
  "lecturer_notes": "Sedang saya review"
}
```

#### Approve Ticket (Dosen only)
```http
POST /api/tickets/{id}/approve
Authorization: Bearer {token}
Content-Type: application/json

{
  "lecturer_notes": "Disetujui"
}
```

#### Reject Ticket (Dosen only)
```http
POST /api/tickets/{id}/reject
Authorization: Bearer {token}
Content-Type: application/json

{
  "rejection_reason": "Dokumen tidak lengkap"
}
```

#### Complete Ticket (Admin only)
```http
POST /api/tickets/{id}/complete
Authorization: Bearer {token}
Content-Type: application/json

{
  "admin_notes": "Ticket telah diselesaikan"
}
```

#### Delete Ticket (Admin only)
```http
DELETE /api/tickets/{id}
Authorization: Bearer {token}
```

### Document Endpoints

#### Get Documents by Ticket
```http
GET /api/tickets/{ticketId}/documents
Authorization: Bearer {token}
```

#### Upload Document
```http
POST /api/tickets/{ticketId}/documents
Authorization: Bearer {token}
Content-Type: multipart/form-data

file: [file binary]
document_type: "attachment" atau "signed_document"
```

**Document Types**:
- `attachment` - Lampiran dari mahasiswa
- `signed_document` - Dokumen yang sudah ditandatangani dari dosen

**Max file size**: 10MB

#### Download Document
```http
GET /api/documents/{id}/download
Authorization: Bearer {token}
```

#### Delete Document
```http
DELETE /api/documents/{id}
Authorization: Bearer {token}
```

## 🔄 Ticket Status Flow

```
pending → in_review → approved → completed
   ↓
rejected
```

1. **pending** - Ticket baru dibuat oleh mahasiswa
2. **in_review** - Dosen sedang mereview
3. **approved** - Dosen menyetujui
4. **rejected** - Dosen menolak
5. **completed** - Admin menandai selesai

## 🔐 Role-Based Access Control

Middleware `role` digunakan untuk membatasi akses endpoint berdasarkan role:

```php
Route::middleware('role:mahasiswa')->group(function () {
    // Hanya mahasiswa
});

Route::middleware('role:dosen')->group(function () {
    // Hanya dosen
});

Route::middleware('role:admin')->group(function () {
    // Hanya admin
});
```

## 📁 Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       ├── AuthController.php
│   │       ├── TicketController.php
│   │       └── DocumentController.php
│   └── Middleware/
│       └── CheckRole.php
└── Models/
    ├── User.php
    ├── Ticket.php
    ├── Document.php
    └── TicketHistory.php

database/
├── migrations/
│   ├── 0001_01_01_000000_create_users_table.php
│   ├── 2024_11_11_000001_create_tickets_table.php
│   ├── 2024_11_11_000002_create_documents_table.php
│   └── 2024_11_11_000003_create_ticket_histories_table.php
└── seeders/
    ├── DatabaseSeeder.php
    └── UserSeeder.php

routes/
└── api.php

config/
├── cors.php
└── sanctum.php
```

## 🧪 Testing dengan Postman

1. Import collection yang disediakan (jika ada)
2. Set environment variables:
   - `base_url`: http://localhost:8000/api
   - `token`: (akan di-set otomatis setelah login)

### Testing Flow:

1. **Login sebagai Mahasiswa**
   - POST /auth/login
   - Copy token dari response

2. **Get Lecturers List**
   - GET /tickets/lecturers

3. **Create Ticket**
   - POST /tickets

4. **Upload Document**
   - POST /tickets/{ticketId}/documents

5. **Login sebagai Dosen** (gunakan token baru)
   - POST /auth/login

6. **Review & Approve Ticket**
   - POST /tickets/{id}/review
   - POST /tickets/{id}/approve

7. **Upload Signed Document**
   - POST /tickets/{ticketId}/documents

8. **Login sebagai Admin** (gunakan token baru)
   - POST /auth/login

9. **Complete Ticket**
   - POST /tickets/{id}/complete

## 🔧 Configuration untuk React Frontend

Pastikan React app Anda dikonfigurasi dengan:

1. **Base URL API**:
```javascript
const API_BASE_URL = 'http://localhost:8000/api';
```

2. **Axios Configuration**:
```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  }
});

// Set token di header
api.interceptors.request.use(config => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default api;
```

3. **Handle Response**:
```javascript
// Semua response mengikuti format:
{
  "success": true/false,
  "message": "...",
  "data": {...}
}
```

## 🐛 Common Issues & Solutions

### Issue: CORS Error
**Solution**: Pastikan `config/cors.php` sudah dikonfigurasi dengan benar dan frontend URL ditambahkan di `allowed_origins`.

### Issue: 401 Unauthenticated
**Solution**: Pastikan token dikirim di header Authorization dengan format `Bearer {token}`.

### Issue: 403 Unauthorized
**Solution**: User tidak memiliki role yang sesuai untuk endpoint tersebut.

### Issue: File upload failed
**Solution**: 
- Pastikan `php.ini` memiliki `upload_max_filesize` dan `post_max_size` yang cukup
- Jalankan `php artisan storage:link`

## 📝 License

This project is open-sourced software licensed under the MIT license.

## 👥 Support

Untuk pertanyaan dan dukungan, silakan hubungi tim developer.
