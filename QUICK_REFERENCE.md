# QUICK REFERENCE - API & ACL Changes

## 🔐 Access Control List (ACL) - Sebelum vs Sesudah

### Route Access Matrix

```
                              DIREKTUR        SEKRETARIS      KEPALA USAHA
/permintaan (GET)             ❌ (403)        ✅              -
/permintaan (POST)            ❌ (403)        ✅              -
/permintaan/{id} (GET)        ❌ (403)        ✅              -
/permintaan/{id}/edit (GET)   ❌ (403)        ✅              -
/permintaan/{id} (PUT)        ❌ (403)        ✅              -
/permintaan/{id}/setuju       ❌ (403)        ✅              -
/permintaan/{id}/tolak        ❌ (403)        ✅              -
/permintaan/laporan/{id}      ❌ (403)        ✅              -
/datauser                     -               ✅              -
/pengadaan                    -               -               ✅
```

---

## 📝 Code Reference

### 1. Routes Changes

**Before:**
```php
Route::middleware(['auth', 'checkRole:direktur,sekretaris'])->group(function(){
    Route::resource('/permintaan', StatusPengadaanController::class);
});

Route::group(['middleware' => ['auth', 'direktur']], function(){
    Route::put('/permintaan/{id}/setuju', [StatusPengadaanController::class, 'setPersetujuan']);
    Route::put('/permintaan/{id}/tolak', [StatusPengadaanController::class, 'setPenolakan']);
});
```

**After:**
```php
Route::middleware(['auth', 'checkRole:sekretaris'])->group(function(){
    Route::resource('/permintaan', StatusPengadaanController::class);
    Route::put('/permintaan/{id}/setuju', [StatusPengadaanController::class, 'setPersetujuan']);
    Route::put('/permintaan/{id}/tolak', [StatusPengadaanController::class, 'setPenolakan']);
    Route::get('permintaan/laporan-pengadaan/{id}', [StatusPengadaanController::class, 'cetakPengadaanBarang']);
});
```

---

### 2. Controller Changes

**Before:**
```php
public function setPersetujuan($id) {
    Statuspengadaan::where('id', $id)->update([
        'status'  => 'disetujui',
        'user_id' => Auth::id()
    ]);
    // ...
}
```

**After:**
```php
public function setPersetujuan($id) {
    if(auth()->user()->roles !== 'sekretaris'){
        abort(403, 'Hanya Sekretaris yang dapat menyetujui pengadaan.');
    }
    
    Statuspengadaan::where('id', $id)->update([
        'status'  => 'disetujui',
        'user_id' => Auth::id()
    ]);
    // ...
}
```

---

### 3. Blade Template Changes

**Before:**
```blade
@if (auth()->user()->roles === 'direktur')
    <button>Setuju</button>
    <button>Tolak</button>
    <button>Kirim Catatan</button>
@endif
```

**After:**
```blade
@if (auth()->user()->roles === 'sekretaris')
    <button>Setuju</button>
    <button>Tolak</button>
    <button>Kirim Catatan</button>
@endif
```

---

## 🧪 Testing Endpoints

### Test dengan cURL

```bash
# Test akses direktur ke permintaan (should 403)
curl -H "Authorization: Bearer TOKEN_DIREKTUR" \
     http://localhost/permintaan

# Test akses sekretaris ke permintaan (should 200)
curl -H "Authorization: Bearer TOKEN_SEKRETARIS" \
     http://localhost/permintaan

# Test setuju pengadaan sebagai direktur (should 403)
curl -X PUT -H "Authorization: Bearer TOKEN_DIREKTUR" \
     http://localhost/permintaan/1/setuju

# Test setuju pengadaan sebagai sekretaris (should 200)
curl -X PUT -H "Authorization: Bearer TOKEN_SEKRETARIS" \
     http://localhost/permintaan/1/setuju
```

---

## 🔍 Database Impact

### Unchanged Tables
- ❌ No schema changes
- ❌ No table migrations needed
- ❌ No data migration required

### Affected Data
- ✅ `statuspengadaans.user_id` akan diisi dengan ID Sekretaris
- ✅ `statuspengadaans.status` akan di-update oleh Sekretaris

**Query untuk verifikasi akses:**
```sql
-- Lihat yang mengubah status
SELECT id, status, user_id, updated_at 
FROM statuspengadaans 
ORDER BY updated_at DESC;

-- Join dengan users untuk lihat role
SELECT s.id, s.status, u.name, u.roles, s.updated_at
FROM statuspengadaans s
JOIN users u ON s.user_id = u.id
WHERE s.status IN ('disetujui', 'ditolak')
ORDER BY s.updated_at DESC;
```

---

## 🛡️ Security Checklist

- [x] Routes dilindungi middleware
- [x] Controller methods memvalidasi role
- [x] No SQL injection vulnerability
- [x] Authorization sebelum database operation
- [x] Clear error messages (tidak expose system info)
- [x] Audit trail (user_id disimpan)

---

## 🐛 Troubleshooting

### Issue: Error 403 ketika Sekretaris akses Permintaan

**Kemungkinan penyebab:**
1. User role tidak 'sekretaris' di database
2. User belum login
3. Session expired

**Solusi:**
```sql
-- Verifikasi role user
SELECT id, name, roles FROM users WHERE email = 'user@example.com';

-- Update role jika perlu
UPDATE users SET roles = 'sekretaris' WHERE id = X;
```

### Issue: Tombol Setuju tidak muncul di view

**Kemungkinan penyebab:**
1. Cache blade template
2. User role tidak sesuai dengan kondisi
3. Status pengadaan bukan 'pending'

**Solusi:**
```php
// Di controller
dd([
    'user_role' => auth()->user()->roles,
    'status' => $permintaan->status,
]);

// Clear cache
php artisan view:clear
php artisan cache:clear
```

### Issue: User Direktur bisa akses /permintaan

**Kemungkinan penyebab:**
1. Cache routes
2. Middleware tidak bekerja
3. User memiliki override permission

**Solusi:**
```bash
# Clear routes cache
php artisan route:clear
php artisan route:cache

# Cek routes
php artisan route:list | grep permintaan
```

---

## 📋 Monitoring Queries

### Audit Permintaan per User

```sql
SELECT 
    DATE(s.updated_at) as tgl,
    u.name as user,
    u.roles as role,
    COUNT(*) as jumlah_aksi,
    GROUP_CONCAT(DISTINCT s.status) as statuses
FROM statuspengadaans s
JOIN users u ON s.user_id = u.id
WHERE DATE(s.updated_at) >= DATE(NOW() - INTERVAL 7 DAY)
GROUP BY DATE(s.updated_at), u.name, u.roles
ORDER BY DATE(s.updated_at) DESC;
```

### Failed Access Attempts (dari logs)

```bash
# Cari error 403 di laravel logs
grep "403" storage/logs/laravel*.log

# Dengan timestamp
grep -E "permission|Unauthorized" storage/logs/laravel*.log | tail -20
```

---

## 🔄 Migration Path (untuk update)

Jika di masa depan perlu ubah permission lagi:

```php
// Opsi 1: Tambah role baru
Route::middleware(['auth', 'checkRole:sekretaris,verifikator'])->group(function(){
    Route::resource('/permintaan', StatusPengadaanController::class);
});

// Opsi 2: Gunakan permission sistem
if (auth()->user()->can('approve_procurement')) {
    // show approve button
}

// Opsi 3: Gunakan role-based gates
Gate::define('approve-procurement', function (User $user) {
    return $user->roles === 'sekretaris';
});
```

---

## 📞 Error Responses

### 403 Forbidden
```json
{
    "message": "Unauthorized action.",
    "status": 403
}
```

### 401 Unauthenticated
```json
{
    "message": "Unauthenticated.",
    "status": 401
}
```

---

## 🎯 Success Response

### Setuju Pengadaan
```json
{
    "success": true,
    "message": "Persetujuan berhasil disimpan.",
    "data": {
        "status": "disetujui",
        "user_id": 5,
        "updated_at": "2026-01-16 10:30:00"
    }
}
```

---

**Last Updated**: 16 Januari 2026
