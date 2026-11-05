# TBK Finance

Aplikasi sederhana untuk mencatat Chart of Accounts (COA) dan transaksi, lalu menampilkan laporan laba/rugi bulanan. Dibuat sebagai technical test dengan fokus pada struktur kode yang rapi, validasi, dan laporan yang mudah diverifikasi.

## Fitur

- CRUD Kategori COA dengan tipe `income` / `expense`.
- CRUD COA (kode unik, nama, dan kategori).
- CRUD transaksi dengan form Vue:
  - satu nominal + pilihan Debit/Credit;
  - hanya salah satu sisi yang boleh lebih dari 0;
  - validasi tetap dilakukan di server.
- Laporan laba/rugi per bulan dengan filter rentang bulan.
- Export laporan ke file Excel (`.xlsx`).
- Data contoh sesuai sheet Excel technical test (5 kategori, 8 COA, 3 transaksi).
- Tanpa autentikasi/login (sesuai kebutuhan technical test).

## Teknologi

- PHP 8.3 + Laravel 13
- MySQL (database aplikasi)
- Tailwind CSS 4 + Vite
- Vue 3 (hanya untuk form transaksi)
- `maatwebsite/excel` 4 + PhpSpreadsheet
- PHPUnit 12 (test memakai SQLite in-memory)

## Prasyarat

- PHP >= 8.3 dengan extension `zip`, `xml`, `xmlwriter`, `mbstring`, `gd`, `fileinfo`
- Composer
- Node.js + npm
- MySQL

## Instalasi

```bash
composer install
```

Salin environment lalu sesuaikan koneksi database:

```bash
cp .env.example .env
php artisan key:generate
```

Di Windows PowerShell:

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

Buat database MySQL, misalnya `db_tbk_finance`, lalu sesuaikan `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_tbk_finance
DB_USERNAME=root
DB_PASSWORD=
```

Jalankan migrasi dan seeder:

```bash
php artisan migrate --seed
```

Install frontend dependency dan build asset:

```bash
npm install
npm run build
```

Jalankan aplikasi:

```bash
php artisan serve
```

Buka `http://127.0.0.1:8000`. Halaman awal akan diarahkan ke daftar kategori COA.

Untuk development frontend, gunakan `npm run dev` di terminal terpisah.

## Halaman Utama

| Path | Keterangan |
|---|---|
| `/` | Redirect ke `/coa-categories` |
| `/coa-categories` | CRUD kategori COA |
| `/coas` | CRUD COA |
| `/transactions` | CRUD transaksi |
| `/profit-loss` | Laporan laba/rugi + filter bulan |
| `/profit-loss/export` | Unduh laporan `.xlsx` |

## Aturan Bisnis

- Setiap transaksi hanya boleh memiliki **satu sisi** yang lebih dari 0: debit atau credit.
- COA bertipe `income`:
  - pendapatan = `credit - debit`.
- COA bertipe `expense`:
  - beban = `debit - credit`.
- Laba/rugi bersih = pendapatan - beban.
- Bulan tanpa transaksi tetap ditampilkan dengan nilai 0.
- Rentang laporan memakai format `YYYY-MM` dan bulan akhir tidak boleh lebih awal dari bulan mulai.

## Skema Data

- `coa_categories`: `name` unik, `type` (`income` / `expense`).
- `coas`: `code` unik, `name`, `coa_category_id` (restrict on delete).
- `transactions`: `date` (index), `coa_id` (restrict on delete), `description` nullable, `debit` dan `credit` `decimal(15,2)` default 0.

## Struktur Penting

```text
app/
  Exports/ProfitLossExport.php
  Http/Controllers/
    CoaCategoryController.php
    CoaController.php
    TransactionController.php
    ProfitLossController.php
  Http/Requests/
    CoaCategoryRequest.php
    CoaRequest.php
    TransactionRequest.php
    ProfitLossRequest.php
  Models/
    CoaCategory.php
    Coa.php
    Transaction.php
  Services/ProfitLossService.php
database/
  migrations/
  seeders/
resources/
  js/app.js
  js/components/TransactionForm.vue
  views/layouts/app.blade.php
  views/reports/
  views/transactions/
tests/Feature/
  ProfitLossServiceTest.php
  TransactionValidationTest.php
```

## Testing

```bash
php artisan test
```

Test memakai SQLite in-memory (`phpunit.xml`) dan `RefreshDatabase`, sehingga tidak menyentuh database MySQL aplikasi.

Cakupan test:

- perhitungan pendapatan, beban, laba/rugi bersih, dan total;
- transaksi pada tanggal terakhir bulan;
- bulan/periode tanpa transaksi;
- validasi dua sisi terisi, dua sisi 0, dan COA tidak valid;
- penyimpanan transaksi debit-only dan credit-only.

## Catatan Teknis

- Timezone aplikasi: `Asia/Jakarta`.
- Form transaksi memakai Vue, tetapi aturan bisnis tetap divalidasi di server.
- Beban Vue hanya dimuat di halaman transaksi melalui `@push('scripts')`.
- Agregasi laporan dilakukan di PHP agar kompatibel dengan MySQL (aplikasi) dan SQLite (test). Untuk data besar, pertimbangkan agregasi SQL + index atau tabel ringkasan bulanan.
- `WithStrictNullComparison` dipakai pada export agar nilai 0 tetap tertulis di Excel.
- Batas query laporan memakai awal/akhir hari agar transaksi pada tanggal terakhir bulan tetap masuk, termasuk di SQLite.

## Reset Data

Perintah berikut menghapus seluruh data lalu mengisi ulang data contoh:

```bash
php artisan migrate:fresh --seed
```

Gunakan dengan hati-hati karena data yang ada akan hilang.
