# 📐 DOKUMENTASI DIAGRAM - SIMASET (Sistem Informasi Manajemen Aset)

**Tanggal**: 18 Januari 2026  
**Status**: ✅ Complete  
**Versi**: 1.0

---

## 📋 Daftar Isi

1. [Usecase Diagram](#usecase-diagram)
2. [Activity Diagram: Pengajuan Pengadaan](#activity-diagram-pengajuan-pengadaan)
3. [Activity Diagram: Dashboard Filtering](#activity-diagram-dashboard-filtering)
4. [Class Diagram: Data Model](#class-diagram-data-model)
5. [Sequence Diagram: Alur Sistem](#sequence-diagram-alur-sistem)

---

## 🎭 Usecase Diagram

### Deskripsi
Diagram ini menunjukkan interaksi antara dua role utama (Admin dan User) dengan fitur-fitur utama sistem SIMASET.

```mermaid
graph TD
    subgraph "Sistem SIMASET"
        LOGIN["🔐 Login"]
        DASHBOARD["📊 Dashboard"]
        MANAJEMEN_ASET["📦 Manajemen Aset"]
        MANAJEMEN_MASTER["⚙️ Manajemen Master Data"]
        PENGAJUAN["📝 Pengajuan Pengadaan"]
        VERIFIKASI["✅ Verifikasi Status"]
        LAPORAN["📄 Laporan & Analytics"]
    end
    
    subgraph "Admin"
        ADMIN["👨‍💼 Admin<br/>Sekretaris"]
    end
    
    subgraph "User"
        USER["👤 User<br/>Kepala Usaha"]
    end
    
    ADMIN -->|Login| LOGIN
    USER -->|Login| LOGIN
    LOGIN -->|✓ Autentikasi| DASHBOARD
    
    ADMIN -->|Lihat Global| DASHBOARD
    USER -->|Lihat Private| DASHBOARD
    
    ADMIN -->|Kelola Semua| MANAJEMEN_ASET
    USER -->|Kelola Milik Sendiri| MANAJEMEN_ASET
    
    ADMIN -->|Setup Master| MANAJEMEN_MASTER
    MANAJEMEN_MASTER -->|Kategori, Lokasi, Satuan| MANAJEMEN_ASET
    
    USER -->|Buat Pengajuan| PENGAJUAN
    PENGAJUAN -->|Data Masuk| DASHBOARD
    
    ADMIN -->|Verifikasi & Approve| VERIFIKASI
    VERIFIKASI -->|Update Status| PENGAJUAN
    
    ADMIN -->|Lihat Semua Data| LAPORAN
    USER -->|Lihat Data Sendiri| LAPORAN
    
    style ADMIN fill:#e1f5ff,stroke:#01579b,stroke-width:3px
    style USER fill:#f3e5f5,stroke:#4a148c,stroke-width:3px
    style LOGIN fill:#fff3e0,stroke:#e65100,stroke-width:2px
    style DASHBOARD fill:#e8f5e9,stroke:#1b5e20,stroke-width:2px
    style PENGAJUAN fill:#fce4ec,stroke:#880e4f,stroke-width:2px
    style VERIFIKASI fill:#e0f2f1,stroke:#004d40,stroke-width:2px
```

### Penjelasan Fitur Utama

| Fitur | Admin | User | Deskripsi |
|-------|-------|------|-----------|
| **Login** | ✅ | ✅ | Autentikasi sistem |
| **Dashboard** | 🌍 Global | 👤 Private | Admin lihat semua data, User lihat milik sendiri |
| **Manajemen Aset** | Semua | Milik Sendiri | Kelola data barang/aset |
| **Master Data** | Setup ✅ | Readonly | Kategori, Lokasi, Satuan |
| **Pengajuan Pengadaan** | Verifikasi | Buat | User membuat, Admin verifikasi |
| **Status Pengadaan** | Ubah Status | Lihat | Admin ubah ke Disetujui/Ditolak |
| **Laporan** | Semua Data | Data Sendiri | Analytics & reporting |

---

## 🔄 Activity Diagram: Pengajuan Pengadaan

### Alur Proses Pengajuan Barang dari User hingga Approval Admin

```mermaid
graph TD
    A["👤 User Membuka Form<br/>Pengajuan Pengadaan"] -->|Akses /pengadaan/create| B["📋 Form Pengajuan<br/>Ditampilkan"]
    
    B -->|Input:<br/>- Nama Pengadaan<br/>- Quantity<br/>- Deskripsi<br/>- Lokasi| C["✏️ User Mengisi<br/>Form Data"]
    
    C -->|Click Submit| D{"📊 Validasi<br/>Data"}
    
    D -->|❌ Ada Error| E["⚠️ Tampil Error<br/>Message"]
    E -->|User Perbaiki| C
    
    D -->|✅ Valid| F["💾 Simpan ke Database<br/>Pengadaan Table"]
    
    F -->|Auto Set:<br/>- user_id = auth()->id()<br/>- status = 'pending'<br/>- tanggal_pengajuan = now()| G["📥 Data Masuk<br/>dengan Status PENDING"]
    
    G -->|Buat Record| H["📌 Buat Status History<br/>Statuspengadaan Table"]
    
    H -->|Status = pending| I["✅ Success Alert<br/>Redirect ke List"]
    
    I -->|Akses /pengadaan| J["📋 List Pengajuan<br/>User"]
    
    J -->|Filter:<br/>WHERE user_id = auth()->id()| K["👤 User Lihat Data<br/>Milik Sendiri Saja"]
    
    K -->|Admin Login| L["👨‍💼 Admin Dashboard"]
    
    L -->|Akses /permintaan| M["📋 List Permintaan<br/>Admin (Global)"]
    
    M -->|JOIN dengan<br/>statuspengadaans| N["🌍 Admin Lihat<br/>SEMUA Pengajuan"]
    
    N -->|Klik Detail/<br/>Edit Status| O["🔍 Admin Review<br/>Data Pengajuan"]
    
    O -->|Tombol Approve| P{"❓ Admin<br/>Putuskan"}
    
    P -->|Setujui| Q["✅ Update Status<br/>ke 'Disetujui'"]
    P -->|Tolak| R["❌ Update Status<br/>ke 'Ditolak'"]
    P -->|Edit| S["✏️ Tambah Catatan<br/>di Field catatan"]
    
    Q -->|Update| T["📊 Status History<br/>Terupdate"]
    R -->|Update| T
    S -->|Save| T
    
    T -->|Success Alert| U["🔔 Notification<br/>Sistem"]
    
    U -->|User Lihat List| V["👤 User Lihat Status<br/>Pengajuannya Berubah"]
    
    style A fill:#f3e5f5,stroke:#4a148c,stroke-width:2px
    style G fill:#fff3e0,stroke:#e65100,stroke-width:2px
    style L fill:#e1f5ff,stroke:#01579b,stroke-width:2px
    style N fill:#e1f5ff,stroke:#01579b,stroke-width:2px
    style Q fill:#e8f5e9,stroke:#1b5e20,stroke-width:2px
    style R fill:#ffebee,stroke:#b71c1c,stroke-width:2px
```

### Penjelasan Alur

| Tahap | Deskripsi | Kode Terkait |
|-------|-----------|--------------|
| **User Input** | User mengisi form pengajuan | `PengadaanController::create()` |
| **Validasi** | Laravel validate rules diterapkan | `$request->validate()` |
| **Simpan Data** | Set user_id, status, tanggal otomatis | `Pengadaan::create($validated)` |
| **Status Pending** | Record awal dengan status = 'pending' | `Statuspengadaan::create()` |
| **User List** | User hanya lihat data sendiri | `WHERE user_id = auth()->id()` |
| **Admin List** | Admin lihat semua (global view) | `JOIN statuspengadaans` (no filter) |
| **Admin Review** | Admin bisa lihat, edit, approve/reject | `Authorization check` |
| **Status Update** | Ubah ke Disetujui/Ditolak + catatan | `Statuspengadaan::update()` |
| **User Notifikasi** | User lihat status berubah | Dashboard refresh |

---

## 📊 Activity Diagram: Dashboard Filtering

### Bagaimana Dashboard Menampilkan Data Berbeda untuk User vs Admin

```mermaid
graph TD
    Start["🌐 User Akses<br/>Dashboard /home"] -->|Request| A["🔐 Middleware Auth<br/>Check"]
    
    A -->|Valid| B["👤 HomeController<br/>index()"]
    
    B -->|Get Current User| C["🔍 Get auth()->user()"]
    
    C -->|Check Role| D{"❓ roles<br/>== 'admin'?"}
    
    D -->|❌ NO<br/>Role = 'user'| E["👤 USER PATH"]
    D -->|✅ YES<br/>Role = 'admin'| F["👨‍💼 ADMIN PATH"]
    
    subgraph USER_FILTER["👤 USER FILTER PATH"]
        E -->|1. Chart Query| E1["📈 Barang<br/>WHERE user_id = auth()->id()"]
        E1 -->|GROUP BY YEAR| E2["📉 Chart Data<br/>User's Yearly Acquisitions"]
        
        E -->|2. Counter:<br/>Total Aset| E3["Barang<br/>WHERE user_id = count()"]
        
        E -->|3. Counter:<br/>Kategori| E4["Kategori<br/>WHERE user_id = count()"]
        
        E -->|4. Counter:<br/>Lokasi| E5["Lokasi<br/>WHERE user_id = count()"]
        
        E -->|5. Counter:<br/>User Aktif| E6["User<br/>Global Count()"]
        
        E2 --> E_VIEW["👤 Pass Data<br/>to View<br/>(Filtered)"]
        E3 --> E_VIEW
        E4 --> E_VIEW
        E5 --> E_VIEW
        E6 --> E_VIEW
    end
    
    subgraph ADMIN_FILTER["👨‍💼 ADMIN FILTER PATH"]
        F -->|1. Chart Query| F1["📈 Barang<br/>NO FILTER"]
        F1 -->|GROUP BY YEAR| F2["📉 Chart Data<br/>All Yearly Acquisitions"]
        
        F -->|2. Counter:<br/>Total Aset| F3["Barang<br/>all()->count()"]
        
        F -->|3. Counter:<br/>Kategori| F4["Kategori<br/>all()->count()"]
        
        F -->|4. Counter:<br/>Lokasi| F5["Lokasi<br/>all()->count()"]
        
        F -->|5. Counter:<br/>User Aktif| F6["User<br/>Global Count()"]
        
        F2 --> F_VIEW["🌍 Pass Data<br/>to View<br/>(Global)"]
        F3 --> F_VIEW
        F4 --> F_VIEW
        F5 --> F_VIEW
        F6 --> F_VIEW
    end
    
    E_VIEW -->|Render| DISPLAY_USER["📱 Dashboard Display<br/>(USER)"]
    F_VIEW -->|Render| DISPLAY_ADMIN["📱 Dashboard Display<br/>(ADMIN)"]
    
    DISPLAY_USER -->|Show Counters| USER_DISPLAY["✅ Total Aset: 5<br/>✅ Kategori: 3<br/>✅ Lokasi: 2<br/>✅ User Aktif: 3"]
    
    DISPLAY_ADMIN -->|Show Counters| ADMIN_DISPLAY["✅ Total Aset: 45<br/>✅ Kategori: 12<br/>✅ Lokasi: 8<br/>✅ User Aktif: 3"]
    
    USER_DISPLAY -->|Only User's Data| END_USER["📊 Dashboard Aman<br/>Data Terisolasi"]
    ADMIN_DISPLAY -->|Global Summary| END_ADMIN["📊 Dashboard Lengkap<br/>Ringkasan Sistem"]
    
    style E fill:#f3e5f5,stroke:#4a148c,stroke-width:2px
    style F fill:#e1f5ff,stroke:#01579b,stroke-width:2px
    style E_VIEW fill:#f3e5f5,stroke:#4a148c,stroke-width:2px
    style F_VIEW fill:#e1f5ff,stroke:#01579b,stroke-width:2px
    style END_USER fill:#f3e5f5,stroke:#4a148c,stroke-width:2px
    style END_ADMIN fill:#e1f5ff,stroke:#01579b,stroke-width:2px
```

### Penjelasan Data Flow

#### User View (Filtered)
```
Dashboard User
├─ Total Aset:   5  (hanya milik user)
├─ Kategori:     3  (hanya milik user)
├─ Lokasi:       2  (hanya milik user)
├─ User Aktif:   3  (global, sistem info)
└─ Chart: Trend aktual pengadaan user per tahun
```

#### Admin View (Global)
```
Dashboard Admin
├─ Total Aset:   45 (semua user digabung)
├─ Kategori:     12 (semua user digabung)
├─ Lokasi:       8  (semua user digabung)
├─ User Aktif:   3  (global, sistem info)
└─ Chart: Trend pengadaan semua user per tahun
```

---

## 📦 Class Diagram: Data Model

### Relasi Entitas dan Atribut Utama

```mermaid
erDiagram
    USER ||--o{ BARANG : "has"
    USER ||--o{ KATEGORI : "has"
    USER ||--o{ LOKASI : "has"
    USER ||--o{ PENGADAAN : "creates"
    BARANG ||--o{ KATEGORI : "belongs_to"
    BARANG ||--o{ LOKASI : "belongs_to"
    BARANG ||--o{ SATUAN : "uses"
    PENGADAAN ||--o{ LOKASI : "uses"
    PENGADAAN ||--o{ STATUSPENGADAAN : "has"
    STATUSPENGADAAN ||--o{ USER : "verified_by"

    USER {
        int id PK
        string name
        string email
        string password
        string roles "admin|user"
        int lokasi_id FK
        timestamp created_at
        timestamp updated_at
    }

    BARANG {
        int id PK
        string kode_barang
        string nama
        string deskripsi
        date tanggal
        decimal harga
        int user_id FK "ownership"
        int kategori_id FK
        int lokasi_id FK
        int satuan_id FK
        timestamp created_at
    }

    KATEGORI {
        int id PK
        string nama
        int user_id FK "ownership"
        timestamp created_at
        timestamp updated_at
    }

    LOKASI {
        int id PK
        string nama_lokasi
        int user_id FK "ownership"
        timestamp created_at
        timestamp updated_at
    }

    SATUAN {
        int id PK
        string nama
        timestamp created_at
    }

    PENGADAAN {
        int id PK
        string nama_pengadaan
        int quantity
        string deskripsi
        int user_id FK "who_requested"
        int lokasi_id FK
        date tanggal_pengajuan
        timestamp created_at
        timestamp updated_at
    }

    STATUSPENGADAAN {
        int id PK
        int pengadaan_id FK
        string status "pending|disetujui|ditolak"
        string catatan
        int user_id FK "who_verified"
        timestamp created_at
        timestamp updated_at
    }
```

### Penjelasan Relasi

| Relasi | Deskripsi |
|--------|-----------|
| **USER → BARANG** | User memiliki multiple barang (1:N) |
| **USER → KATEGORI** | User membuat kategori sendiri (1:N) |
| **USER → LOKASI** | User memiliki lokasi sendiri (1:N) |
| **USER → PENGADAAN** | User mengajukan pengadaan (1:N) |
| **PENGADAAN → STATUSPENGADAAN** | Pengadaan bisa punya multiple status history (1:N) |
| **BARANG → KATEGORI** | Barang milik kategori tertentu (N:1) |
| **BARANG → LOKASI** | Barang ada di lokasi tertentu (N:1) |

### Tabel User_id untuk Privacy Control

```
Tabel yang menggunakan user_id untuk filtering:
✅ BARANG.user_id           → Filter barang milik user
✅ KATEGORI.user_id         → Filter kategori milik user
✅ LOKASI.user_id           → Filter lokasi milik user
✅ PENGADAAN.user_id        → Filter pengajuan milik user
✅ STATUSPENGADAAN.user_id  → Track siapa yang verify
```

---

## 🔄 Sequence Diagram: Alur Sistem Lengkap

### Skenario: User Mengajukan Pengadaan hingga Admin Approve

```mermaid
sequenceDiagram
    actor U as 👤 User
    participant B as 🌐 Browser
    participant LC as Laravel Controller
    participant DB as 💾 Database
    participant AUTH as 🔐 Auth Middleware
    actor A as 👨‍💼 Admin

    U->>B: 1. Akses /pengadaan/create
    B->>AUTH: Cek autentikasi
    AUTH-->>B: ✅ Valid, role=user
    B->>LC: GET PengadaanController@create
    LC-->>B: Return form pengadaan
    B-->>U: Tampil form

    U->>B: 2. Input form:<br/>Nama, Qty, Deskripsi, Lokasi
    U->>B: 3. Click Submit

    B->>LC: POST PengadaanController@store
    LC->>LC: Validasi input
    LC->>DB: Simpan Pengadaan:<br/>user_id=auth()->id()<br/>status=pending
    DB-->>LC: ✅ Record created
    
    LC->>DB: Buat Statuspengadaan:<br/>status=pending
    DB-->>LC: ✅ Status record created
    
    LC-->>B: Alert success + redirect
    B-->>U: ✅ Pengajuan Berhasil!

    U->>B: 4. Akses /pengadaan
    B->>LC: GET PengadaanController@index
    LC->>DB: Query Pengadaan<br/>WHERE user_id=?
    DB-->>LC: Return user's pengadaan
    LC-->>B: Pass filtered data
    B-->>U: Show data milik user

    A->>B: 5. Login sebagai Admin
    B->>AUTH: Cek autentikasi
    AUTH-->>B: ✅ Valid, role=admin
    B->>LC: GET Dashboard
    LC->>DB: Query Pengadaan (no filter)
    DB-->>LC: Return ALL pengadaan
    LC->>DB: Query counters (all data)
    DB-->>LC: Return global counts
    LC-->>B: Pass global data
    B-->>A: Show dashboard global

    A->>B: 6. Akses /permintaan
    B->>LC: GET StatusPengadaanController@index
    LC->>DB: Query Pengadaan (no filter)
    DB-->>LC: Return ALL pengajuan
    LC-->>B: Pass admin list
    B-->>A: Show all pengajuan

    A->>B: 7. Click detail pengajuan user
    B->>LC: GET StatusPengadaanController@show
    LC->>DB: Query specific pengadaan
    DB-->>LC: Return data
    LC->>LC: Auth check: admin? YES
    LC-->>B: Allow access
    B-->>A: Show detail + tombol approve/reject

    A->>B: 8. Click Setujui
    B->>LC: Update status ke 'disetujui'
    LC->>LC: Check admin role
    LC->>DB: Update Statuspengadaan<br/>status=disetujui<br/>user_id=admin_id
    DB-->>LC: ✅ Updated
    LC-->>B: Alert success
    B-->>A: ✅ Status Diperbarui

    U->>B: 9. Lihat list pengajuan
    B->>LC: GET /pengadaan
    LC->>DB: Query WHERE user_id=?
    DB-->>LC: Return dengan status updated
    LC-->>B: Show list
    B-->>U: Status berubah ke Disetujui! ✅
```

---

## 📝 Catatan Implementasi

### Privacy Control Implementation

```
Lokasi File Logika:
├─ app/Http/Controllers/HomeController.php
│  └─ Dashboard filtering by user_id
│
├─ app/Http/Controllers/PengadaanController.php
│  ├─ index() → Filter WHERE user_id
│  ├─ show() → Authorization check
│  ├─ edit() → Authorization check
│  └─ update() → Authorization check
│
├─ app/Http/Controllers/StatusPengadaanController.php
│  ├─ index() → Filter WHERE user_id
│  └─ show() → Authorization check
│
├─ app/Models/Barang.php
│  └─ user_id field untuk ownership
│
├─ app/Models/Kategori.php
│  └─ user_id field untuk ownership
│
├─ app/Models/Lokasi.php
│  └─ user_id field untuk ownership
│
└─ app/Models/Pengadaan.php
   └─ user_id field untuk ownership
```

### Database Schema Key Fields

```sql
-- Ownership Tracking
ALTER TABLE barangs ADD user_id UNSIGNED INTEGER;
ALTER TABLE kategoris ADD user_id UNSIGNED INTEGER;
ALTER TABLE lokasis ADD user_id UNSIGNED INTEGER;
ALTER TABLE pengadaans ADD user_id UNSIGNED INTEGER;
ALTER TABLE statuspengadaans ADD user_id UNSIGNED INTEGER;

-- Foreign Keys
ALTER TABLE barangs ADD FOREIGN KEY (user_id) REFERENCES users(id);
ALTER TABLE kategoris ADD FOREIGN KEY (user_id) REFERENCES users(id);
ALTER TABLE lokasis ADD FOREIGN KEY (user_id) REFERENCES users(id);
ALTER TABLE pengadaans ADD FOREIGN KEY (user_id) REFERENCES users(id);
ALTER TABLE statuspengadaans ADD FOREIGN KEY (user_id) REFERENCES users(id);
```

### Query Filtering Patterns

```php
// USER: Filter by user_id
WHERE user_id = auth()->user()->id

// ADMIN: No filter (show all)
// Default: Barang::all() or empty WHERE clause
```

---

## 🎓 Kesimpulan

Diagram-diagram di atas menunjukkan:

1. **Usecase Diagram** → Interaksi user dan admin dengan sistem
2. **Activity Diagram Pengajuan** → Alur lengkap dari user request hingga admin approve
3. **Activity Diagram Dashboard** → Bagaimana filtering privacy diterapkan
4. **Class Diagram** → Relasi entitas dan field user_id untuk privacy
5. **Sequence Diagram** → Timeline interaksi user-admin-database

Semua diagram ini mencerminkan logika yang sudah diimplementasikan di:
- `HomeController.php` → Dashboard filtering
- `PengadaanController.php` → Pengajuan & privacy
- `StatusPengadaanController.php` → Verifikasi & privacy

**Status**: ✅ Dokumentasi sesuai dengan kode aktual sistem

---

**Created**: 18 Januari 2026  
**Version**: 1.0  
**Format**: Mermaid.js (GitHub-compatible)

