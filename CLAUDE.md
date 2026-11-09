# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Laravel 12 app used on the ISEKI tractor production line for *marshalling* (part-checking): operators record which parts were pulled from which rack/box per production plan, and assembly-line staff ("perakitan") report shortages and view procedures. Indonesian is used throughout the UI, validation messages, and many code comments — match that when editing user-facing strings.

## Commands

```bash
# Install & setup
composer install
npm install
composer run setup      # all-in-one: install, .env, migrate, npm build

# Local dev (serves PHP, queue listener, log tail, and Vite together)
composer run dev

# Frontend only
npm run dev      # vite dev server
npm run build    # production build

# Tests (uses in-memory sqlite, see phpunit.xml)
composer run test
php artisan test
php artisan test --filter=TestName
php artisan test tests/Feature/SomeTest.php

# Code style
vendor/bin/pint          # Laravel Pint, fixes in place
vendor/bin/pint --test   # check only

# Database
php artisan migrate
php artisan migrate:fresh
```

`tests/` currently only has Laravel's default scaffold — no project-specific test coverage exists yet.

## Architecture

### Four database connections (`config/database.php`)

- **default** (`sqlite`/`mysql`) — this app's own tables: `users`, `types`, `main_types`, `marshallings`, `records`, `record_lists`, `punishments`, `member_areas`, `map_areas`, `part_kurangs`, `marshalling_settings`, `record_pendings`, `scan_order_overrides`.
- **podium** (MySQL, `iseki_podium`) — external production planning system. Read-only. Used to look up production schedules and part requirements.
- **rifa** (MySQL, `iseki_rifa`) — external HR system. `DB::connection('rifa')->table('employees')` is the source of truth for people: both `Member` and `Perakitan` Eloquent models point at it (see below). Never migrate or write to this connection except where existing code already does.
- **aspro** (MySQL, `iseki_aspro`) — external "ASPRO" system used only by `Perakitan\KanbanController` and `Perakitan\ProsedurController` to look up training/procedure records and PDF paths on disk (`C:/xampp/htdocs/iseki_aspro/public/storage/...`). Read-only.

### Three parallel auth guards, one login form

`config/auth.php` defines three guards — `admin`, `member`, `perakitan` — backed by three Eloquent providers (`User`, `Member`, `Perakitan`). `AuthController` handles all three login endpoints from a single `/login` page, logging out the other two guards first (`clearPreviousAuth`) so a session only ever holds one identity. Passwords are compared as **plain text** (not hashed) — intentional per existing code.

`Member` and `Perakitan` are two models mapped to the same `rifa.employees` table — they exist to let one person authenticate against two different guards/permission sets.

Route groups and guards:
- `/admin/*` → `auth:admin` — marshalling master data, records review, NG handling, punishments, member-area registration, summary/exports, part photos
- `/member/*` → `auth:member` — operator recording flow: create record → scan-part → update-part loop
- `/perakitan/*` → `auth:perakitan` — kanban lookup, procedure PDFs, part-shortage comments
- `/part-kurang/*` → public, no auth — shortage self-report kiosk (identifies people by NIK typed at kiosk)

Global exception handling in `bootstrap/app.php`: 404s under `member/record*` redirect to `member.record.create` with friendly message (QR-scanned URLs can go stale), and 409/419 force-logout all guards and bounce to `/login`.

### Core data model

- `Type` (tractor type) belongs to `MainType`; `Marshalling` (master part-per-area: `Sequence_No`, `Code_Part`, `Code_Rack`, `Qty`, `Mode`, `Area`, `Box`) belongs to `Type`.
- `Record` (one operator's run) belongs to `Member` and has many `Record_List` (one per part, copied from `Marshalling` at creation, then filled with `Qty_Record`/`Time_Record` as operator scans). Fields: `Sequence_No_Record`, `Production_Date`, `Type`, `Area`.
- Scan order is fixed when the record is created: `Member\RecordController::store()` decides it once via `MarshallingSetting::scanOrderFor($request->sequence_no)` — the global default (`marshalling_settings.scan_order`, `box`|`sequence`) unless `scan_order_overrides` has a row for that production sequence number (`records.Sequence_No_Record`, not the rack `Marshalling.Sequence_No`); both are managed at Admin `marshalling-settings`. It numbers `Record_List.Sequence_No` by sub-area (transmisi/sub_engine) → `Box` → `Marshalling.Sequence_No` (mode `box`) or sub-area → `Marshalling.Sequence_No` → `Box` (mode `sequence`). `nextPart()` just follows `Record_List.Sequence_No`, so setting changes never affect existing records.
- `RecordPending` (`record_pendings`): operator bisa mem-pending kanban (alasan wajib) dan mengerjakan kanban lain. Per member per record (pemilik dan tiap helper pending bagiannya sendiri); aktif = `Resumed_At` NULL. `Member\RecordController::create()` tidak auto-lanjut ke record yang di-pending member itu (ada kartu "Kanban Pending" + tombol Lanjutkan); membuka/submit scan otomatis me-resume. `Record::calculateDurationSeconds()` mengurangi waktu pending (perkiraan). Pending bisa diambil alih member lain yang terdaftar di area itu (`takeover`, modal duplikat) per box pilihan (`boxes[]` = `area|box`): part belum di-scan di box terpilih pindah `Assigned_To` ke pengambil (NULL jika pengambil adalah pemilik); selama pender masih punya sisa part pending tetap aktif (bisa diambil member lain), baru setelah habis `Resumed_At` diisi dan `Taken_By` = pengambil.
- `Record_List` includes `Time_Record` for each part scan; `Record::calculateDurationSeconds()` computes elapsed time (first to last scan), subtracting fixed company break windows (10:00–10:10, 15:00–15:10, plus Friday lunch window). Reuse this instead of ad-hoc time math.
- "Bantu scan per box": `record_lists.Assigned_To` (nullable, tanpa FK karena member ada di DB rifa) — NULL = milik pemilik record (`records.Id_User`), isi = id member yang membantu, sekaligus penanda siapa yang scan part itu. Box = pasangan `Area`+`Box` (huruf box berulang antar sub-area). Saat `store()` menemukan kanban duplikat, member lain yang terdaftar di area itu ditawari box yang belum disentuh (`availableHelpBoxes`) lewat `member.record.help`. `nextPart($recordId, $memberId, $isOwner)` memfilter pemilik ke `Assigned_To` NULL dan helper ke `Assigned_To` = id-nya; `scanPart`/`updatePart` menolak part milik orang lain. Efisiensi dikirim hanya saat semua part record ter-scan; remark ditawarkan ke pemilik begitu bagiannya selesai (walau operator lain belum), dan ke yang men-scan part terakhir jika remark masih kosong. `saveRemark` boleh oleh pemilik atau member ber-`Assigned_To` di record itu, dan remark yang sudah ada digabung dengan " | " (tidak ditimpa). Helper mengambil satu box per kali (radio) dan, saat box-nya selesai, ditawari box bebas berikutnya (`help_next`); helper tidak punya record sendiri, kerjanya dikreditkan per part lewat `Assigned_To` memakai `Record::scannersSummary()`; Pending di balik flag `PENDING_ENABLED` (default off). Tidak ada `release`/Lepas Box (mengembalikan box setengah jadi ke pemilik yang absen membuat record macet): helper yang harus pergi memakai Pending; pemilik bisa `reclaim` (Ambil Kembali) box yang dipegang orang lain dan menutup pending pemegangnya. `Member\RecordController::index` juga menampilkan record tempat member punya `Assigned_To`; detail admin punya kolom "Discan oleh"; Part Kurang kiosk mengirim `box` sehingga `id_user`/`member_nik` mengikuti helper yang men-scan box itu (campuran/tanpa box = pemilik).
- `PartKurang` (reported shortage) belongs to `Record` and `Member`, and carries separate `perakitan_nik` (assembly worker who noticed) from `member_nik`/`id_user` (marshalling operator responsible).
- Special multi-part areas: `transmisi` expands to `transmisi_a/b/c` and `sub_engine` expands to `sub_engine_a/b` — `Marshalling`/`Record_List` rows are tagged with the specific sub-area, but UI/type lookups collapse them back to parent name (see `Member\RecordController::getAreasByType` and `orderByRaw("FIELD(Area, ...)")` in `store()`). When adding logic that touches `Area`, check both forms. Pemetaan area induk -> sub-area ada di `Marshalling::subAreasOf()` / `Marshalling::SUB_AREAS` (dipakai `store()`, `getAreasByType()`, `PublicPartKurangController::marshallingsFor()`).
- "Punished" members (`Member::isPunished()`, keyed by `nik` in `punishments`) are held to stricter validation in `Member\RecordController::updatePart` — they must manually enter `Qty_Record` instead of auto-fill.
- NG (defect) photo capture in `updatePart()` only fires when `Marshalling.Mode === 'ai'` and scanned quantity doesn't match plan; images are resized/compressed with GD to stay under ~500KB, stored under `public/uploads/ng/<Ym>/`.
- Reference part photos come from the iseki_recorder app (no DB connection, no model): `Marshalling::$photo_url` accessor links to `http://192.168.173.201/iseki_recorder/public/part-photo/{Code_Rack}`, a Recorder route that redirects to the photo file (404 if none). Photos are added/edited/deleted only in iseki_recorder; `AdminFotoPartController` just browses by type/area/box and the kiosk shows them. The old `foto_parts` table was dropped (migration `2026_11_04_000004`); its 9 JPGs were moved to `storage/app/old_part_photos/`.
- `MapArea` (master `Area` + `Location_Rack` → `Sequence_No` mapping), managed via `Admin\MapAreaController` with bulk Excel import (columns: `Area`, `Location_Rack`, `Sequence_No`). Its main consumer is the Marshalling Excel import (below): rows whose rack isn't in `map_areas` get `Sequence_No = 0`, so MapArea must be populated before importing marshalling data.
- `Marshalling.Is_Active` (default 1) and `Marshalling.No_Instruction`: rows with a pending instruction number are imported as inactive. **Every query that builds a record or listing from `Marshalling` must filter `where('Is_Active', 1)`** (see `Member\RecordController` and `Admin\MarshallingController`).

### Marshalling Excel import (`Admin\MarshallingController::import`)

This is the most complex piece of logic in the app. One sheet per area (sheet title = area; `mower`/`collector` → `mowcol`). Data starts at row 9. Fixed columns: C blok, D code part, E name, K code rack, L location rack (first value if comma-separated), M "part di luar area" (any value skips the row), N sub-area suffix (→ `transmisi_a` etc.), O no. instruksi, P box, Q pembeda (per-type box/qty overrides in the format `TYPE:box=qty,box=qty;TYPE2:...`). From column R onward, each header is a tractor type (unknown types are auto-created) and each cell is that type's qty. Struck-through qty cells are ignored.
For each type × area, the import **deletes all existing `Marshalling` rows and re-inserts them**. Old active rows whose `Sequence_No` has a pending instruction are carried over. Because of this, `Id_Marshalling` values change on every import — keep that in mind for anything that references them, such as anything keyed on `Id_Marshalling`. Before replacing, the old type × area list (all rows incl. inactive, in `Sequence_No` order with `Sequence_No = 0` rows last) is backed up to `public/history/{type}-{area}-{Ymd_His}.xlsx` (older files use `_` separators; the history page reads both), browsable at `admin.marshallings.history`. A type × area with no old rows gets the newly imported list saved instead, so every imported type × area appears in history.
Riwayat record tidak boleh hilang karena import: `record_lists.Id_Marshalling` bersifat nullable dengan FK `nullOnDelete()` (migrasi `2026_11_03_000001`), jadi menghapus/mengganti baris `Marshalling` (import Excel, hapus type via `TypeController::destroy`) hanya mengosongkan kolom itu, bukan menghapus `Record_List`. `Record_List` menyalin semua datanya sendiri saat record dibuat dan **tidak boleh bergantung pada baris Marshalling-nya** (jangan baca `Id_Marshalling`/relasi `marshalling()` untuk menampilkan riwayat). Satu-satunya cascade yang benar: `record_lists.Id_Record` → `records`. FK `foto_parts.Id_Marshalling` sudah dilepas.

### Frontend

Bootstrap 4 (Kaiadmin admin theme) + Tailwind CSS 4, built with Vite. Entry points: `resources/css/app.css` + `resources/js/app.js`. All list views use Yajra server-side DataTables (`datatables($query)->make(true)` pattern) — follow this for new tables rather than client-side data. Excel export uses `phpoffice/phpspreadsheet` via Yajra helpers on `*@export` routes.

### Repo quirks

- `iseki_marshalling.sql` at the root is a MySQL dump of the default DB. It's useful for checking real column names and data, but it isn't a migration source.
- Some compiled Blade views under `storage/framework/views/` are tracked in git. Never edit them; edit `resources/views/` instead.
