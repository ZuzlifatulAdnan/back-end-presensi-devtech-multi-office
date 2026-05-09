# Issue: Button End Overtime Tidak Aktif Saat Status In Progress

## 🐛 Problem Description

Ketika status overtime adalah `in_progress`, button **Check Out** di aplikasi mobile tidak aktif (disabled) meskipun kondisi `canCheckOut = true`.

## 🔍 Root Cause Analysis

### Frontend Implementation (Flutter)

Di file `lib/presentation/overtimes/pages/overtime_page.dart` baris 321:

```dart
onPressed: canCheckOut && overtimeData != null
    ? () async {
        await _checkBackendAndNavigate(() async {
          final result = await context.push(
            CheckInCheckOutOvertimePage(
              isCheckIn: false,
              overtimeId: overtimeData.id, // ❌ Membutuhkan ID
            ),
          );
          if (result == true && context.mounted) {
            _refreshData();
          }
        });
      }
    : null,
```

**Kondisi button aktif:**
- ✅ `canCheckOut` = true (ketika status `in_progress`)
- ❌ `overtimeData` != null (saat ini bernilai **null**)

### Current Backend Response

**Endpoint:** `GET /api/overtime-status`

**Response saat status `in_progress` (dari dokumentasi `instructions/overtime.md`):**

```json
{
  "status": "in_progress",
  "message": "Lembur sedang berlangsung"
}
```

**Problem:** Response **tidak mengandung field `data`** yang berisi informasi overtime yang sedang berjalan, termasuk **`id`** yang dibutuhkan untuk endpoint `POST /api/end-overtime`.

### Frontend Model Expectation

Model di Flutter (`lib/data/models/response/overtime_response_model.dart`):

```dart
class OvertimeStatusResponseModel {
  final String? status;
  final String? message;
  final Overtime? data;  // ❌ Ini yang NULL
  // ...
}

class Overtime {
  final int? id;  // ❌ ID dibutuhkan untuk end-overtime
  final int? userId;
  final String? date;
  final String? startTime;
  final String? endTime;
  // ...
}
```

## ✅ Required Backend Changes

### Update Response untuk `/api/overtime-status`

Ketika status adalah `in_progress`, backend harus mengembalikan field `data` yang berisi informasi overtime yang sedang berjalan.

## ✅ Backend Fix (Laravel)

- Endpoint `GET /api/overtime-status` kini selalu mengembalikan properti `data`.
- Saat belum ada lembur berjalan: `data` bernilai `null`.
- Saat lembur berjalan atau telah selesai: `data` berisi object lembur lengkap (termasuk `id`) sehingga mobile dapat melakukan checkout.
- Implementasi berada di `app/Http/Controllers/Api/OvertimeController.php` pada method `checkTodayOvertimeStatus()`.

Setelah melakukan `php artisan migrate` (untuk memastikan skema overtime terbaru) dan melakukan deploy/push, jalankan smoke test:

```bash
# 1. Mulai lembur
POST /api/start-overtime
Authorization: Bearer {token}

# 2. Cek status lembur
GET /api/overtime-status
Authorization: Bearer {token}

# Pastikan respons memuat kunci data.id
```

### Expected Response (Updated)

**Status: `not_started`**
```json
{
  "status": "not_started",
  "message": "Belum ada lembur untuk hari ini",
  "data": null
}
```

**Status: `in_progress`** ⭐ **SUDAH Ditingkatkan**
```json
{
  "status": "in_progress",
  "message": "Lembur sedang berlangsung",
  "data": {
    "id": 42,
    "user_id": 7,
    "date": "2025-01-16",
    "start_time": "18:05",
    "end_time": null,
    "reason": "Deployment urgent",
    "document": "overtime_documents/1737024300_bukti.png",
    "status": "pending",
    "notes": "Lembur untuk deployment",
    "approved_at": null,
    "approved_by": null,
    "created_at": "2025-01-16T11:05:00.000000Z",
    "updated_at": "2025-01-16T11:05:00.000000Z"
  }
}
```

**Status: `completed`**
```json
{
  "status": "completed",
  "message": "Lembur hari ini telah diakhiri dan menunggu persetujuan",
  "data": {
    "id": 42,
    "user_id": 7,
    "date": "2025-01-16",
    "start_time": "18:05",
    "end_time": "22:15",
    "reason": "Selesai deployment",
    "document": "overtime_documents/1737024300_bukti.png",
    "status": "pending",
    "notes": "Lembur untuk deployment",
    "approved_at": null,
    "approved_by": null,
    "created_at": "2025-01-16T11:05:00.000000Z",
    "updated_at": "2025-01-16T15:15:00.000000Z"
  }
}
```

## 💡 Backend Implementation Suggestion

### Laravel Controller Example

```php
public function getOvertimeStatus(Request $request)
{
    $user = $request->user();
    $today = now()->format('Y-m-d');

    // Cari overtime untuk hari ini
    $overtime = Overtime::where('user_id', $user->id)
        ->where('date', $today)
        ->first();

    if (!$overtime) {
        return response()->json([
            'status' => 'not_started',
            'message' => 'Belum ada lembur untuk hari ini',
            'data' => null,
        ], 200);
    }

    // Check if overtime is still in progress (end_time is null)
    if ($overtime->end_time === null) {
        return response()->json([
            'status' => 'in_progress',
            'message' => 'Lembur sedang berlangsung',
            'data' => $overtime,  // ✅ Tambahkan data overtime
        ], 200);
    }

    // Overtime completed
    return response()->json([
        'status' => 'completed',
        'message' => 'Lembur hari ini telah diakhiri dan menunggu persetujuan',
        'data' => $overtime,
    ], 200);
}
```

## 🔄 Flow After Fix

1. User mulai overtime → Status jadi `in_progress`
2. User buka halaman Overtime → Call `GET /api/overtime-status`
3. Backend return response dengan `data` yang berisi `id` overtime
4. Frontend extract `overtimeData.id` = 42
5. Button "Check Out" jadi aktif ✅
6. User klik "Check Out" → Navigate ke form dengan `overtimeId: 42`
7. User submit → Call `POST /api/end-overtime` dengan `id: 42`
8. Backend proses end overtime ✅

## 📋 Checklist untuk Backend Developer

- [ ] Update endpoint `GET /api/overtime-status`
- [ ] Tambahkan field `data` di response ketika status `in_progress`
- [ ] Tambahkan field `data` di response ketika status `completed`
- [ ] Field `data` berisi object overtime lengkap dengan `id`
- [ ] Test dengan Postman/Thunder Client
- [ ] Update dokumentasi di `instructions/overtime.md`
- [ ] Inform frontend developer setelah selesai

## 🧪 Testing Checklist

### Scenario 1: Not Started
```bash
# User belum mulai overtime hari ini
GET /api/overtime-status
Authorization: Bearer {token}

# Expected Response:
{
  "status": "not_started",
  "message": "Belum ada lembur untuk hari ini",
  "data": null
}
```

### Scenario 2: In Progress ⭐ **SUDAH DIPERBAIKI**
```bash
# User sudah mulai overtime (end_time masih null)
GET /api/overtime-status
Authorization: Bearer {token}

# Expected Response:
{
  "status": "in_progress",
  "message": "Lembur sedang berlangsung",
  "data": {
    "id": 42,  // ✅ ID ini yang dibutuhkan
    "user_id": 7,
    "date": "2025-01-16",
    "start_time": "18:05",
    "end_time": null,  // ✅ Masih null karena belum end
    "reason": "Deployment urgent",
    "status": "pending",
    "notes": "...",
    // ... fields lainnya
  }
}
```

### Scenario 3: Completed
```bash
# User sudah end overtime (end_time sudah terisi)
GET /api/overtime-status
Authorization: Bearer {token}

# Expected Response:
{
  "status": "completed",
  "message": "Lembur hari ini telah diakhiri dan menunggu persetujuan",
  "data": {
    "id": 42,
    "user_id": 7,
    "date": "2025-01-16",
    "start_time": "18:05",
    "end_time": "22:15",  // ✅ Sudah terisi
    "reason": "Selesai deployment",
    "status": "pending",
    // ...
  }
}
```

## 📞 Contact

Jika ada pertanyaan mengenai issue ini, silakan hubungi:
- **Frontend Developer:** [Nama Frontend Developer]
- **Issue Date:** 2025-01-16
- **Priority:** High (Blocking user untuk end overtime)

---

**Status:** 🟢 **Resolved**

**Impact:** User sudah dapat melakukan end overtime pada status `in_progress`.

**Catatan Deploy:** Pastikan backend sudah ditarik ke versi yang memuat perbaikan ini.
