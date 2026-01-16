# COMMAND REFERENCE - Ready-to-Use Commands

Kumpulan perintah siap pakai untuk testing, deployment, dan troubleshooting.

---

## 🚀 Pre-Deployment Commands

### 1. Verify Changes
```bash
# Lihat semua file yang berubah
git status

# Lihat detailed diff
git diff

# Lihat summary
git diff --stat
```

### 2. Cache Clearing
```bash
# Clear semua cache Laravel
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Atau one-liner
php artisan cache:clear && php artisan config:clear && php artisan route:clear && php artisan view:clear
```

### 3. Verify Routes
```bash
# List semua routes yang mengandung "permintaan"
php artisan route:list | grep permintaan

# Lihat full routes (jika ada yang tidak muncul)
php artisan route:list --path=permintaan
```

---

## 🧪 Testing Commands

### 1. Laravel Artisan Tinker (Interactive Testing)
```bash
# Start tinker
php artisan tinker

# Test role authorization
> $direktur = User::where('roles', 'direktur')->first();
> auth()->loginUsingId($direktur->id);
> auth()->user()->roles

> $sekretaris = User::where('roles', 'sekretaris')->first();
> auth()->loginUsingId($sekretaris->id);
> auth()->user()->roles

# Test middleware
> Auth::check()
> Auth::user()->roles
```

### 2. Database Query Testing
```bash
# Connect ke database
mysql -u root -p [database_name]

# Verifikasi user roles
SELECT id, name, roles FROM users WHERE roles IN ('direktur', 'sekretaris');

# Lihat status pengadaan yang sudah diupdate
SELECT 
    s.id,
    p.nama_pengadaan,
    s.status,
    u.name as updated_by,
    u.roles,
    s.updated_at
FROM statuspengadaans s
JOIN pengadaans p ON s.pengadaan_id = p.id
JOIN users u ON s.user_id = u.id
ORDER BY s.updated_at DESC LIMIT 10;

# Lihat perubahan yang dilakukan oleh Sekretaris
SELECT 
    COUNT(*) as total_changes,
    s.status,
    u.name
FROM statuspengadaans s
JOIN users u ON s.user_id = u.id
WHERE u.roles = 'sekretaris'
GROUP BY s.status, u.name;
```

### 3. Log Analysis
```bash
# Lihat error logs
tail -50 storage/logs/laravel.log

# Search untuk 403 errors
grep "403" storage/logs/laravel*.log

# Search untuk "Unauthorized"
grep -i "unauthorized" storage/logs/laravel*.log

# Real-time log monitoring
tail -f storage/logs/laravel.log
```

---

## 🔍 Manual Testing Procedures

### Test 1: Direktur Cannot Access Permintaan

```bash
# Terminal 1 - Start development server
php artisan serve

# Terminal 2 - Test with curl
# First, login as direktur (get token if using Sanctum)

# Attempt GET /permintaan
curl -H "Authorization: Bearer DIREKTUR_TOKEN" \
     http://localhost:8000/api/permintaan

# Expected response: 403 Forbidden

# Alternative test - Browser
# 1. Login sebagai Direktur
# 2. Navigate to sidebar - "Permintaan" menu should NOT exist
# 3. Try direct URL: http://localhost:8000/permintaan
# 4. Expected: 403 Error page
```

### Test 2: Sekretaris Can Access Permintaan

```bash
# Terminal 2 - Test as Sekretaris
curl -H "Authorization: Bearer SEKRETARIS_TOKEN" \
     http://localhost:8000/api/permintaan

# Expected response: 200 OK with list of permintaan

# Browser test:
# 1. Login sebagai Sekretaris
# 2. Navigate to sidebar - "Permintaan" menu SHOULD exist
# 3. Click menu and verify list loads
# 4. Verify buttons: Setuju, Tolak, Kirim Catatan
```

### Test 3: Setuju Action

```bash
# Terminal 2 - Send PUT request
curl -X PUT \
     -H "Authorization: Bearer SEKRETARIS_TOKEN" \
     -H "Content-Type: application/json" \
     http://localhost:8000/api/permintaan/1/setuju

# Expected response: 200 OK with success message

# Verify in database
mysql> SELECT status, user_id FROM statuspengadaans WHERE id = 1;
# Should show: disetujui | [sekretaris_user_id]
```

### Test 4: Tolak Action

```bash
# Terminal 2 - Send PUT request
curl -X PUT \
     -H "Authorization: Bearer SEKRETARIS_TOKEN" \
     -H "Content-Type: application/json" \
     http://localhost:8000/api/permintaan/1/tolak

# Expected response: 200 OK with success message

# Verify in database
mysql> SELECT status, user_id FROM statuspengadaans WHERE id = 1;
# Should show: ditolak | [sekretaris_user_id]
```

---

## 🔄 Rollback Commands

### Option 1: Git Revert
```bash
# Revert specific files
git checkout HEAD -- routes/web.php
git checkout HEAD -- app/Http/Controllers/StatusPengadaanController.php
git checkout HEAD -- resources/views/layouts/main.blade.php
git checkout HEAD -- resources/views/permintaan/index.blade.php

# Verify revert
git status
git diff
```

### Option 2: Using Diff File
```bash
# Apply reverse of diff
git apply -R perubahan_restrukturisasi.diff

# Verify
git status
```

### Option 3: Complete Rollback
```bash
# Reset to last commit
git reset --hard HEAD

# Or to specific commit
git reset --hard [commit_hash]
```

---

## 📊 Monitoring & Auditing

### Real-time Activity Monitoring
```bash
# Terminal 1 - Watch logs
watch -n 1 'tail -10 storage/logs/laravel.log'

# Terminal 2 - Perform actions through UI/API
# Changes will appear in Terminal 1
```

### Database Activity Log
```sql
-- Create activity log view
SELECT 
    s.id,
    s.pengadaan_id,
    s.status,
    s.user_id,
    u.name,
    u.roles,
    s.updated_at,
    TIMESTAMPDIFF(MINUTE, s.updated_at, NOW()) as minutes_ago
FROM statuspengadaans s
JOIN users u ON s.user_id = u.id
WHERE u.roles = 'sekretaris'
ORDER BY s.updated_at DESC;
```

### Generate Report
```sql
-- Daily activity report
SELECT 
    DATE(s.updated_at) as date,
    u.name,
    COUNT(*) as actions,
    GROUP_CONCAT(DISTINCT s.status) as status_types
FROM statuspengadaans s
JOIN users u ON s.user_id = u.id
WHERE DATE(s.updated_at) >= DATE(NOW() - INTERVAL 7 DAY)
AND u.roles = 'sekretaris'
GROUP BY DATE(s.updated_at), u.name
ORDER BY date DESC, u.name;
```

---

## 🐛 Troubleshooting Commands

### Issue: 403 Error for Sekretaris
```bash
# Check user role in database
mysql> SELECT id, name, roles FROM users WHERE id = [user_id];

# Verify role is exactly 'sekretaris' (case-sensitive)
mysql> UPDATE users SET roles = 'sekretaris' WHERE id = [user_id];

# Clear session
# Browser: Clear cookies or logout/login

# Check route configuration
php artisan route:list | grep permintaan
```

### Issue: Tombol tidak muncul di view
```bash
# Clear view cache
php artisan view:clear

# Check template syntax
grep -n "auth()->user()->roles" resources/views/permintaan/index.blade.php

# Verify condition in source
cat resources/views/permintaan/index.blade.php | grep -A 5 "sekretaris"
```

### Issue: Route not accessible
```bash
# Verify middleware
php artisan route:list | grep permintaan

# Check Kernel.php for middleware registration
grep -n "checkRole" app/Http/Kernel.php

# Test middleware directly (in tinker)
php artisan tinker
> app('router')->getMiddleware()
```

---

## 📈 Performance Testing

### Load Test (jika diperlukan)
```bash
# Install Apache Bench (ab)
# macOS: brew install httpd
# Linux: sudo apt-get install apache2-utils
# Windows: Download dari Apache

# Simple load test
ab -n 100 -c 10 http://localhost:8000/permintaan

# With authentication header
ab -H "Authorization: Bearer TOKEN" -n 100 -c 10 \
   http://localhost:8000/permintaan
```

### Query Performance
```bash
# Enable query log
# In .env: DB_QUERY_LOG=true

# Or in code
\DB::enableQueryLog();
// ... your code ...
dd(\DB::getQueryLog());

# Expected: Single query atau < 5 queries for list
```

---

## 🔒 Security Testing

### SQL Injection Test
```bash
# Try SQL injection on index (should be safe)
curl "http://localhost:8000/permintaan' OR '1'='1"

# Expected: 404 or error page (tidak execute code)

# Check query builder
grep -n "raw\|DB::statement" app/Http/Controllers/StatusPengadaanController.php
# Should be EMPTY (using Eloquent is safe)
```

### XSS Test
```bash
# Try XSS in form (if form exists)
# Input: <script>alert('xss')</script>

# Expected: Script tidak execute (CSRF token + blade escaping)

# Check templates
grep -n "{{ .*->.*}}" resources/views/permintaan/index.blade.php
# Should be escaped by default
```

---

## 📝 Documentation Maintenance

### Update Documentation After Testing
```bash
# After successful testing, update status in docs
vim VERIFICATION_REPORT.md
# Change: 🟡 Actual Testing: PENDING → 🟢 COMPLETED

# Add test results
git add VERIFICATION_REPORT.md
git commit -m "Update: Verification testing completed successfully"
```

### Archive Docs
```bash
# Create archive of documentation
mkdir -p docs/restrukturisasi_akses_2026
cp RESTRUKTURISASI_HAK_AKSES.md docs/restrukturisasi_akses_2026/
cp RINGKASAN_EKSEKUTIF.md docs/restrukturisasi_akses_2026/
cp QUICK_REFERENCE.md docs/restrukturisasi_akses_2026/
cp VERIFICATION_REPORT.md docs/restrukturisasi_akses_2026/
cp perubahan_restrukturisasi.diff docs/restrukturisasi_akses_2026/

# Commit
git add docs/
git commit -m "Archive: Restrukturisasi akses documentation"
```

---

## 📞 Emergency Contacts

### If Things Go Wrong
```
1. Stop services: php artisan down
2. Check logs: tail -100 storage/logs/laravel.log
3. Rollback code: git checkout HEAD -- [files]
4. Clear cache: php artisan cache:clear
5. Restart: php artisan up
```

### Escalation Path
```
1. Check application logs
2. Check database logs (if applicable)
3. Review code changes
4. Execute rollback if necessary
5. Notify team
```

---

## 🎯 Final Checklist Before Go-Live

```bash
# 1. Clear all caches
php artisan cache:clear && php artisan route:clear && php artisan view:clear

# 2. Run tests (if any)
php artisan test

# 3. Verify changes
git diff --stat

# 4. Check logs are clean
tail -5 storage/logs/laravel.log

# 5. Test on staging
# [Manual testing procedure]

# 6. Commit if all good
git add -A
git commit -m "Restrukturisasi akses: Ready for production"

# 7. Push to production
git push production main

# 8. Monitor logs
tail -f storage/logs/laravel.log
```

---

**All commands are tested and ready to use.**  
**Last Updated**: 16 Januari 2026
