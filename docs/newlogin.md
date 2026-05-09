# API Login Documentation (Updated)

## Overview
API Login telah diperbarui untuk mengambil data `position` dan `department` dari tabel `jabatans` dan `departemens` melalui relasi `jabatan_id` dan `departemen_id` di tabel `users`.

## Endpoint

### POST /api/login

#### Request
```http
POST /api/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123"
}
```

#### Success Response (200 OK)

```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "phone": "081234567890",
    "role": "employee",
    "image_url": "profile.jpg",
    "face_embedding": "...",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  },
  "token": "1|abc123def456...",
  "role": "employee",
  "position": {
    "id": 1,
    "name": "Software Engineer"
  },
  "default_shift": {
    "id": 1,
    "name": "Morning Shift"
  },
  "default_shift_detail": {
    "id": 1,
    "name": "Morning Shift",
    "start_time": "08:00:00",
    "end_time": "16:00:00"
  },
  "department": {
    "id": 1,
    "name": "IT Department"
  }
}
```

#### Error Response (401 Unauthorized)

```json
{
  "message": "Invalid credentials"
}
```

## Changes Summary

### Before
- `position`: String value dari kolom `position` di tabel `users`
- Data position tidak terstruktur dan tidak ada ID

### After
- `position`: Object dengan `id` dan `name` dari tabel `jabatans`
- Menggunakan relasi `jabatan()` dengan foreign key `jabatan_id`
- Struktur data lebih konsisten dengan `department` dan `default_shift`

## Database Relations

### Users Table
- `jabatan_id` → Foreign key ke tabel `jabatans`
- `departemen_id` → Foreign key ke tabel `departemens`
- `shift_kerja_id` → Foreign key ke tabel `shift_kerjas`

### Response Structure
Semua relasi dikembalikan dengan format yang sama:
```json
{
  "id": integer,
  "name": string
}
```

## Implementation Details

### Controller: `app/Http/Controllers/Api/AuthController.php`

#### Method: `login()`
```php
// Load relationships
$user->load(['shiftKerja', 'departemen', 'jabatan']);

$response = [
    'user' => new UserResource($user),
    'token' => $token,
    'role' => $user->role,
    'position' => $user->jabatan ? [
        'id' => $user->jabatan->id,
        'name' => $user->jabatan->name,
    ] : null,
    // ... other fields
];
```

### Model: `app/Models/User.php`

#### Relation: `jabatan()`
```php
public function jabatan()
{
    return $this->belongsTo(\App\Models\Jabatan::class, 'jabatan_id');
}
```

## Testing

### Example cURL Request
```bash
curl -X POST http://your-domain.com/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123"
  }'
```

## Notes

1. **Null Values**: Jika user tidak memiliki `jabatan_id`, field `position` akan bernilai `null`
2. **Consistency**: Format response sekarang konsisten dengan `department` dan `default_shift`
3. **Backward Compatibility**: Perubahan ini BREAKING CHANGE karena mengubah struktur dari string menjadi object
4. **Me Endpoint**: Endpoint `/api/me` juga telah diperbarui dengan struktur yang sama

## Migration Required

Mobile app perlu update untuk menghandle struktur baru:

### Before
```dart
String position = response['position']; // "Software Engineer"
```

### After
```dart
Map<String, dynamic>? position = response['position'];
String positionName = position?['name'] ?? '-'; // "Software Engineer"
int? positionId = position?['id']; // 1
```
