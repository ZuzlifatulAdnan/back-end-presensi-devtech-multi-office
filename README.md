# JagoHRIS - Laravel Absensi Backend

Sistem backend comprehensive untuk aplikasi absensi karyawan berbasis Laravel dengan dukungan API untuk mobile apps (Flutter) dan web dashboard untuk admin menggunakan Filament.

---

## 🚀 How to Install

### Prerequisites

-   PHP 8.3.22+
-   Composer
-   MySQL/MariaDB
-   Node.js & NPM (untuk frontend assets)
-   Firebase Project (untuk FCM)

### Installation Steps

1. **Clone Repository**

```bash
extract
cd laravel-absensi-backend-master
```

2. **Install Dependencies**

```bash
composer install
npm install
```

3. **Environment Configuration**

```bash
cp .env.example .env
php artisan key:generate
```

4. **Database Setup**

```bash
# Update .env dengan database credentials
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jagohris_db
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Run migrations & seeders
php artisan migrate
php artisan db:seed
```

5. **Storage Setup**

```bash
php artisan storage:link
```

6. **Firebase Configuration**

```bash
# Add Firebase credentials ke .env
FIREBASE_PROJECT_ID=your_project_id
FIREBASE_PRIVATE_KEY="your_private_key"
FIREBASE_CLIENT_EMAIL=your_client_email
```

7. **Email Configuration (Brevo/Resend)**

```bash
# For Brevo
MAIL_DRIVER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your_brevo_username
MAIL_PASSWORD=your_brevo_password

# For Resend (alternative)
RESEND_API_KEY=your_resend_api_key
```

8. **Development Server**

```bash
# Start Laravel server
php artisan serve

# Start Vite dev server (untuk assets)
npm run dev

# Or build for production
npm run build
```

### 🔑 Default Login Credentials

**Admin Access:**

-   URL: `http://localhost:8000/admin/login`
-   Email: `admin@admin.com`
-   Password: `password`

_Note: Auto-fill enabled in development environment_

---

## 🚀 Tech Stack

-   **Framework**: Laravel 12.31.1
-   **PHP Version**: 8.3.22
-   **Database**: MySQL
-   **Authentication**: Laravel Sanctum + Fortify (v1.30.0)
-   **Admin Panel**: Filament v4.0.20
-   **Frontend Framework**: Livewire v3.6.4
-   **Queue System**: Database Queue
-   **Email Service**: Brevo SMTP / Resend
-   **PDF Generation**: DomPDF
-   **QR Code**: Simple QR Code + Endroid QR Code
-   **Push Notifications**: Firebase Cloud Messaging (FCM)
-   **Code Quality**: Laravel Pint (v1.25.1)
-   **Testing**: PHPUnit (v11.5.41)
-   **Development**: Laravel Sail (v1.46.0)

---

## ✨ Fitur Utama

### 🏢 Company Management

-   **Company Settings**: Konfigurasi detail perusahaan (nama, alamat, email, lokasi GPS)
-   **Location-based Attendance**: Pengaturan koordinat kantor dengan radius validasi
-   **Attendance Types**: Dukungan GPS-based atau QR Code-based attendance
-   **Work Hours Configuration**: Pengaturan jam kerja standar perusahaan

### 👥 Employee Management

-   **User Registration & Profile**: Manajemen data karyawan lengkap
-   **Department & Position**: Struktur organisasi dengan departemen dan jabatan
-   **Shift Management**: Sistem shift kerja fleksibel dengan cross-day support
-   **Face Recognition**: Face embedding untuk autentikasi tambahan
-   **Role-based Access**: Admin, Supervisor, dan Employee roles

### 📊 Advanced HR Features

-   **Leave Management**: Sistem cuti dengan berbagai tipe leave dan balance tracking
-   **Holiday Management**: Kalender hari libur nasional dan perusahaan
-   **Overtime Management**: Tracking lembur dengan approval workflow
-   **Weekend Configuration**: Pengaturan hari kerja dan weekend
-   **Shift Assignment**: Assignment karyawan ke shift tertentu

### 📱 Mobile API Features (Flutter Integration)

#### 🔐 Authentication & Profile

-   **Login/Logout**: Sanctum token-based authentication dengan FCM integration
-   **Profile Management**: Update profile lengkap dengan foto
-   **Face Recognition**: Update face embedding untuk autentikasi biometric
-   **FCM Token Management**: Push notifications untuk approval status

#### ⏰ Attendance Management

-   **Check-in/Check-out**: GPS location tracking dengan validasi radius
-   **Location Validation**: Validasi kehadiran berdasarkan koordinat perusahaan
-   **QR Code Scanning**: Alternative attendance menggunakan QR code harian
-   **Real-time Status**: Status kehadiran real-time (is-checkin, is-checkout)
-   **Attendance History**: Riwayat attendance dengan filter tanggal dan export

#### 📝 Permission & Leave Management

-   **Leave Request**: Pengajuan cuti dengan berbagai tipe (annual, sick, etc.)
-   **Permission Request**: Pengajuan izin dengan reason dan upload dokumen
-   **Document Upload**: Support upload gambar pendukung (surat dokter, dll)
-   **Approval Tracking**: Real-time status tracking approval
-   **Leave Balance**: Monitoring sisa cuti per tipe leave

#### ⏱️ Overtime Management

-   **Start/End Overtime**: Tracking lembur dengan timestamp akurat
-   **Document Support**: Upload dokumen pendukung saat start overtime
-   **Status Tracking**: Status pending, approved, rejected dengan notifikasi
-   **Monthly Reports**: List overtime dengan filter bulan dan tahun
-   **Today's Status**: Check status overtime hari ini

#### 📋 Notes Management

-   **Personal Notes**: CRUD catatan personal untuk reminder
-   **Task Management**: Notes untuk memo dan task tracking
-   **Search & Filter**: Pencarian dan filter notes berdasarkan kategori

#### 🏢 Company Information

-   **Company Profile**: Informasi lengkap perusahaan
-   **Office Location**: Data lokasi kantor dengan koordinat GPS
-   **Work Settings**: Radius validasi, jam kerja, tipe attendance
-   **Shift Information**: Detail shift kerja karyawan

### 🖥️ Web Dashboard Features (Filament Admin Panel)

#### 👨‍💼 Employee Management

-   **User CRUD**: Manajemen karyawan dengan roles, departments, positions
-   **Department Management**: Struktur organisasi departemen
-   **Position/Jabatan Management**: Manajemen jabatan dan level
-   **Shift Management**: Pengaturan shift kerja dengan grace period
-   **Shift Assignment**: Assignment karyawan ke shift tertentu

#### 🏢 Company & Settings

-   **Company Settings**: Konfigurasi lokasi, radius, dan attendance type
-   **Holiday Management**: Kalender hari libur nasional dan perusahaan
-   **Weekend Configuration**: Pengaturan hari kerja dan weekend
-   **Leave Types**: Manajemen tipe-tipe cuti (annual, sick, maternity, dll)
-   **Leave Balance**: Monitoring dan update balance cuti karyawan

#### 📊 Attendance & Monitoring

-   **Real-time Monitoring**: Pantau kehadiran real-time semua karyawan
-   **Attendance Reports**: Laporan kehadiran dengan export PDF/Excel
-   **Late Tracking**: Monitoring keterlambatan dengan grace period
-   **Location Verification**: Verifikasi lokasi check-in/check-out

#### ✅ Approval Management

-   **Permission Approval**: Approve/reject pengajuan izin dengan email notification
-   **Leave Approval**: Review dan approve/reject cuti dengan workflow
-   **Overtime Approval**: Approve overtime dengan notes dan feedback
-   **Bulk Actions**: Approve/reject multiple requests sekaligus

#### 📋 Advanced Features

-   **QR Code Generator**: Generate QR code harian untuk absensi alternative
-   **PDF Reports**: Export QR code dan laporan kehadiran
-   **Email Notifications**: Automated email untuk approval status
-   **Audit Logs**: Tracking semua aktivitas admin dan approval
-   **Dashboard Analytics**: Statistik kehadiran, izin, dan lembur

---

## 🔄 Application Flow & Architecture

### 📱 Mobile App Flow

#### 1. **Authentication Flow**

```
Start → Login Screen → API Authentication → Token Storage → Dashboard
        ↓
      FCM Token Registration → Push Notification Setup
```

#### 2. **Daily Attendance Flow**

```
Dashboard → Check Location → Validate Radius →
Choice: GPS Attendance OR QR Code Scanning →
Check-in Success → Work Period → Check-out → Attendance Complete
```

#### 3. **Permission Request Flow**

```
Permission Menu → Fill Form → Upload Documents → Submit Request →
Notification to Supervisor → Approval/Rejection → Status Update →
Email Notification to Employee
```

#### 4. **Overtime Flow**

```
Overtime Menu → Start Overtime → Upload Documents → Work Period →
End Overtime → Submit for Approval → Admin Review →
Approval/Rejection → Notification
```

### 🖥️ Admin Dashboard Flow

#### 1. **Employee Onboarding Flow**

```
Create User → Assign Department → Set Position → Configure Shift →
Set Leave Balance → Generate QR Access → Account Activation
```

#### 2. **Daily Operations Flow**

```
Dashboard Overview → Monitor Real-time Attendance →
Review Pending Requests → Process Approvals →
Generate Reports → Send Notifications
```

#### 3. **Approval Workflow**

```
Notification Alert → Review Request Details → Check Documents →
Decision Making → Add Notes/Comments → Approve/Reject →
Email Notification → Status Update
```

---

## 📊 Database Schema & Relationships

### Core Tables & Relationships

#### 1. **users** (Employee Management)

**Fields:**

-   `id` - Primary key
-   `name` - Nama lengkap karyawan
-   `email` - Email unique untuk login
-   `password` - Hashed password
-   `phone` - Nomor telepon
-   `role` - Role user (admin/user/supervisor)
-   `position` - Jabatan/posisi (legacy)
-   `department` - Departemen (legacy)
-   `jabatan_id` - Foreign key ke jabatans table
-   `departemen_id` - Foreign key ke departemens table
-   `shift_kerja_id` - Foreign key ke shift_kerjas table
-   `face_embedding` - Data biometric untuk face recognition
-   `image_url` - URL foto profil karyawan
-   `fcm_token` - Firebase Cloud Messaging token
-   `timestamps` - Created/updated timestamps

**Relationships:**

-   `belongsTo` → jabatans (position)
-   `belongsTo` → departemens (department)
-   `belongsTo` → shift_kerjas (work shift)
-   `hasMany` → attendances
-   `hasMany` → permissions
-   `hasMany` → notes
-   `hasMany` → overtimes
-   `hasMany` → leaves
-   `hasOne` → leave_balances

#### 2. **companies** (Company Settings)

**Fields:**

-   `id` - Primary key
-   `name` - Nama perusahaan
-   `email` - Email perusahaan
-   `address` - Alamat lengkap
-   `latitude` - Koordinat latitude kantor
-   `longitude` - Koordinat longitude kantor
-   `radius_km` - Radius validasi attendance (kilometer)
-   `attendance_type` - Tipe attendance (GPS/QR/BOTH)
-   `timestamps`

#### 3. **attendances** (Daily Attendance)

**Fields:**

-   `id` - Primary key
-   `user_id` - Foreign key ke users
-   `date` - Tanggal attendance
-   `time_in` - Waktu check-in
-   `time_out` - Waktu check-out
-   `latlon_in` - Koordinat saat check-in
-   `latlon_out` - Koordinat saat check-out
-   `timestamps`

**Relationships:**

-   `belongsTo` → users

#### 4. **permissions** (Permission Requests)

**Fields:**

-   `id` - Primary key
-   `user_id` - Foreign key ke users
-   `date_permission` - Tanggal izin
-   `reason` - Alasan izin
-   `image` - Dokumen pendukung
-   `is_approved` - Status approval (0=pending, 1=approved, 2=rejected)
-   `approved_by` - User ID yang approve
-   `approved_at` - Timestamp approval
-   `timestamps`

**Relationships:**

-   `belongsTo` → users
-   `belongsTo` → users (approved_by)

#### 5. **overtimes** (Overtime Management)

**Fields:**

-   `id` - Primary key
-   `user_id` - Foreign key ke users
-   `date` - Tanggal overtime
-   `start_time` - Waktu mulai overtime
-   `end_time` - Waktu selesai overtime
-   `reason` - Alasan overtime
-   `document` - Dokumen pendukung
-   `status` - Status (pending/approved/rejected)
-   `notes` - Catatan dari admin
-   `approved_at` - Timestamp approval
-   `approved_by` - User ID yang approve
-   `timestamps`

**Relationships:**

-   `belongsTo` → users
-   `belongsTo` → users (approved_by)

#### 6. **notes** (Personal Notes)

**Fields:**

-   `id` - Primary key
-   `user_id` - Foreign key ke users
-   `title` - Judul note
-   `note` - Isi note
-   `timestamps`

**Relationships:**

-   `belongsTo` → users

#### 7. **shift_kerjas** (Work Shifts)

**Fields:**

-   `id` - Primary key
-   `name` - Nama shift
-   `start_time` - Jam mulai kerja
-   `end_time` - Jam selesai kerja
-   `description` - Deskripsi shift
-   `is_cross_day` - Apakah shift melewati tengah malam
-   `grace_period_minutes` - Grace period keterlambatan
-   `is_active` - Status aktif shift
-   `timestamps`

**Relationships:**

-   `hasMany` → users
-   `hasMany` → shift_assignments

#### 8. **departemens** (Departments)

**Fields:**

-   `id` - Primary key
-   `name` - Nama departemen
-   `description` - Deskripsi departemen
-   `timestamps`

**Relationships:**

-   `hasMany` → users
-   `hasMany` → jabatans

#### 9. **jabatans** (Positions)

**Fields:**

-   `id` - Primary key
-   `name` - Nama jabatan
-   `description` - Deskripsi jabatan
-   `departemen_id` - Foreign key ke departemens
-   `timestamps`

**Relationships:**

-   `belongsTo` → departemens
-   `hasMany` → users

#### 10. **leaves** (Leave Requests)

**Fields:**

-   `id` - Primary key
-   `user_id` - Foreign key ke users
-   `leave_type_id` - Foreign key ke leave_types
-   `start_date` - Tanggal mulai cuti
-   `end_date` - Tanggal selesai cuti
-   `total_days` - Total hari cuti
-   `reason` - Alasan cuti
-   `status` - Status approval
-   `approved_by` - User ID yang approve
-   `approved_at` - Timestamp approval
-   `timestamps`

**Relationships:**

-   `belongsTo` → users
-   `belongsTo` → leave_types
-   `belongsTo` → users (approved_by)

#### 11. **leave_types** (Leave Categories)

**Fields:**

-   `id` - Primary key
-   `name` - Nama tipe cuti (Annual, Sick, Maternity, dll)
-   `max_days_per_year` - Maksimal hari per tahun
-   `requires_document` - Apakah butuh dokumen
-   `is_paid` - Apakah dibayar
-   `is_active` - Status aktif
-   `timestamps`

**Relationships:**

-   `hasMany` → leaves
-   `hasMany` → leave_balances

#### 12. **leave_balances** (Leave Balance Tracking)

**Fields:**

-   `id` - Primary key
-   `user_id` - Foreign key ke users
-   `leave_type_id` - Foreign key ke leave_types
-   `year` - Tahun periode
-   `allocated_days` - Hari yang dialokasikan
-   `used_days` - Hari yang sudah digunakan
-   `remaining_days` - Sisa hari cuti
-   `timestamps`

**Relationships:**

-   `belongsTo` → users
-   `belongsTo` → leave_types

#### 13. **qr_absens** (QR Code Management)

**Fields:**

-   `id` - Primary key
-   `qr_code` - QR code string
-   `date` - Tanggal berlaku
-   `is_active` - Status aktif
-   `timestamps`

#### 14. **holidays** (Company Holidays)

**Fields:**

-   `id` - Primary key
-   `name` - Nama hari libur
-   `date` - Tanggal libur
-   `is_national` - Apakah hari libur nasional
-   `description` - Deskripsi
-   `timestamps`

---

## 🛠️ API Endpoints Documentation

### 🔐 Authentication Endpoints

| Method | Endpoint                | Description                       | Auth Required |
| ------ | ----------------------- | --------------------------------- | ------------- |
| POST   | `/api/login`            | User login dengan email/password  | ❌            |
| POST   | `/api/logout`           | User logout dan hapus token       | ✅            |
| POST   | `/api/update-profile`   | Update profile user               | ✅            |
| POST   | `/api/update-fcm-token` | Update FCM token untuk notifikasi | ✅            |
| GET    | `/api/user`             | Get current user data             | ✅            |

### 🏢 Company Endpoints

| Method | Endpoint       | Description                        | Auth Required |
| ------ | -------------- | ---------------------------------- | ------------- |
| GET    | `/api/company` | Get company information & settings | ✅            |

### ⏰ Attendance Endpoints

| Method | Endpoint               | Description                        | Auth Required |
| ------ | ---------------------- | ---------------------------------- | ------------- |
| POST   | `/api/checkin`         | Check-in dengan GPS coordinates    | ✅            |
| POST   | `/api/checkout`        | Check-out dengan GPS coordinates   | ✅            |
| GET    | `/api/is-checkin`      | Check status apakah sudah check-in | ✅            |
| GET    | `/api/api-attendances` | Get attendance history             | ✅            |
| POST   | `/api/check-qr`        | Attendance via QR code scanning    | ✅            |

### 📝 Permission Endpoints

| Method | Endpoint                    | Description                    | Auth Required |
| ------ | --------------------------- | ------------------------------ | ------------- |
| GET    | `/api/api-permissions`      | List permission requests       | ✅            |
| POST   | `/api/api-permissions`      | Create new permission request  | ✅            |
| GET    | `/api/api-permissions/{id}` | Get specific permission detail | ✅            |
| PUT    | `/api/api-permissions/{id}` | Update permission request      | ✅            |
| DELETE | `/api/api-permissions/{id}` | Delete permission request      | ✅            |

### ⏱️ Overtime Endpoints

| Method | Endpoint               | Description                   | Auth Required |
| ------ | ---------------------- | ----------------------------- | ------------- |
| POST   | `/api/start-overtime`  | Start overtime session        | ✅            |
| POST   | `/api/end-overtime`    | End overtime session          | ✅            |
| GET    | `/api/overtime-status` | Check today's overtime status | ✅            |
| GET    | `/api/overtimes`       | List overtime history         | ✅            |

### 📋 Notes Endpoints

| Method | Endpoint              | Description              | Auth Required |
| ------ | --------------------- | ------------------------ | ------------- |
| GET    | `/api/api-notes`      | List personal notes      | ✅            |
| POST   | `/api/api-notes`      | Create new note          | ✅            |
| GET    | `/api/api-notes/{id}` | Get specific note detail | ✅            |
| PUT    | `/api/api-notes/{id}` | Update note              | ✅            |
| DELETE | `/api/api-notes/{id}` | Delete note              | ✅            |

### 👤 User Management Endpoints

| Method | Endpoint             | Description         | Auth Required |
| ------ | -------------------- | ------------------- | ------------- |
| GET    | `/api/api-user/{id}` | Get user by ID      | ✅            |
| POST   | `/api/api-user/edit` | Update user profile | ✅            |

---

## 🎯 Business Logic & Validation

### 📍 Location-based Attendance

```php
// Validasi radius attendance
$company = Company::first();
$userLat = $request->latitude;
$userLon = $request->longitude;
$distance = calculateDistance($userLat, $userLon, $company->latitude, $company->longitude);

if ($distance > $company->radius_km) {
    return response(['message' => 'Lokasi terlalu jauh dari kantor'], 400);
}
```

### ⏰ Shift Management Logic

```php
// Validasi waktu check-in berdasarkan shift
$userShift = $user->shiftKerja;
$currentTime = now();
$shiftStart = Carbon::createFromFormat('H:i', $userShift->start_time);
$gracePeriod = $userShift->grace_period_minutes;

if ($currentTime->gt($shiftStart->addMinutes($gracePeriod))) {
    // Mark as late attendance
    $attendance->is_late = true;
}
```

### 📅 Leave Balance Calculation

```php
// Calculate remaining leave balance
$leaveBalance = LeaveBalance::where('user_id', $userId)
    ->where('leave_type_id', $leaveTypeId)
    ->where('year', date('Y'))
    ->first();

$remainingDays = $leaveBalance->allocated_days - $leaveBalance->used_days;

if ($requestedDays > $remainingDays) {
    return response(['message' => 'Sisa cuti tidak mencukupi'], 400);
}
```

### 🔔 Notification System

```php
// Send FCM notification untuk approval
$fcmToken = $user->fcm_token;
$notification = [
    'title' => 'Permission Approved',
    'body' => 'Your permission request has been approved',
    'data' => [
        'type' => 'permission',
        'permission_id' => $permission->id
    ]
];

FCM::sendTo($fcmToken, $notification);
```

---

## 📱 Mobile App Integration

#### Flutter API Base URL

```dart
const String baseUrl = 'http://your-domain.com/api';

// For local development
const String baseUrl = 'http://10.0.2.2:8000/api'; // Android Emulator
const String baseUrl = 'http://127.0.0.1:8000/api'; // iOS Simulator
```

#### Authentication Headers

```dart
// Include Sanctum token in all API requests
final headers = {
  'Accept': 'application/json',
  'Content-Type': 'application/json',
  'Authorization': 'Bearer $token',
};
```

---

## 🧪 Testing

### Run Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/AttendanceTest.php

# Run with coverage
php artisan test --coverage
```

### API Testing dengan Postman

Import koleksi Postman yang tersedia di:

```
postman-collection/FIC16-Absensi.postman_collection.json
```

---

## 📈 Performance & Optimization

### Database Indexing

```sql
-- Key indexes untuk performance
CREATE INDEX idx_attendances_user_date ON attendances(user_id, date);
CREATE INDEX idx_permissions_user_status ON permissions(user_id, is_approved);
CREATE INDEX idx_leaves_user_dates ON leaves(user_id, start_date, end_date);
```

### Caching Strategy

```php
// Cache company settings
$company = Cache::remember('company_settings', 3600, function () {
    return Company::first();
});

// Cache user shift information
$userShift = Cache::remember("user_shift_{$userId}", 1800, function () use ($userId) {
    return User::with('shiftKerja')->find($userId)->shiftKerja;
});
```

### Queue Jobs untuk Background Processing

```php
// Email notifications
dispatch(new SendApprovalNotification($permission));

// Generate monthly reports
dispatch(new GenerateMonthlyReport($month, $year));
```

---

## 🔐 Security Features

### API Security

-   **Sanctum Token Authentication**: Secure token-based API access
-   **Rate Limiting**: API throttling untuk prevent abuse
-   **CSRF Protection**: Cross-site request forgery protection
-   **Input Validation**: Comprehensive request validation
-   **SQL Injection Prevention**: Eloquent ORM protection

### Data Protection

-   **Password Hashing**: Bcrypt dengan custom rounds
-   **File Upload Validation**: Strict file type & size validation
-   **Location Data Encryption**: GPS coordinates protection
-   **Audit Logging**: Track all critical operations

### Access Control

-   **Role-based Access**: Admin, Supervisor, Employee roles
-   **Permission Gates**: Granular permission control
-   **Two-Factor Authentication**: Optional 2FA dengan Fortify
-   **Session Management**: Secure session handling

---

## 📝 Contributing

### Code Standards

```bash
# Run code formatting
vendor/bin/pint

# Check code quality
vendor/bin/pint --test
```

### Git Workflow

1. Fork repository
2. Create feature branch: `git checkout -b feature/nama-fitur`
3. Commit changes: `git commit -m 'Add new feature'`
4. Push to branch: `git push origin feature/nama-fitur`
5. Submit Pull Request

### Documentation

-   Update README untuk fitur baru
-   Tambahkan PHPDoc untuk methods
-   Update API documentation
-   Include test cases

---

## 📞 Support & Contact

**Development Team:**

-   Email: support@jagohris.com
-   Documentation: [Link to docs]
-   Issue Tracker: [GitHub Issues URL]

**License:**
This project is licensed under the MIT License.

---

## 🎉 Acknowledgments

-   Laravel Framework Team
-   Filament Admin Panel
-   Laravel Sanctum & Fortify
-   Firebase Cloud Messaging
-   Brevo/Resend Email Services
-   `timestamps`

**Relationships:**

-   `belongsToMany` → users (pivot: jabatan_user)

#### 9. **departemens** (Departments)

Master data departemen.

**Fields:**

-   `id` - Primary key
-   `name` - Nama departemen
-   `description` - Deskripsi (nullable)
-   `timestamps`

**Relationships:**

-   `belongsToMany` → users (pivot: departemen_user)

#### 10. **shift_kerjas** (Work Shifts)

Master data shift kerja.

**Fields:**

-   `id` - Primary key
-   `name` - Nama shift (ex: Pagi, Siang, Malam)
-   `start_time` - Jam mulai shift (H:i)
-   `end_time` - Jam selesai shift (H:i)
-   `description` - Deskripsi (nullable)
-   `timestamps`

**Relationships:**

-   `belongsToMany` → users (pivot: shift_kerja_user)

---

## 🔄 Application Flow

### 1. Authentication Flow

````
┌─────────────┐
│ Flutter App │
└──────┬──────┘
       │ POST /api/login
       │ {email, password}
       ▼
┌──────────────────┐
│ Laravel Backend  │

- Date and time format validation

### 3. GPS Security
- Server-side radius checking (Haversine formula)
- Cannot spoof location on backend
- Stores actual GPS coordinates for audit

### 4. File Security
- Files stored in `storage/app/public/`
- Symlink to `public/storage/`
- Validate MIME types
- Max file size: 2MB

### 5. API Rate Limiting
- Built-in Laravel rate limiting
- Configure in `bootstrap/app.php`
- Default: 60 requests per minute

---

## 🧪 Testing

### Run Tests
```bash
# All tests
php artisan test

# Specific test file
php artisan test tests/Feature/AuthTest.php

# With coverage
php artisan test --coverage
````

### Example Test

```php
public function test_user_can_checkin()
{
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/checkin', [
            'latitude' => '-6.200000',
            'longitude' => '106.816666',
        ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Checkin success']);

    $this->assertDatabaseHas('attendances', [
        'user_id' => $user->id,
        'date' => now()->toDateString(),
    ]);
}
```

---

## 🐛 Troubleshooting

### Common Issues

#### 1. Migration Errors

```bash
# Reset database
php artisan migrate:fresh --seed

# Check database connection
php artisan db:show
```

#### 2. Storage Permission

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### 3. Queue Not Processing

```bash
# Clear failed jobs
php artisan queue:clear

# Restart worker
php artisan queue:restart && php artisan queue:work
```

#### 4. Sanctum Token Issues

```bash
# Clear cache
php artisan config:clear
php artisan cache:clear

# Verify sanctum middleware in api routes
```

#### 5. CORS Errors (Flutter)

Update `config/cors.php`:

```php
'paths' => ['api/*'],
'allowed_origins' => ['*'],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
```

#### 6. Image Upload Errors

-   Check `post_max_size` and `upload_max_filesize` in `php.ini`
-   Verify storage link: `php artisan storage:link`
-   Check folder permissions

---

## 📈 Performance Optimization

### 1. Database Optimization

```sql
-- Add indexes for frequent queries
CREATE INDEX idx_attendance_user_date ON attendances(user_id, date);
CREATE INDEX idx_permissions_user_status ON permissions(user_id, is_approved);
CREATE INDEX idx_overtimes_user_date ON overtimes(user_id, date);
```

### 2. Query Optimization

```php
// Use eager loading to prevent N+1
$attendances = Attendance::with('user')->get();

// Use select to reduce data
$users = User::select('id', 'name', 'email')->get();
```

### 3. Caching

```php
// Cache company settings (rarely changes)
$company = Cache::remember('company_settings', 3600, function () {
    return Company::first();
});
```

### 4. Queue Jobs

```php
// Send emails via queue
Mail::to($user)->queue(new PermissionApprovedMail($permission));
```

---

## 📚 Additional Resources

### API Testing Tools

-   **Postman**: Import collection untuk test semua endpoints
-   **Thunder Client**: VS Code extension
-   **HTTPie**: CLI tool

### Recommended Flutter Packages

```yaml
dependencies:
    # State Management
    provider: ^6.1.0
    # or
    flutter_bloc: ^8.1.3

    # Network
    dio: ^5.4.0 # Alternative to http

    # Storage
    flutter_secure_storage: ^9.0.0
    shared_preferences: ^2.2.2

    # Location
    geolocator: ^10.1.0
    geocoding: ^2.1.1

    # QR Code
    qr_code_scanner: ^1.0.1
    qr_flutter: ^4.1.0

    # Image
    image_picker: ^1.0.4
    cached_network_image: ^3.3.1

    # Firebase
    firebase_core: ^2.24.0
    firebase_messaging: ^14.7.6

    # UI
    intl: ^0.19.0 # Date formatting
    flutter_spinkit: ^5.2.0 # Loading indicators
```

---

## 🔄 Git Workflow

```bash
# Current branch
git branch  # dev

# Pull latest changes
git pull origin dev

# Create feature branch
git checkout -b feature/new-feature

# After changes
git add .
git commit -m "feat: add new feature"
git push origin feature/new-feature

# Create PR to dev branch
```

---

## 📞 Support & Contact

Untuk pertanyaan teknis, bug reports, atau feature requests:

-   **Repository Issues**: Create issue di GitHub
-   **Documentation**: Baca file ini dan code comments
-   **API Testing**: Gunakan Postman collection (jika tersedia)

---

## 📄 License

This project is licensed under the MIT License.

---

## 📝 Changelog

### Version 2.0 (Current)

-   ✅ Laravel 12 upgrade
-   ✅ Filament v4 admin panel
-   ✅ Overtime management
-   ✅ Department & Position management
-   ✅ Shift management
-   ✅ Enhanced permission system with document support
-   ✅ FCM push notifications
-   ✅ QR Code attendance

### Version 1.0

-   ✅ Basic attendance system (GPS-based)
-   ✅ Permission requests
-   ✅ User management
-   ✅ Company settings

---

**Last Updated**: October 2025
**Laravel Version**: 12.x
**PHP Version**: 8.3.22
**Maintained By**: Development Team
