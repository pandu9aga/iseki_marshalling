# ISEKI Marshalling System

Sistem pencatatan *marshalling* (part checking) untuk lini produksi traktor ISEKI. Digunakan oleh operator untuk merekam komponen per rack/box pada setiap area produksi.

## Fitur

- **Multi Auth** — Login terpisah untuk admin dan member (operator)
- **Marshalling Data** — CRUD data master part per area produksi
- **Record Part** — Operator membuat record berdasarkan plan dari sistem PODIUM, mencatat qty per part secara berurutan
- **Server Side DataTables** — Semua tabel menggunakan Yajra DataTables dengan server-side processing
- **Export Excel** — Record bisa diexport ke format XLSX
- **Responsive Table** — Tabel otomatis horizontal scroll di layar kecil

## Tech Stack

| Stack | Versi |
|-------|-------|
| PHP | ^8.2 |
| Laravel | ^12.0 |
| Database | SQLite / MySQL |
| DataTables | Yajra Laravel DataTables Oracle |
| Spreadsheet | PhpOffice PhpSpreadsheet |
| Frontend | Bootstrap 4 (Kaiadmin) + Tailwind CSS 4 |
| Build | Vite 7 |

## Struktur Database

### 3 Koneksi Database

| Connection | Driver | Default DB | Kegunaan |
|------------|--------|------------|----------|
| `default` | sqlite / mysql | `database.sqlite` | Data aplikasi: users, types, marshallings, records |
| `podium` | mysql | `iseki_podium` | Sistem PODIUM (data plan produksi) |
| `rifa` | mysql | `iseki_rifa` | Data karyawan (tabel `employees` sebagai member) |

### Tabel Utama (Default DB)

| Tabel | Keterangan |
|-------|------------|
| `users` | Admin (name + password plain) |
| `types` | Master tipe traktor |
| `marshallings` | Master data part per area (Sequence_No, Code_Part, Name_Part, Code_Rack, Qty, Mode, Area, dll) |
| `records` | Record produksi (Id_User, Sequence_No_Record, Production_Date, Type, Area) |
| `record_lists` | Detail part dalam record (Id_Marshalling, Qty_Record, Time_Record) |

### Relasi

- **Record** → `member` → tabel `employees` di koneksi `rifa`
- **Record** → `recordLists` → **Record_List**
- **Marshalling** → `type` → **Type**
- **Marshalling** → `recordLists` → **Record_List**

## Routes

### Public
| Method | URI | Name |
|--------|-----|------|
| GET | `/` | redirect ke login |
| GET | `/login` | `login` |
| POST | `/login/admin` | `login.admin` |
| POST | `/login/member` | `login.member` |
| POST | `/logout` | `logout` |

### Admin (`/admin`) — Middleware `auth:admin`
| Method | URI | Controller |
|--------|-----|------------|
| GET | `/admin/dashboard` | View `admin.dashboard` |
| GET/POST/PUT/DELETE | `/admin/users` | `UserController` (resource) |
| GET/POST/PUT/DELETE | `/admin/types` | `TypeController` (resource) |
| GET/POST/PUT/DELETE | `/admin/marshallings` | `MarshallingController` (resource) |
| GET | `/admin/records` | `RecordController@index` |
| GET | `/admin/records/export` | `RecordController@export` |
| GET | `/admin/records/{id}` | `RecordController@show` |

### Member (`/member`) — Middleware `auth:member`
| Method | URI | Controller |
|--------|-----|------------|
| GET | `/member/records` | `RecordController@index` |
| GET | `/member/record/create` | `RecordController@create` |
| POST | `/member/record/store` | `RecordController@store` |
| GET | `/member/record/{id}/record-part` | `RecordController@recordPart` |
| POST | `/member/record/{id}/update-part` | `RecordController@updatePart` |
| GET | `/member/records/export` | `RecordController@export` |

## Instalasi

### Prasyarat
- PHP 8.2+
- Composer 2.x
- MySQL (untuk koneksi podium & rifa)
- Node.js & npm (untuk frontend build)

### Langkah

```bash
# 1. Clone repositori
git clone <repo-url> iseki_marshalling
cd iseki_marshalling

# 2. Install PHP dependencies
composer install

# 3. Copy environment
cp .env.example .env

# 4. Generate app key
php artisan key:generate

# 5. Konfigurasi database di .env
# Untuk SQLite:
touch database/database.sqlite
# Untuk MySQL default:
# DB_CONNECTION=mysql
# DB_DATABASE=iseki_marshalling

# Koneksi tambahan (wajib MySQL):
DB_PODIUM_HOST=...
DB_PODIUM_DATABASE=iseki_podium
DB_RIFA_HOST=...
DB_RIFA_DATABASE=iseki_rifa

# 6. Jalankan migrasi
php artisan migrate

# 7. Install frontend dependencies
npm install && npm run build

# 8. Jalankan server
php artisan serve
```

### Login Default
- **Admin**: `name=admin`, `password=admin` (isi manual di tabel `users`)
- **Member**: login menggunakan NIK dari tabel `employees` di koneksi `rifa`

> **Catatan**: Auth menggunakan password plain text (bukan hashed) — lihat `AuthController`.

## Credential Default (Contoh .env)

```
APP_NAME=ISEKI Marshalling System
APP_URL=http://localhost/iseki_marshalling/public
DB_CONNECTION=sqlite
DB_PODIUM_HOST=127.0.0.1
DB_PODIUM_DATABASE=iseki_podium
DB_PODIUM_USERNAME=root
DB_PODIUM_PASSWORD=
DB_RIFA_HOST=127.0.0.1
DB_RIFA_DATABASE=iseki_rifa
DB_RIFA_USERNAME=root
DB_RIFA_PASSWORD=
```

## Package Tambahan

- **[yajra/laravel-datatables-oracle](https://github.com/yajra/laravel-datatables)** — Server-side DataTables
- **[phpoffice/phpspreadsheet](https://github.com/PHPOffice/PhpSpreadsheet)** — Export Excel
