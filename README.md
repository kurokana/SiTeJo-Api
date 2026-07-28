# SiTeJo API

SiTeJo API adalah backend untuk Sistem Informasi Ticketing Persetujuan Tanda Tangan Jurusan Teknik Elektro Universitas Lampung. Service ini dipakai oleh frontend SiTeJo Web untuk autentikasi pengguna, pengelolaan tiket, unggah dokumen, verifikasi surat, dan manajemen pengguna berbasis peran.

## Ringkasan

- Backend dibangun dengan Laravel 11.
- Autentikasi memakai Laravel Sanctum.
- Akses API dibatasi berdasarkan role `mahasiswa`, `dosen`, dan `admin`.
- Tersedia endpoint publik untuk verifikasi nomor surat.
- Registration dari publik dimatikan; pengguna dibuat oleh admin.

## Fitur Utama

- Login, logout, dan pembaruan profil pengguna.
- CRUD tiket pengajuan surat.
- Review, approve, reject, dan completion flow untuk tiket.
- Upload, download, dan hapus dokumen pendukung.
- Manajemen data pengguna untuk admin.
- Verifikasi keaslian nomor surat melalui endpoint publik.

## Teknologi

- PHP 8.2+
- Laravel 11
- Laravel Sanctum
- FPDF dan FPDI untuk kebutuhan dokumen

## Menjalankan Proyek

### 1. Install dependensi

```bash
composer install
```

### 2. Siapkan environment

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Jalankan migrasi

```bash
php artisan migrate
```

### 4. Jalankan server development

```bash
php artisan serve
```

Jika ingin menjalankan seluruh proses development yang umum dipakai Laravel, gunakan:

```bash
composer run dev
```

## Endpoint Penting

### Publik

- `POST /api/auth/login` - login pengguna.
- `GET /api/verify-letter/{nomorSurat}` - verifikasi surat berdasarkan nomor.
- `GET /api/verify-letter?nomorSurat=...` - alternatif verifikasi lewat query string.

### Terproteksi

- `POST /api/auth/logout` - logout.
- `GET /api/auth/me` - ambil data pengguna aktif.
- `PUT /api/auth/profile` - update profil.
- `PUT /api/auth/change-password` - ubah password.
- `GET /api/tickets` - daftar tiket.
- `POST /api/tickets` - buat tiket baru.
- `PUT /api/tickets/{id}` - ubah tiket.
- `POST /api/tickets/{id}/review` - review tiket.
- `POST /api/tickets/{id}/approve` - setujui tiket.
- `POST /api/tickets/{id}/reject` - tolak tiket.
- `GET /api/users` - daftar pengguna untuk admin.

## Alur Integrasi dengan SiTeJo Web

1. Pengguna membuka frontend SiTeJo Web.
2. Login dilakukan lewat API ini menggunakan Sanctum.
3. Frontend membaca role pengguna dan menampilkan dashboard yang sesuai.
4. Mahasiswa membuat tiket, dosen memproses review, dan admin memonitor alur serta data pengguna.
5. Halaman verifikasi surat pada frontend memanggil endpoint publik backend ini.

## Catatan Konfigurasi

- Pastikan `APP_URL`, koneksi database, dan konfigurasi Sanctum sudah sesuai di file `.env`.
- Jika frontend dan backend berjalan di domain berbeda, sesuaikan pengaturan CORS dan session/Sanctum.
- Karena registrasi publik dinonaktifkan, akun baru harus dibuat melalui admin atau proses seed internal.

## Struktur Logis

- `app/Http/Controllers/Api` untuk controller API.
- `routes/api.php` untuk endpoint publik dan terproteksi.
- `storage/` untuk file sementara dan output dokumen.
- `database/` untuk migrasi, seeder, dan factory.

## License

Project ini mengikuti lisensi MIT seperti yang digunakan oleh Laravel.
