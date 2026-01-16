# Restrukturisasi Hak Akses Role Direktur dan Sekretaris

## 📋 Ringkasan Perubahan

Restrukturisasi sistem hak akses telah dilakukan untuk modul Pengadaan Barang. Role **Direktur** tidak lagi memiliki akses ke fitur Permintaan dan Pengajuan, sementara akses tersebut sepenuhnya ditransfer ke role **Sekretaris**.

---

## 🔄 Perubahan Detail

### 1. **Routes (routes/web.php)**

#### Sebelum:
```php
Route::middleware(['auth', 'checkRole:direktur,sekretaris'])->group(function(){
    Route::resource('/permintaan', StatusPengadaanController::class);
});

Route::group(['middleware' => ['auth', 'direktur']], function(){
    Route::put('/permintaan/{id}/setuju', [StatusPengadaanController::class, 'setPersetujuan'])->name('permintaan.setuju');
    Route::put('/permintaan/{id}/tolak', [StatusPengadaanController::class, 'setPenolakan'])->name('permintaan.tolak');
});

Route::group(['middleware' => ['auth', 'sekretaris']], function(){
    Route::resource('/datauser', DataUserController::class);
    Route::get('permintaan/laporan-pengadaan/{id}', [StatusPengadaanController::class, 'cetakPengadaanBarang']);
});
```

#### Sesudah:
```php
Route::middleware(['auth', 'checkRole:sekretaris'])->group(function(){
    Route::resource('/permintaan', StatusPengadaanController::class);
    Route::put('/permintaan/{id}/setuju', [StatusPengadaanController::class, 'setPersetujuan'])->name('permintaan.setuju');
    Route::put('/permintaan/{id}/tolak', [StatusPengadaanController::class, 'setPenolakan'])->name('permintaan.tolak');
    Route::get('permintaan/laporan-pengadaan/{id}', [StatusPengadaanController::class, 'cetakPengadaanBarang']);
});

Route::group(['middleware' => ['auth', 'sekretaris']], function(){
    Route::resource('/datauser', DataUserController::class);
});
```

**Perubahan:**
- ✅ Middleware diubah dari `checkRole:direktur,sekretaris` menjadi `checkRole:sekretaris`
- ✅ Routes untuk `setPersetujuan` dan `setPenolakan` dipindahkan ke group Sekretaris
- ✅ Routes untuk `cetakPengadaanBarang` dipindahkan ke group Sekretaris

---

### 2. **Controller (app/Http/Controllers/StatusPengadaanController.php)**

#### Method: `setPersetujuan($id)`

**Sebelum:**
```php
public function setPersetujuan($id)
{
    Statuspengadaan::where('id', $id)
        ->update([
            'status'  => 'disetujui',
            'user_id' => Auth::id()
        ]);
    Alert::success('Berhasil', 'Pengadaan Barang Disetujui');
    return redirect()->back()->with('success', 'Persetujuan berhasil disimpan.');
}
```

**Sesudah:**
```php
public function setPersetujuan($id)
{
    // Validasi role sekretaris
    if(auth()->user()->roles !== 'sekretaris'){
        abort(403, 'Hanya Sekretaris yang dapat menyetujui pengadaan.');
    }

    Statuspengadaan::where('id', $id)
        ->update([
            'status'  => 'disetujui',
            'user_id' => Auth::id()
        ]);
    Alert::success('Berhasil', 'Pengadaan Barang Disetujui');
    return redirect()->back()->with('success', 'Persetujuan berhasil disimpan.');
}
```

#### Method: `setPenolakan($id)`

**Sebelum:**
```php
public function setPenolakan($id)
{ 
    Statuspengadaan::where('id', $id)
        ->update([
            'status' => 'ditolak',
            'user_id' => Auth::id()
        ]);
    Alert::success('Berhasil', 'Pengadaan Barang Ditolak');
    return redirect()->back()->with('success', 'Penolakan berhasil disimpan.');
}
```

**Sesudah:**
```php
public function setPenolakan($id)
{ 
    // Validasi role sekretaris
    if(auth()->user()->roles !== 'sekretaris'){
        abort(403, 'Hanya Sekretaris yang dapat menolak pengadaan.');
    }

    Statuspengadaan::where('id', $id)
        ->update([
            'status' => 'ditolak',
            'user_id' => Auth::id()
        ]);
    Alert::success('Berhasil', 'Pengadaan Barang Ditolak');
    return redirect()->back()->with('success', 'Penolakan berhasil disimpan.');
}
```

**Perubahan:**
- ✅ Ditambahkan validasi role Sekretaris pada method `setPersetujuan`
- ✅ Ditambahkan validasi role Sekretaris pada method `setPenolakan`
- ✅ Mengembalikan error 403 jika user bukan Sekretaris

---

### 3. **Blade Views**

#### a. Sidebar (resources/views/layouts/main.blade.php)

**Direktur Menu - Sebelum:**
```blade
{{-- Menu untuk roles Direktur --}}
@if($users->roles === 'direktur');
<li class="sidebar-item">
    <a class="sidebar-link" href="/home">
        <i class="bi bi-speedometer2"></i> <span class="align-middle">Dashboard</span>
    </a>
</li>

<li class="sidebar-header">
    Data Master
</li>
<!-- ... Data Master items ... -->

<li class="sidebar-header">
    Pengadaan Barang
</li>
<li class="sidebar-item">
    <a class="sidebar-link" href="/permintaan/">
        <i class="bi bi-card-list"></i> <span class="align-middle">Permintaan</span>
    </a>
</li>

<li class="sidebar-header">
    Laporan
</li>
<!-- ... Laporan items ... -->
@endif
```

**Direktur Menu - Sesudah:**
```blade
{{-- Menu untuk roles Direktur --}}
@if($users->roles === 'direktur');
<li class="sidebar-item">
    <a class="sidebar-link" href="/home">
        <i class="bi bi-speedometer2"></i> <span class="align-middle">Dashboard</span>
    </a>
</li>

<li class="sidebar-header">
    Data Master
</li>
<!-- ... Data Master items ... -->

<li class="sidebar-header">
    Laporan
</li>
<!-- ... Laporan items ... -->
@endif
```

**Perubahan:**
- ✅ Menu "Pengadaan Barang" beserta "Permintaan" dihapus dari sidebar Direktur
- ✅ Direktur hanya melihat Dashboard, Data Master, Laporan, Riwayat, dan Pengaturan

#### b. Permintaan Index (resources/views/permintaan/index.blade.php)

**Sebelum:**
```blade
@if (auth()->user()->roles === 'direktur')
    <td>
        @if ($permintaan->status == 'pending')
        <!-- Tombol Setuju/Tolak untuk Direktur -->
        @else
            <a href="/permintaan/{{ $permintaan->id }}/edit" class="btn btn-primary d-inline mb-2">
                <i class="bi bi-plus-square-fill"></i> Kirim Catatan
            </a>
        @endif
    </td>
@else
   <td>
        <a class="btn btn-primary" href="/permintaan/laporan-pengadaan/{{ $permintaan->id }}" target="_blank" role="button">
            <i class="bi bi-printer"></i>&nbsp; Cetak
        </a>                    
   </td>
@endif
```

**Sesudah:**
```blade
@if (auth()->user()->roles === 'sekretaris')
    <td>
        @if ($permintaan->status == 'pending')
        <!-- Tombol Setuju/Tolak untuk Sekretaris -->
        @else
            <a href="/permintaan/{{ $permintaan->id }}/edit" class="btn btn-primary d-inline mb-2">
                <i class="bi bi-plus-square-fill"></i> Kirim Catatan
            </a>
        @endif
    </td>
@else
   <td>
        <a class="btn btn-primary" href="/permintaan/laporan-pengadaan/{{ $permintaan->id }}" target="_blank" role="button">
            <i class="bi bi-printer"></i>&nbsp; Cetak
        </a>                    
   </td>
@endif
```

**Perubahan:**
- ✅ Kondisi `@if (auth()->user()->roles === 'direktur')` diubah menjadi `@if (auth()->user()->roles === 'sekretaris')`
- ✅ Tombol "Setuju", "Tolak", dan "Kirim Catatan" kini hanya ditampilkan untuk Sekretaris

---

## 🔐 Hak Akses yang Berubah

### Direktur (Sebelum):
- ✅ Lihat daftar Permintaan
- ✅ Menyetujui Pengadaan (tombol Setuju)
- ✅ Menolak Pengadaan (tombol Tolak)
- ✅ Kirim Catatan untuk Pengadaan yang ditolak
- ✅ Menu Permintaan ada di sidebar

### Direktur (Sesudah):
- ❌ Lihat daftar Permintaan (akses ditolak 403)
- ❌ Menyetujui Pengadaan
- ❌ Menolak Pengadaan
- ❌ Kirim Catatan untuk Pengadaan
- ❌ Menu Permintaan TIDAK ada di sidebar

### Sekretaris (Sebelum):
- ✅ Kelola Data User
- ✅ Lihat daftar Permintaan
- ✅ Cetak Laporan Pengadaan

### Sekretaris (Sesudah):
- ✅ Kelola Data User
- ✅ Lihat daftar Permintaan
- ✅ Menyetujui Pengadaan (tombol Setuju) - **BARU**
- ✅ Menolak Pengadaan (tombol Tolak) - **BARU**
- ✅ Kirim Catatan untuk Pengadaan yang ditolak - **BARU**
- ✅ Cetak Laporan Pengadaan

---

## ✅ Checklist Verifikasi

- [x] Routes diupdate dengan middleware hanya untuk Sekretaris
- [x] Methods `setPersetujuan` dan `setPenolakan` memvalidasi role Sekretaris
- [x] Menu Permintaan dihapus dari sidebar Direktur
- [x] Tombol aksi (Setuju/Tolak/Kirim Catatan) menampilkan untuk Sekretaris saja
- [x] Endpoint `/permintaan` hanya bisa diakses oleh Sekretaris
- [x] Endpoint `/permintaan/{id}/setuju` hanya bisa diakses oleh Sekretaris
- [x] Endpoint `/permintaan/{id}/tolak` hanya bisa diakses oleh Sekretaris

---

## 📝 Testing Recommendations

### Test Case 1: Akses Direktur ke Permintaan
```
1. Login sebagai Direktur
2. Navigasi ke sidebar - menu "Permintaan" tidak ada ✓
3. Coba akses langsung URL /permintaan
4. Harapan: Error 403 "Unauthorized action" ✓
```

### Test Case 2: Akses Sekretaris ke Permintaan
```
1. Login sebagai Sekretaris
2. Menu "Permintaan" ada di sidebar ✓
3. Klik menu Permintaan
4. Halaman terbuka dengan daftar pengadaan ✓
5. Tombol Setuju/Tolak muncul untuk status pending ✓
6. Tombol Kirim Catatan muncul untuk status selain pending ✓
```

### Test Case 3: Aksi Setuju/Tolak
```
1. Login sebagai Sekretaris
2. Klik tombol Setuju pada pengadaan status pending
3. Harapan: Status berubah menjadi "disetujui" ✓
4. Klik tombol Tolak pada pengadaan status pending
5. Harapan: Status berubah menjadi "ditolak" ✓
```

### Test Case 4: Aksi Kirim Catatan
```
1. Login sebagai Sekretaris
2. Pilih pengadaan dengan status ditolak
3. Klik tombol "Kirim Catatan"
4. Halaman edit terbuka untuk menambah catatan ✓
5. Catatan disimpan dengan role user_id = Sekretaris ✓
```

---

## 🔧 Rollback (Jika Diperlukan)

Untuk membatalkan perubahan ini, jalankan:
```bash
git checkout HEAD -- routes/web.php app/Http/Controllers/StatusPengadaanController.php resources/views/layouts/main.blade.php resources/views/permintaan/index.blade.php
```

Atau gunakan file diff yang telah disimpan:
```bash
git apply -R perubahan_restrukturisasi.diff
```

---

## 📅 Tanggal Implementasi

- **Tanggal**: 16 Januari 2026
- **Diimplementasikan oleh**: AI Assistant
- **File yang Diubah**: 4 files
- **Total Perubahan**: 46 insertions(+), 55 deletions(-)

---

## ⚠️ Catatan Penting

1. **Backup Data**: Pastikan semua data pengadaan telah di-backup sebelum melakukan implementasi ini di production.
2. **Testing**: Lakukan testing menyeluruh dengan user role Direktur dan Sekretaris sebelum go-live.
3. **Dokumentasi User**: Berikan panduan kepada user bahwa fitur Permintaan kini hanya tersedia untuk Sekretaris.
4. **Audit Trail**: Log semua aksi Sekretaris untuk audit compliance.

---

**Status**: ✅ **SELESAI DAN SIAP TESTING**
