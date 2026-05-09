# Filament Dashboard untuk Laravel Absensi

## Overview
Dashboard Filament 4 telah berhasil diinstall dan dikonfigurasi untuk sistem Laravel Absensi. Dashboard ini menyediakan interface admin yang lengkap untuk mengelola semua fitur aplikasi absensi.

## Akses Dashboard
- **URL**: `/admin`
- **Admin User**:
  - Email: `admin@admin.com`
  - Password: `password`

## Fitur Dashboard

### 1. User Management
- **Resource**: UserResource
- **Features**:
  - CRUD operations untuk users
  - Role-based access (admin, manager, employee)
  - Profile image upload
  - Filter berdasarkan role dan department
- **Navigation**: User Management > Users

### 2. Attendance Management
- **Resource**: AttendanceResource
- **Features**:
  - View attendance records dengan work duration calculation
  - Date range filtering
  - Employee filtering
  - Location tracking (lat/lon)
- **Navigation**: Attendance Management > Attendances

### 3. Permission Management
- **Resource**: PermissionResource
- **Features**:
  - Kelola izin/cuti karyawan
  - Approval system
  - Image attachments
- **Navigation**: Attendance Management > Permissions

### 4. QR Code Management
- **Resource**: QrAbsenResource
- **Features**:
  - Generate dan kelola QR codes untuk absensi
  - Check-in dan check-out QR codes
- **Navigation**: Attendance Management > Qr Absens

### 5. Company Settings
- **Resource**: CompanyResource
- **Features**:
  - Konfigurasi company profile
  - Set working hours
  - Location-based attendance settings
- **Navigation**: Company Settings > Companies

### 6. Notes
- **Resource**: NoteResource
- **Features**:
  - Internal notes system
  - User-based notes
- **Navigation**: General > Notes

## Database Seeders

Berikut adalah data yang akan di-seed ke database:

### Users
- **Admin**: admin@admin.com / password
- **Manager**: manager@company.com / password
- **Employees**: john@company.com, jane@company.com, bob@company.com / password

### Company
- PT. ABC Technology dengan setting location-based attendance

### Sample Data
- Notes: 3 welcome notes
- QR Codes: 2 daily QR codes

## Commands untuk Setup

```bash
# Install Filament 4
composer require filament/filament

# Install Filament panels
php artisan filament:install --panels

# Migrate dan seed database
php artisan migrate:fresh --seed

# Format code
vendor/bin/pint --dirty
```

## File Structure

```
app/Filament/Resources/
├── Users/
│   ├── UserResource.php
│   ├── Schemas/UserForm.php
│   └── Tables/UsersTable.php
├── Attendances/
│   ├── AttendanceResource.php
│   ├── Schemas/AttendanceForm.php
│   └── Tables/AttendancesTable.php
├── Companies/
├── Permissions/
├── Notes/
└── QrAbsens/
```

## Kustomisasi Yang Telah Dilakukan

1. **Navigation Groups**: Resources dikelompokkan berdasarkan fungsi
2. **Custom Icons**: Menggunakan Heroicons yang sesuai
3. **Enhanced Tables**: Tambah filters, search, dan custom columns
4. **Improved Forms**: Validation, file uploads, dan better UX
5. **Work Duration Calculation**: Otomatis hitung durasi kerja
6. **Badge Styling**: Role-based colors dan status indicators

## Next Steps

1. **Security**: Implementasikan proper authorization policies
2. **Widgets**: Tambah dashboard widgets untuk statistics
3. **Export**: Implementasikan export functionality
4. **Notifications**: Setup real-time notifications
5. **Reports**: Buat reports dan analytics features