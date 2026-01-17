# Restrukturisasi Role Pengguna - SIMASET 2025

## Ringkasan Perubahan

Restrukturisasi role pengguna telah diselesaikan dengan memenuhi ketentuan berikut:

### ✅ 1. Hapus Role Direktur
- Role **Direktur** telah dihapus dari sistem
- Semua data pengguna dengan role direktur telah dihapus dari database (melalui migration)
- Semua referensi direktur di dalam kode telah dibersihkan

### ✅ 2. Rename Role Sekretaris → Admin
- Semua referensi role **sekretaris** diganti dengan **admin**
- Data pengguna dengan role sekretaris otomatis diperbarui ke admin melalui migration
- Fitur dan hak akses sekretaris sepenuhnya berpindah ke admin

### ✅ 3. Rename Role Kepala Usaha → User
- Semua referensi role **kepalausaha** diganti dengan **user**
- Data pengguna dengan role kepalausaha otomatis diperbarui ke user melalui migration
- Fitur dan hak akses kepala usaha sepenuhnya berpindah ke user

---

## Cakupan Perubahan Detail

### 1. Database
- **Migration Created**: `database/migrations/2025_01_17_000000_restruktur_roles.php`
  - Update enum roles dari `['sekretaris', 'direktur', 'kepalausaha']` menjadi `['admin', 'user']`
  - Migrasi data: `sekretaris` → `admin`, `kepalausaha` → `user`
  - Hapus semua data pengguna dengan role `direktur`

- **Migration Updated**: `database/migrations/2014_10_12_000000_create_users_table.php`
  - Update enum roles untuk tabel users baru

- **Seeder Updated**: `database/seeders/DatabaseSeeder.php`
  - User "Dwi Purnomo": role `sekretaris` → `admin`
  - User "Galang Adi Trianto": role `kepalausaha` → `user`
  - User "Mujiyono" (Direktur): **DIHAPUS**

### 2. Middleware

#### Perubahan File:
1. **DirekturMiddleware.php** → Diubah menjadi **AdminMiddleware**
   - Validasi: `roles === 'direktur'` → `roles === 'admin'`

2. **SekretarisMiddleware.php** → Diubah menjadi **AdminMiddleware**
   - Validasi: `roles === 'sekretaris'` → `roles === 'admin'`

3. **KepalausahaMiddleware.php** → Diubah menjadi **UserMiddleware**
   - Validasi: `roles === 'kepalausaha'` → `roles === 'user'`

### 3. Routes
**File**: `routes/web.php`

```php
// Sebelum:
Route::middleware(['auth', 'checkRole:sekretaris,kepalausaha'])->group(...)
Route::middleware(['auth', 'checkRole:sekretaris'])->group(...)
Route::group(['middleware' => ['auth', 'sekretaris']])
Route::group(['middleware' => ['auth', 'kepalausaha']])

// Sesudah:
Route::middleware(['auth', 'checkRole:admin,user'])->group(...)
Route::middleware(['auth', 'checkRole:admin'])->group(...)
Route::group(['middleware' => ['auth', 'admin']])
Route::group(['middleware' => ['auth', 'user']])
```

### 4. Controllers

#### File Perubahan:
1. **DataUserController.php**
   - Validasi role: `in:sekretaris,direktur,kepalausaha` → `in:admin,user`
   - Kondisi lokasi: `$request->roles == 'kepalausaha'` → `$request->roles == 'user'`

2. **StatusPengadaanController.php**
   - `setPersetujuan()`: `roles !== 'sekretaris'` → `roles !== 'admin'`
   - `setPenolakan()`: `roles !== 'sekretaris'` → `roles !== 'admin'`

3. **HomeController.php**
   - `if($userLogin == 'kepalausaha')` → `if($userLogin == 'user')`

4. **LaporanController.php**
   - `if($userLogin == 'kepalausaha')` → `if($userLogin == 'user')`
   - `if($userLogin === 'kepalausaha')` → `if($userLogin === 'user')`

5. **PenghapusanAsetController.php**
   - `if($userLogin == 'kepalausaha')` → `if($userLogin == 'user')`

6. **StatistikController.php**
   - `if($userLogin == 'kepalausaha')` → `if($userLogin == 'user')`

7. **KeuanganController.php**
   - `if($userLogin == 'kepalausaha')` → `if($userLogin == 'user')`

8. **LabelController.php**
   - `if($userLogin == 'kepalausaha')` → `if($userLogin == 'user')`

9. **GrafikController.php**
   - `if($userLogin == 'kepalausaha')` → `if($userLogin == 'user')`

10. **BarangController.php**
    - `if($userLogin == 'kepalausaha')` → `if($userLogin == 'user')`

### 5. Views (Blade Templates)

#### File Perubahan:

1. **layouts/main.blade.php**
   - Menu sections: `@if($users->roles === 'sekretaris')` → `@if($users->roles === 'admin')`
   - Menu sections: `@if($users->roles === 'kepalausaha')` → `@if($users->roles === 'user')`
   - **Hapus** seluruh section untuk `@if($users->roles === 'direktur')`

2. **datauser/create.blade.php**
   - Role options: 
     - `value="kepalausaha"` → `value="user"` (label: "Kepala Usaha" → "User")
     - `value="direktur"` → **DIHAPUS**
   - JavaScript validation: `if(roles.value === "kepalausaha")` → `if(roles.value === "user")`

3. **datauser/edit.blade.php**
   - JavaScript validation: `if(roles.value === "kepalausaha")` → `if(roles.value === "user")`

4. **barang/create.blade.php**
   - `@if(auth()->user()->roles === 'kepalausaha')` → `@if(auth()->user()->roles === 'user')`

5. **barang/index.blade.php**
   - `@if(auth()->user()->roles === 'kepalausaha')` → `@if(auth()->user()->roles === 'user')`

6. **permintaan/index.blade.php**
   - `@if (auth()->user()->roles === 'sekretaris')` → `@if (auth()->user()->roles === 'admin')`
   - `@elseif (auth()->user()->roles === 'kepalausaha')` → `@elseif (auth()->user()->roles === 'user')`

7. **penghapusan-aset/index.blade.php**
   - `@if(auth()->user()->roles === 'kepalausaha')` → `@if(auth()->user()->roles === 'user')`

8. **pengadaan/index.blade.php**
   - `@if (auth()->user()->roles === 'kepalausaha')` → `@if (auth()->user()->roles === 'user')`

9. **laporan/index.blade.php**
   - `@if(auth()->user()->roles === 'kepalausaha')` → `@if(auth()->user()->roles === 'user')`

10. **laporan/cetak.blade.php**
    - `@if(auth()->user()->roles === 'kepalausaha')` → `@if(auth()->user()->roles === 'user')`
    - `@if(auth()->user()->roles == 'kepalausaha')` → `@if(auth()->user()->roles == 'user')`

11. **label/index.blade.php**
    - `@if(auth()->user()->roles === 'kepalausaha')` → `@if(auth()->user()->roles === 'user')`

---

## Langkah Implementasi

Untuk menerapkan perubahan ini, jalankan perintah berikut:

```bash
# 1. Jalankan migration
php artisan migrate

# 2. Jalankan seeder (jika ingin reset data pengguna)
php artisan db:seed

# 3. Clear cache
php artisan cache:clear
php artisan config:cache
```

---

## Verifikasi

Untuk memverifikasi bahwa perubahan telah diterapkan dengan benar:

### 1. Cek Database
```sql
-- Cek values enum roles
SELECT DISTINCT roles FROM users;
-- Hasil yang diharapkan: admin, user

-- Cek data pengguna
SELECT id, name, email, roles FROM users;
```

### 2. Cek Middleware
- File: `app/Http/Middleware/AdminMiddleware.php` (sebelumnya DirekturMiddleware/SekretarisMiddleware)
- File: `app/Http/Middleware/UserMiddleware.php` (sebelumnya KepalausahaMiddleware)

### 3. Test Login
- Login sebagai admin (sebelumnya sekretaris)
- Login sebagai user (sebelumnya kepalausaha)
- Verifikasi akses ke menu dan fitur sesuai role

### 4. Test Fitur
- **Admin**: Dapat melihat data permintaan, menyetujui/menolak pengadaan, mengelola user
- **User**: Dapat mengajukan pengadaan, melihat laporan yang terkait dengan lokasinya

---

## Catatan Penting

1. **Backup Data**: Pastikan melakukan backup database sebelum menjalankan migration
2. **Rollback**: Jika diperlukan, jalankan `php artisan migrate:rollback`
3. **Roles Seeding**: Update file seeder jika menambah pengguna baru dengan roles baru
4. **Authorization**: Pastikan semua policy/gate di code sudah updated referensi rolenya

---

## File yang Dimodifikasi

### Database
- ✅ database/migrations/2025_01_17_000000_restruktur_roles.php (BARU)
- ✅ database/migrations/2014_10_12_000000_create_users_table.php
- ✅ database/seeders/DatabaseSeeder.php

### Middleware
- ✅ app/Http/Middleware/DirekturMiddleware.php (content diganti ke AdminMiddleware)
- ✅ app/Http/Middleware/SekretarisMiddleware.php (content diganti ke AdminMiddleware)
- ✅ app/Http/Middleware/KepalausahaMiddleware.php (content diganti ke UserMiddleware)

### Routes
- ✅ routes/web.php

### Controllers
- ✅ app/Http/Controllers/DataUserController.php
- ✅ app/Http/Controllers/StatusPengadaanController.php
- ✅ app/Http/Controllers/HomeController.php
- ✅ app/Http/Controllers/LaporanController.php
- ✅ app/Http/Controllers/PenghapusanAsetController.php
- ✅ app/Http/Controllers/StatistikController.php
- ✅ app/Http/Controllers/KeuanganController.php
- ✅ app/Http/Controllers/LabelController.php
- ✅ app/Http/Controllers/GrafikController.php
- ✅ app/Http/Controllers/BarangController.php

### Views
- ✅ resources/views/layouts/main.blade.php
- ✅ resources/views/datauser/create.blade.php
- ✅ resources/views/datauser/edit.blade.php
- ✅ resources/views/barang/create.blade.php
- ✅ resources/views/barang/index.blade.php
- ✅ resources/views/permintaan/index.blade.php
- ✅ resources/views/penghapusan-aset/index.blade.php
- ✅ resources/views/pengadaan/index.blade.php
- ✅ resources/views/laporan/index.blade.php
- ✅ resources/views/laporan/cetak.blade.php
- ✅ resources/views/label/index.blade.php

---

## Status: ✅ SELESAI

Restrukturisasi role pengguna telah dikomplekkan. Semua perubahan telah diterapkan ke codebase.

Tanggal: 17 Januari 2025
