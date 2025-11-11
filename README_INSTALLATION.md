# ✅ Sistem Ticketing - Backend Installation Complete!

Backend API untuk sistem ticketing pengajuan tanda tangan telah berhasil dibuat dan dikonfigurasi.

## 📦 Yang Telah Dibuat

### 1. Database Structure
- ✅ **users table** (dengan role: mahasiswa, dosen, admin)
- ✅ **tickets table** (untuk ticket pengajuan)
- ✅ **documents table** (untuk file attachments)
- ✅ **ticket_histories table** (untuk tracking perubahan)

### 2. Models & Relationships
- ✅ **User Model** - dengan relationships ke tickets dan documents
- ✅ **Ticket Model** - dengan auto-generate ticket number
- ✅ **Document Model** - untuk file management
- ✅ **TicketHistory Model** - untuk audit trail

### 3. Authentication & Authorization
- ✅ **Laravel Sanctum** - Token-based authentication
- ✅ **CheckRole Middleware** - Role-based access control
- ✅ **AuthController** - Register, Login, Logout, Profile management

### 4. Controllers
- ✅ **AuthController** - Authentication & user management
- ✅ **TicketController** - CRUD tickets dengan role-based actions
- ✅ **DocumentController** - Upload, download, delete documents

### 5. API Routes
- ✅ Authentication endpoints (`/api/auth/*`)
- ✅ Ticket management endpoints (`/api/tickets/*`)
- ✅ Document management endpoints (`/api/documents/*`)

### 6. Configuration
- ✅ CORS configuration
- ✅ Sanctum configuration
- ✅ API routes setup

### 7. Seeders
- ✅ User seeder dengan data dummy:
  - 1 Admin
  - 3 Dosen
  - 5 Mahasiswa

### 8. Documentation
- ✅ **API_DOCUMENTATION.md** - Complete API documentation
- ✅ **REACT_INTEGRATION.md** - React integration guide
- ✅ **SETUP_GUIDE.md** - Quick setup guide
- ✅ **Postman Collection** - Ready-to-use API testing

## 🎯 Fitur Utama

### Untuk Mahasiswa
- Membuat ticket pengajuan
- Upload dokumen lampiran
- Melihat status ticket
- Update ticket yang pending
- Melihat history perubahan

### Untuk Dosen
- Melihat ticket yang di-assign
- Review ticket
- Approve/Reject ticket
- Upload dokumen bertanda tangan
- Menambahkan catatan

### Untuk Admin
- Melihat semua ticket
- Mark ticket sebagai completed
- Mengelola seluruh sistem
- Delete ticket jika diperlukan

## 🔑 Default Accounts (sudah di-seed)

### Admin
```
Email: admin@example.com
Password: password
```

### Dosen
```
Email: ahmad.sudirman@example.com
Password: password

Email: siti.nurhaliza@example.com
Password: password

Email: budi.santoso@example.com
Password: password
```

### Mahasiswa
```
Email: andi.pratama@student.example.com
Password: password

Email: dewi.lestari@student.example.com
Password: password

(dan 3 mahasiswa lainnya)
```

## 🚀 Cara Menjalankan Server

```bash
php artisan serve
```

Server akan berjalan di: **http://localhost:8000**

API Base URL: **http://localhost:8000/api**

## 📝 Testing API

### Option 1: Postman
1. Import file: `Ticketing_System_API.postman_collection.json`
2. Set base_url: `http://localhost:8000/api`
3. Login untuk mendapatkan token
4. Test endpoints lainnya

### Option 2: Manual Test dengan curl

**Login:**
```bash
curl -X POST http://localhost:8000/api/auth/login ^
  -H "Content-Type: application/json" ^
  -d "{\"email\":\"andi.pratama@student.example.com\",\"password\":\"password\"}"
```

**Get Tickets:**
```bash
curl -X GET http://localhost:8000/api/tickets ^
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 🔗 Integrasi dengan React

Lihat file **REACT_INTEGRATION.md** untuk:
- Setup Axios
- Auth Service
- Ticket Service
- Document Service
- Protected Routes
- Example Components

## 📚 API Endpoints

### Authentication
- POST `/api/auth/register` - Register user baru
- POST `/api/auth/login` - Login
- POST `/api/auth/logout` - Logout
- GET `/api/auth/me` - Get current user
- PUT `/api/auth/profile` - Update profile
- PUT `/api/auth/change-password` - Change password

### Tickets
- GET `/api/tickets` - Get all tickets (filtered by role)
- GET `/api/tickets/statistics` - Get statistics
- GET `/api/tickets/lecturers` - Get dosen list
- GET `/api/tickets/{id}` - Get ticket detail
- POST `/api/tickets` - Create ticket (Mahasiswa)
- PUT `/api/tickets/{id}` - Update ticket (Mahasiswa)
- POST `/api/tickets/{id}/review` - Review ticket (Dosen)
- POST `/api/tickets/{id}/approve` - Approve ticket (Dosen)
- POST `/api/tickets/{id}/reject` - Reject ticket (Dosen)
- POST `/api/tickets/{id}/complete` - Complete ticket (Admin)
- DELETE `/api/tickets/{id}` - Delete ticket (Admin)

### Documents
- GET `/api/tickets/{ticketId}/documents` - Get documents
- POST `/api/tickets/{ticketId}/documents` - Upload document
- GET `/api/documents/{id}/download` - Download document
- DELETE `/api/documents/{id}` - Delete document

## 🔄 Ticket Status Flow

```
PENDING → IN_REVIEW → APPROVED → COMPLETED
    ↓
REJECTED
```

## 📁 File Structure

```
e:\WFP\Web-BE\
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── AuthController.php
│   │   │       ├── TicketController.php
│   │   │       └── DocumentController.php
│   │   └── Middleware/
│   │       └── CheckRole.php
│   └── Models/
│       ├── User.php
│       ├── Ticket.php
│       ├── Document.php
│       └── TicketHistory.php
├── database/
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 2024_11_11_000001_create_tickets_table.php
│   │   ├── 2024_11_11_000002_create_documents_table.php
│   │   └── 2024_11_11_000003_create_ticket_histories_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── UserSeeder.php
├── routes/
│   └── api.php
├── config/
│   ├── cors.php
│   └── sanctum.php
├── storage/
│   └── app/
│       └── public/
│           └── documents/ (untuk file uploads)
├── API_DOCUMENTATION.md
├── REACT_INTEGRATION.md
├── SETUP_GUIDE.md
└── Ticketing_System_API.postman_collection.json
```

## ✨ Next Steps

1. **Test API** menggunakan Postman atau curl
2. **Buat React Frontend** mengikuti panduan di REACT_INTEGRATION.md
3. **Customize** sesuai kebutuhan spesifik Anda
4. **Deploy** ke production server

## 🎓 Testing Flow Recommendation

1. Login sebagai **Mahasiswa**
   - Create ticket
   - Upload document attachment

2. Login sebagai **Dosen**
   - Review ticket mahasiswa
   - Approve ticket
   - Upload signed document

3. Login sebagai **Admin**
   - View all tickets
   - Complete ticket

4. Kembali login sebagai **Mahasiswa**
   - View completed ticket
   - Download signed document

## 💡 Tips

- Semua response API menggunakan format JSON yang konsisten:
  ```json
  {
    "success": true/false,
    "message": "...",
    "data": {...}
  }
  ```

- Token authentication menggunakan Bearer token:
  ```
  Authorization: Bearer {your-token}
  ```

- File upload max 10MB (dapat diubah di controller)

- Pagination default 15 items per page

## 🆘 Support

Jika ada pertanyaan atau menemui masalah:
1. Cek file **API_DOCUMENTATION.md** untuk detail endpoint
2. Cek file **SETUP_GUIDE.md** untuk troubleshooting
3. Cek error log di `storage/logs/laravel.log`

---

**Sistem berhasil dibuat! Siap untuk diintegrasikan dengan React Frontend.** 🎉

Happy Coding! 💻
