# JagoHRIS API Documentation

Comprehensive API documentation for Flutter integration with JagoHRIS Laravel backend.

## 📋 Table of Contents

1. [Base Configuration](#base-configuration)
2. [Authentication](#authentication)
3. [Company & Settings](#company--settings)
4. [Attendance Management](#attendance-management)
5. [Leave Management](#leave-management)
6. [Overtime Management](#overtime-management)
7. [Notes Management](#notes-management)
8. [User Profile](#user-profile)
9. [Error Handling](#error-handling)
10. [Flutter Integration Examples](#flutter-integration-examples)

---

## Base Configuration

### Base URL

```
Production: https://hris.jagoflutter.com/api
Local: http://127.0.0.1:8000/api
```

### Headers

All authenticated requests require:

```http
Content-Type: application/json
Accept: application/json
Authorization: Bearer {your_token}
```

### Response Format

All responses follow this format:

```json
{
    "message": "Success message",
    "data": {}, // or []
    "status": "success" // or "error"
}
```

---

## Authentication

### 1. Login

**Endpoint:** `POST /login`  
**Auth Required:** ❌

**Request:**

```json
{
    "email": "admin@admin.com",
    "password": "password"
}
```

**Response Success (200):**

```json
{
    "user": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@admin.com",
        "phone": "081234567890",
        "role": "admin",
        "position": "Administrator",
        "department": "IT",
        "jabatan_id": 1,
        "departemen_id": 1,
        "shift_kerja_id": 1,
        "face_embedding": null,
        "image_url": "storage/users/profile.jpg",
        "fcm_token": null,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    },
    "token": "1|abcd1234efgh5678ijkl9012mnop3456qrst7890"
}
```

**Response Error (401):**

```json
{
    "message": "Invalid credentials"
}
```

**Flutter Implementation:**

```dart
Future<Map<String, dynamic>> login(String email, String password) async {
  final response = await http.post(
    Uri.parse('$baseUrl/login'),
    headers: {'Content-Type': 'application/json'},
    body: jsonEncode({
      'email': email,
      'password': password,
    }),
  );

  if (response.statusCode == 200) {
    final data = jsonDecode(response.body);
    // Save token to secure storage
    await storage.write(key: 'auth_token', value: data['token']);
    return data;
  } else {
    throw Exception('Login failed');
  }
}
```

### 2. Logout

**Endpoint:** `POST /logout`  
**Auth Required:** ✅

**Response (200):**

```json
{
    "message": "Logged out successfully"
}
```

### 3. Update FCM Token

**Endpoint:** `POST /update-fcm-token`  
**Auth Required:** ✅

**Request:**

```json
{
    "fcm_token": "firebase_fcm_token_from_device"
}
```

**Response (200):**

```json
{
    "message": "FCM token updated successfully"
}
```

### 4. Get Current User

**Endpoint:** `GET /user`  
**Auth Required:** ✅

**Response (200):**

```json
{
    "id": 1,
    "name": "Admin User",
    "email": "admin@admin.com",
    "phone": "081234567890",
    "role": "admin",
    "position": "Administrator",
    "department": "IT",
    "image_url": "storage/users/profile.jpg",
    "fcm_token": "firebase_token_here"
}
```

---

## Company & Settings

### Get Company Information

**Endpoint:** `GET /company`  
**Auth Required:** ✅

**Response (200):**

```json
{
    "company": {
        "id": 1,
        "name": "PT Technology Indonesia",
        "email": "info@company.com",
        "address": "Jl. Sudirman No. 123, Jakarta Pusat",
        "latitude": "-6.208763",
        "longitude": "106.845599",
        "radius_km": "0.5",
        "attendance_type": "both",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

**Usage in Flutter:**

-   `latitude`, `longitude`: Office coordinates for GPS validation
-   `radius_km`: Maximum distance for valid attendance (in kilometers)
-   `attendance_type`: "gps", "qr", or "both"

---

## Attendance Management

### 1. Check-in

**Endpoint:** `POST /checkin`  
**Auth Required:** ✅

**Request:**

```json
{
    "latitude": "-6.208763",
    "longitude": "106.845599"
}
```

**Response Success (200):**

```json
{
    "message": "Checkin success",
    "attendance": {
        "id": 123,
        "user_id": 1,
        "date": "2024-10-02",
        "time_in": "08:30:15",
        "time_out": null,
        "latlon_in": "-6.208763,106.845599",
        "latlon_out": null,
        "created_at": "2024-10-02T08:30:15.000000Z",
        "updated_at": "2024-10-02T08:30:15.000000Z"
    }
}
```

**Response Error (400):**

```json
{
    "message": "You are outside the allowed radius"
}
```

### 2. Check-out

**Endpoint:** `POST /checkout`  
**Auth Required:** ✅

**Request:**

```json
{
    "latitude": "-6.208763",
    "longitude": "106.845599"
}
```

**Response Success (200):**

```json
{
    "message": "Checkout success",
    "attendance": {
        "id": 123,
        "user_id": 1,
        "date": "2024-10-02",
        "time_in": "08:30:15",
        "time_out": "17:15:30",
        "latlon_in": "-6.208763,106.845599",
        "latlon_out": "-6.208800,106.845600",
        "created_at": "2024-10-02T08:30:15.000000Z",
        "updated_at": "2024-10-02T17:15:30.000000Z"
    }
}
```

**Response Error (400):**

```json
{
    "message": "You must check-in first"
}
```

### 3. Check Status

**Endpoint:** `GET /is-checkin`  
**Auth Required:** ✅

**Response (200):**

```json
{
    "checkedin": true,
    "checkedout": false
}
```

**Status Combinations:**

-   `checkedin: false, checkedout: false` - Belum check-in
-   `checkedin: true, checkedout: false` - Sudah check-in, belum check-out
-   `checkedin: true, checkedout: true` - Sudah check-in & check-out

### 4. Get Attendance History

**Endpoint:** `GET /api-attendances`  
**Auth Required:** ✅  
**Query Parameters:** `?date=2024-10-02` (optional)

**Response (200):**

```json
{
    "message": "Success",
    "data": [
        {
            "id": 123,
            "user_id": 1,
            "date": "2024-10-02",
            "time_in": "08:30:15",
            "time_out": "17:15:30",
            "latlon_in": "-6.208763,106.845599",
            "latlon_out": "-6.208800,106.845600",
            "created_at": "2024-10-02T08:30:15.000000Z",
            "updated_at": "2024-10-02T17:15:30.000000Z"
        }
    ]
}
```

### 5. QR Code Check-in

**Endpoint:** `POST /check-qr`  
**Auth Required:** ✅

**Request:**

```json
{
    "qr_code": "QR_20241002_ABCD1234"
}
```

**Response Success (200):**

```json
{
    "message": "Attendance recorded via QR code",
    "attendance": {
        "id": 124,
        "user_id": 1,
        "date": "2024-10-02",
        "time_in": "08:30:15",
        "time_out": null,
        "latlon_in": null,
        "latlon_out": null,
        "created_at": "2024-10-02T08:30:15.000000Z",
        "updated_at": "2024-10-02T08:30:15.000000Z"
    }
}
```

**Response Error (404):**

```json
{
    "message": "QR code not found or expired"
}
```

---

## Leave Management

### 1. Get Leave Types

**Endpoint:** `GET /leave-types`  
**Auth Required:** ✅

**Response (200):**

```json
{
    "message": "Leave types retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "Annual Leave",
            "quota_days": 12,
            "is_paid": true,
            "created_at": "2024-01-01T00:00:00.000000Z",
            "updated_at": "2024-01-01T00:00:00.000000Z"
        },
        {
            "id": 2,
            "name": "Sick Leave",
            "quota_days": 12,
            "is_paid": true,
            "created_at": "2024-01-01T00:00:00.000000Z",
            "updated_at": "2024-01-01T00:00:00.000000Z"
        },
        {
            "id": 3,
            "name": "Personal Leave",
            "quota_days": 5,
            "is_paid": false,
            "created_at": "2024-01-01T00:00:00.000000Z",
            "updated_at": "2024-01-01T00:00:00.000000Z"
        }
    ]
}
```

### 2. Get Leave Balance

**Endpoint:** `GET /leave-balance`  
**Auth Required:** ✅  
**Query Parameters:** `?year=2024` (optional, defaults to current year)

**Response (200):**

```json
{
    "message": "Leave balance retrieved successfully",
    "data": [
        {
            "id": 1,
            "employee_id": 1,
            "leave_type_id": 1,
            "year": 2024,
            "quota_days": 12,
            "used_days": 3,
            "remaining_days": 9,
            "last_updated": "2024-10-02T10:00:00.000000Z",
            "leave_type": {
                "id": 1,
                "name": "Annual Leave",
                "quota_days": 12,
                "is_paid": true
            }
        },
        {
            "id": 2,
            "employee_id": 1,
            "leave_type_id": 2,
            "year": 2024,
            "quota_days": 12,
            "used_days": 1,
            "remaining_days": 11,
            "last_updated": "2024-09-15T14:30:00.000000Z",
            "leave_type": {
                "id": 2,
                "name": "Sick Leave",
                "quota_days": 12,
                "is_paid": true
            }
        }
    ]
}
```

### 3. Get All Leaves

**Endpoint:** `GET /leaves`  
**Auth Required:** ✅  
**Query Parameters:** `?status=pending` (optional - pending, approved, rejected, cancelled)

**Response (200):**

```json
{
    "message": "Leaves retrieved successfully",
    "data": [
        {
            "id": 1,
            "employee_id": 1,
            "leave_type_id": 1,
            "start_date": "2024-10-15",
            "end_date": "2024-10-17",
            "total_days": 3,
            "reason": "Family vacation",
            "attachment_url": "leaves/vacation_plan.pdf",
            "status": "approved",
            "approved_by": 2,
            "approved_at": "2024-10-05T09:00:00.000000Z",
            "notes": "Approved for family time",
            "created_at": "2024-10-01T10:00:00.000000Z",
            "updated_at": "2024-10-05T09:00:00.000000Z",
            "leave_type": {
                "id": 1,
                "name": "Annual Leave",
                "quota_days": 12,
                "is_paid": true
            },
            "approver": {
                "id": 2,
                "name": "Manager User",
                "email": "manager@company.com"
            }
        }
    ]
}
```

**Status Values:**

-   `pending` - Menunggu persetujuan
-   `approved` - Disetujui
-   `rejected` - Ditolak
-   `cancelled` - Dibatalkan

### 4. Get Leave Details

**Endpoint:** `GET /leaves/{id}`  
**Auth Required:** ✅

**Response (200):**

```json
{
    "message": "Leave retrieved successfully",
    "data": {
        "id": 1,
        "employee_id": 1,
        "leave_type_id": 1,
        "start_date": "2024-10-15",
        "end_date": "2024-10-17",
        "total_days": 3,
        "reason": "Family vacation",
        "attachment_url": "leaves/vacation_plan.pdf",
        "status": "approved",
        "approved_by": 2,
        "approved_at": "2024-10-05T09:00:00.000000Z",
        "notes": "Approved for family time",
        "created_at": "2024-10-01T10:00:00.000000Z",
        "updated_at": "2024-10-05T09:00:00.000000Z",
        "employee": {
            "id": 1,
            "name": "John Doe",
            "email": "john@company.com"
        },
        "leave_type": {
            "id": 1,
            "name": "Annual Leave",
            "quota_days": 12,
            "is_paid": true
        },
        "approver": {
            "id": 2,
            "name": "Manager User",
            "email": "manager@company.com"
        }
    }
}
```

### 5. Create Leave Request

**Endpoint:** `POST /leaves`  
**Auth Required:** ✅

**Request:**

```json
{
    "leave_type_id": 1,
    "start_date": "2024-10-15",
    "end_date": "2024-10-17",
    "reason": "Family vacation",
    "attachment_url": "leaves/vacation_plan.pdf"
}
```

**Response Success (201):**

```json
{
    "message": "Leave request created successfully",
    "data": {
        "id": 1,
        "employee_id": 1,
        "leave_type_id": 1,
        "start_date": "2024-10-15",
        "end_date": "2024-10-17",
        "total_days": 3,
        "reason": "Family vacation",
        "attachment_url": "leaves/vacation_plan.pdf",
        "status": "pending",
        "approved_by": null,
        "approved_at": null,
        "notes": null,
        "created_at": "2024-10-01T10:00:00.000000Z",
        "updated_at": "2024-10-01T10:00:00.000000Z",
        "employee": {
            "id": 1,
            "name": "John Doe",
            "email": "john@company.com"
        },
        "leave_type": {
            "id": 1,
            "name": "Annual Leave",
            "quota_days": 12,
            "is_paid": true
        }
    }
}
```

**Response Error (400) - Insufficient Balance:**

```json
{
    "message": "Insufficient leave balance",
    "remaining_days": 2,
    "requested_days": 3
}
```

**Flutter Implementation:**

```dart
Future<void> createLeaveRequest({
  required int leaveTypeId,
  required String startDate,
  required String endDate,
  String? reason,
  String? attachmentUrl,
}) async {
  final token = await storage.read(key: 'auth_token');
  final response = await http.post(
    Uri.parse('$baseUrl/leaves'),
    headers: {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer $token',
    },
    body: jsonEncode({
      'leave_type_id': leaveTypeId,
      'start_date': startDate,
      'end_date': endDate,
      'reason': reason,
      'attachment_url': attachmentUrl,
    }),
  );

  if (response.statusCode != 201) {
    final error = jsonDecode(response.body);
    throw Exception(error['message']);
  }
}
```

### 6. Update Leave Request

**Endpoint:** `PUT /leaves/{id}`  
**Auth Required:** ✅  
**Note:** Only pending leaves can be updated

**Request:**

```json
{
    "leave_type_id": 1,
    "start_date": "2024-10-16",
    "end_date": "2024-10-18",
    "reason": "Updated family vacation dates",
    "attachment_url": "leaves/updated_vacation_plan.pdf"
}
```

**Response Success (200):**

```json
{
    "message": "Leave request updated successfully",
    "data": {
        "id": 1,
        "employee_id": 1,
        "leave_type_id": 1,
        "start_date": "2024-10-16",
        "end_date": "2024-10-18",
        "total_days": 3,
        "reason": "Updated family vacation dates",
        "attachment_url": "leaves/updated_vacation_plan.pdf",
        "status": "pending",
        "employee": {
            "id": 1,
            "name": "John Doe",
            "email": "john@company.com"
        },
        "leave_type": {
            "id": 1,
            "name": "Annual Leave",
            "quota_days": 12,
            "is_paid": true
        }
    }
}
```

**Response Error (400):**

```json
{
    "message": "Cannot update leave request that has been processed"
}
```

### 7. Cancel Leave Request

**Endpoint:** `POST /leaves/{id}/cancel`  
**Auth Required:** ✅  
**Note:** Only pending leaves can be cancelled

**Response Success (200):**

```json
{
    "message": "Leave request cancelled successfully",
    "data": {
        "id": 1,
        "employee_id": 1,
        "leave_type_id": 1,
        "start_date": "2024-10-15",
        "end_date": "2024-10-17",
        "total_days": 3,
        "reason": "Family vacation",
        "status": "cancelled",
        "created_at": "2024-10-01T10:00:00.000000Z",
        "updated_at": "2024-10-02T14:30:00.000000Z"
    }
}
```

**Response Error (400):**

```json
{
    "message": "Cannot cancel leave request that has been processed"
}
```

**Response Error (403):**

```json
{
    "message": "Unauthorized"
}
```

### Leave Request Workflow

1. **Employee creates leave request** → Status: `pending`
2. **Manager reviews and approves/rejects** → Status: `approved` or `rejected`
3. **Employee can cancel pending requests** → Status: `cancelled`
4. **Approved leaves automatically deduct from balance**

### Important Notes

-   **Total days calculation**: Excludes weekends and holidays automatically
-   **Balance validation**: System checks available balance before creating request
-   **Only pending leaves** can be updated or cancelled
-   **Attachment URL**: Optional field for supporting documents
-   **Date format**: Use YYYY-MM-DD format for all dates

---

## Overtime Management

### 1. Start Overtime

**Endpoint:** `POST /start-overtime`  
**Auth Required:** ✅  
**Content-Type:** `multipart/form-data`

**Request:**

```
notes: "Finishing urgent project" (optional)
reason: "Project deadline tomorrow" (optional)
start_document_path: <file> (optional - pdf, jpg, png, max 2MB)
```

**Response Success (201):**

```json
{
    "message": "Lembur berhasil dimulai"
}
```

**Response Error (422):**

```json
{
    "message": "Anda sudah memulai lembur hari ini."
}
```

### 2. End Overtime

**Endpoint:** `POST /end-overtime`  
**Auth Required:** ✅

**Request:**

```json
{
    "id": 1,
    "reason": "Updated finish reason"
}
```

**Response Success (200):**

```json
{
    "data": {
        "id": 1,
        "user_id": 1,
        "date": "2024-10-02",
        "start_time": "17:00",
        "end_time": "20:30",
        "status": "pending",
        "reason": "Project deadline",
        "notes": "Finishing urgent project",
        "created_at": "2024-10-02T17:00:00.000000Z",
        "updated_at": "2024-10-02T20:30:00.000000Z"
    },
    "message": "Lembur berhasil diselesaikan dan menunggu persetujuan"
}
```

### 3. Get Overtime Status Today

**Endpoint:** `GET /overtime-status`  
**Auth Required:** ✅

**Response (200):**

```json
{
    "status": "in_progress",
    "message": "Lembur sedang berlangsung"
}
```

**Status Values:**

-   `not_started` - Belum mulai lembur hari ini
-   `in_progress` - Lembur sedang berlangsung
-   `completed` - Lembur sudah selesai, menunggu approval

### 4. Get Overtime History

**Endpoint:** `GET /overtimes`  
**Auth Required:** ✅  
**Query Parameters:** `?month=2024-10` (optional)

**Response (200):**

```json
{
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "date": "2024-10-02",
            "start_time": "17:00",
            "end_time": "20:30",
            "reason": "Project deadline",
            "document": "overtime_documents/file.pdf",
            "status": "approved",
            "notes": "Good work",
            "approved_at": "2024-10-03T08:00:00.000000Z",
            "approved_by": 2,
            "created_at": "2024-10-02T17:00:00.000000Z",
            "updated_at": "2024-10-03T08:00:00.000000Z"
        }
    ],
    "message": "Daftar lembur"
}
```

---

## Notes Management

### 1. Get All Notes

**Endpoint:** `GET /api-notes`  
**Auth Required:** ✅

**Response (200):**

```json
{
    "notes": [
        {
            "id": 1,
            "user_id": 1,
            "title": "Meeting Notes",
            "note": "Discussed project timeline and deliverables",
            "created_at": "2024-10-02T10:00:00.000000Z",
            "updated_at": "2024-10-02T10:00:00.000000Z"
        }
    ]
}
```

### 2. Create Note

**Endpoint:** `POST /api-notes`  
**Auth Required:** ✅

**Request:**

```json
{
    "title": "Meeting Notes",
    "note": "Discussed project timeline and deliverables"
}
```

**Response (201):**

```json
{
    "message": "Note created successfully"
}
```

### 3. Update Note

**Endpoint:** `PUT /api-notes/{id}`  
**Auth Required:** ✅

**Request:**

```json
{
    "title": "Updated Meeting Notes",
    "note": "Updated content"
}
```

### 4. Delete Note

**Endpoint:** `DELETE /api-notes/{id}`  
**Auth Required:** ✅

**Response (200):**

```json
{
    "message": "Note deleted successfully"
}
```

---

## User Profile

### 1. Get User by ID

**Endpoint:** `GET /api-user/{id}`  
**Auth Required:** ✅

**Response (200):**

```json
{
    "status": "Success",
    "message": "User found",
    "data": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@admin.com",
        "phone": "081234567890",
        "role": "admin",
        "position": "Administrator",
        "department": "IT",
        "image_url": "storage/users/profile.jpg"
    }
}
```

### 2. Update Profile

**Endpoint:** `POST /api-update-profile`  
**Auth Required:** ✅  
**Content-Type:** `multipart/form-data`

**Request:**

```
name: "Updated Name"
email: "updated@email.com"
phone: "081234567890"
face_embedding: "serialized_face_data" (optional)
image: <file> (optional - jpg, jpeg, png, max 2MB)
```

**Response (200):**

```json
{
    "message": "Profile updated successfully",
    "user": {
        "id": 1,
        "name": "Updated Name",
        "email": "updated@email.com",
        "phone": "081234567890",
        "image_url": "storage/users/new_profile.jpg"
    }
}
```

### 3. Update User Profile (Alternative)

**Endpoint:** `POST /api-user/edit`  
**Auth Required:** ✅

**Request:**

```json
{
    "id": 1,
    "name": "Updated Name",
    "email": "updated@email.com",
    "phone": "081234567890"
}
```

---

## Error Handling

### Standard HTTP Status Codes

| Code | Meaning               | Description                   |
| ---- | --------------------- | ----------------------------- |
| 200  | OK                    | Request successful            |
| 201  | Created               | Resource created successfully |
| 400  | Bad Request           | Invalid request data          |
| 401  | Unauthorized          | Invalid or missing token      |
| 403  | Forbidden             | Access denied                 |
| 404  | Not Found             | Resource not found            |
| 422  | Unprocessable Entity  | Validation errors             |
| 500  | Internal Server Error | Server error                  |

### Error Response Format

```json
{
    "message": "Error message",
    "errors": {
        "field_name": ["Validation error message"]
    }
}
```

### Flutter Error Handling Example

```dart
try {
  final response = await http.post(/* ... */);

  if (response.statusCode == 200) {
    return jsonDecode(response.body);
  } else if (response.statusCode == 401) {
    // Token expired, redirect to login
    await logout();
    throw Exception('Session expired');
  } else if (response.statusCode == 422) {
    // Validation errors
    final errors = jsonDecode(response.body)['errors'];
    throw ValidationException(errors);
  } else {
    throw Exception('Request failed: ${response.statusCode}');
  }
} catch (e) {
  print('API Error: $e');
  rethrow;
}
```

---

## Flutter Integration Examples

### 1. API Service Class

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class ApiService {
  static const String baseUrl = 'https://hris.jagoflutter.com/api';
  final storage = FlutterSecureStorage();

  Future<String?> getToken() async {
    return await storage.read(key: 'auth_token');
  }

  Future<Map<String, String>> getHeaders() async {
    final token = await getToken();
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  // Login
  Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/login'),
      headers: await getHeaders(),
      body: jsonEncode({
        'email': email,
        'password': password,
      }),
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      await storage.write(key: 'auth_token', value: data['token']);
      return data;
    } else {
      throw Exception('Login failed');
    }
  }

  // Check-in
  Future<Map<String, dynamic>> checkin(double lat, double lon) async {
    final response = await http.post(
      Uri.parse('$baseUrl/checkin'),
      headers: await getHeaders(),
      body: jsonEncode({
        'latitude': lat.toString(),
        'longitude': lon.toString(),
      }),
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Check-in failed');
    }
  }

  // Get company info
  Future<Map<String, dynamic>> getCompanyInfo() async {
    final response = await http.get(
      Uri.parse('$baseUrl/company'),
      headers: await getHeaders(),
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return data['company'];
    } else {
      throw Exception('Failed to fetch company info');
    }
  }

  // Leave Management
  Future<List<dynamic>> getLeaveTypes() async {
    final response = await http.get(
      Uri.parse('$baseUrl/leave-types'),
      headers: await getHeaders(),
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return data['data'];
    } else {
      throw Exception('Failed to fetch leave types');
    }
  }

  Future<List<dynamic>> getLeaveBalance({int? year}) async {
    String url = '$baseUrl/leave-balance';
    if (year != null) {
      url += '?year=$year';
    }

    final response = await http.get(
      Uri.parse(url),
      headers: await getHeaders(),
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return data['data'];
    } else {
      throw Exception('Failed to fetch leave balance');
    }
  }

  Future<List<dynamic>> getLeaves({String? status}) async {
    String url = '$baseUrl/leaves';
    if (status != null) {
      url += '?status=$status';
    }

    final response = await http.get(
      Uri.parse(url),
      headers: await getHeaders(),
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return data['data'];
    } else {
      throw Exception('Failed to fetch leaves');
    }
  }

  Future<Map<String, dynamic>> createLeaveRequest({
    required int leaveTypeId,
    required String startDate,
    required String endDate,
    String? reason,
    String? attachmentUrl,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/leaves'),
      headers: await getHeaders(),
      body: jsonEncode({
        'leave_type_id': leaveTypeId,
        'start_date': startDate,
        'end_date': endDate,
        'reason': reason,
        'attachment_url': attachmentUrl,
      }),
    );

    if (response.statusCode == 201) {
      final data = jsonDecode(response.body);
      return data['data'];
    } else {
      final error = jsonDecode(response.body);
      throw Exception(error['message']);
    }
  }

  Future<void> cancelLeaveRequest(int leaveId) async {
    final response = await http.post(
      Uri.parse('$baseUrl/leaves/$leaveId/cancel'),
      headers: await getHeaders(),
    );

    if (response.statusCode != 200) {
      final error = jsonDecode(response.body);
      throw Exception(error['message']);
    }
  }
}
```

### 2. Location Helper

```dart
import 'package:geolocator/geolocator.dart';
import 'dart:math' show cos, sqrt, asin;

class LocationHelper {
  static Future<bool> requestPermission() async {
    LocationPermission permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
    }
    return permission == LocationPermission.always ||
           permission == LocationPermission.whileInUse;
  }

  static Future<Position> getCurrentPosition() async {
    return await Geolocator.getCurrentPosition(
      desiredAccuracy: LocationAccuracy.high,
    );
  }

  static double calculateDistance(
    double lat1, double lon1,
    double lat2, double lon2,
  ) {
    const p = 0.017453292519943295; // PI / 180
    final a = 0.5 - cos((lat2 - lat1) * p) / 2 +
        cos(lat1 * p) * cos(lat2 * p) *
        (1 - cos((lon2 - lon1) * p)) / 2;
    return 12742 * asin(sqrt(a)); // 2 * R; R = 6371 km
  }

  static bool isWithinRadius(
    Position currentPosition,
    double officeLat,
    double officeLon,
    double radiusKm,
  ) {
    double distance = calculateDistance(
      currentPosition.latitude,
      currentPosition.longitude,
      officeLat,
      officeLon,
    );
    return distance <= radiusKm;
  }
}
```

### 3. Attendance Provider (State Management)

```dart
import 'package:flutter/material.dart';

class AttendanceProvider with ChangeNotifier {
  final ApiService _api = ApiService();

  bool _isCheckedIn = false;
  bool _isCheckedOut = false;
  bool _isLoading = false;
  String? _errorMessage;

  bool get isCheckedIn => _isCheckedIn;
  bool get isCheckedOut => _isCheckedOut;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  Future<void> checkStatus() async {
    try {
      _errorMessage = null;
      final response = await _api.get('/is-checkin');
      _isCheckedIn = response['checkedin'];
      _isCheckedOut = response['checkedout'];
      notifyListeners();
    } catch (e) {
      _errorMessage = e.toString();
      notifyListeners();
    }
  }

  Future<bool> performCheckin() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      // Get location
      final hasPermission = await LocationHelper.requestPermission();
      if (!hasPermission) {
        throw Exception('Location permission denied');
      }

      final position = await LocationHelper.getCurrentPosition();

      // Get company info for validation
      final company = await _api.getCompanyInfo();

      // Validate radius
      final inRange = LocationHelper.isWithinRadius(
        position,
        double.parse(company['latitude']),
        double.parse(company['longitude']),
        double.parse(company['radius_km']),
      );

      if (!inRange) {
        throw Exception('You are outside office radius');
      }

      // Perform check-in
      await _api.checkin(position.latitude, position.longitude);

      _isCheckedIn = true;
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }
}
```

### 4. Leave Provider (State Management)

```dart
import 'package:flutter/material.dart';

class LeaveProvider with ChangeNotifier {
  final ApiService _api = ApiService();

  List<dynamic> _leaveTypes = [];
  List<dynamic> _leaveBalance = [];
  List<dynamic> _leaves = [];
  bool _isLoading = false;
  String? _errorMessage;

  List<dynamic> get leaveTypes => _leaveTypes;
  List<dynamic> get leaveBalance => _leaveBalance;
  List<dynamic> get leaves => _leaves;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  Future<void> loadLeaveTypes() async {
    try {
      _isLoading = true;
      _errorMessage = null;
      notifyListeners();

      _leaveTypes = await _api.getLeaveTypes();

      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> loadLeaveBalance({int? year}) async {
    try {
      _isLoading = true;
      _errorMessage = null;
      notifyListeners();

      _leaveBalance = await _api.getLeaveBalance(year: year);

      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> loadLeaves({String? status}) async {
    try {
      _isLoading = true;
      _errorMessage = null;
      notifyListeners();

      _leaves = await _api.getLeaves(status: status);

      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> createLeaveRequest({
    required int leaveTypeId,
    required String startDate,
    required String endDate,
    String? reason,
    String? attachmentUrl,
  }) async {
    try {
      _isLoading = true;
      _errorMessage = null;
      notifyListeners();

      await _api.createLeaveRequest(
        leaveTypeId: leaveTypeId,
        startDate: startDate,
        endDate: endDate,
        reason: reason,
        attachmentUrl: attachmentUrl,
      );

      // Refresh leaves list
      await loadLeaves();
      await loadLeaveBalance();

      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> cancelLeaveRequest(int leaveId) async {
    try {
      _isLoading = true;
      _errorMessage = null;
      notifyListeners();

      await _api.cancelLeaveRequest(leaveId);

      // Refresh leaves list
      await loadLeaves();
      await loadLeaveBalance();

      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  int getRemainingDays(int leaveTypeId) {
    final balance = _leaveBalance.firstWhere(
      (b) => b['leave_type_id'] == leaveTypeId,
      orElse: () => null,
    );
    return balance?['remaining_days'] ?? 0;
  }
}
```

### 5. Complete Attendance Screen Example

```dart
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

class AttendanceScreen extends StatefulWidget {
  @override
  _AttendanceScreenState createState() => _AttendanceScreenState();
}

class _AttendanceScreenState extends State<AttendanceScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<AttendanceProvider>().checkStatus();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Attendance'),
      ),
      body: Consumer<AttendanceProvider>(
        builder: (context, provider, child) {
          if (provider.isLoading) {
            return Center(child: CircularProgressIndicator());
          }

          return Padding(
            padding: EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Status Card
                Card(
                  child: Padding(
                    padding: EdgeInsets.all(16.0),
                    child: Column(
                      children: [
                        Text(
                          'Today Status',
                          style: Theme.of(context).textTheme.headlineSmall,
                        ),
                        SizedBox(height: 16),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceAround,
                          children: [
                            _buildStatusItem(
                              'Check-in',
                              provider.isCheckedIn ? 'Done' : 'Not yet',
                              provider.isCheckedIn ? Colors.green : Colors.red,
                            ),
                            _buildStatusItem(
                              'Check-out',
                              provider.isCheckedOut ? 'Done' : 'Not yet',
                              provider.isCheckedOut ? Colors.green : Colors.red,
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ),

                SizedBox(height: 20),

                // Error Message
                if (provider.errorMessage != null)
                  Card(
                    color: Colors.red.shade50,
                    child: Padding(
                      padding: EdgeInsets.all(16.0),
                      child: Text(
                        provider.errorMessage!,
                        style: TextStyle(color: Colors.red),
                      ),
                    ),
                  ),

                SizedBox(height: 20),

                // Action Buttons
                if (!provider.isCheckedIn)
                  ElevatedButton.icon(
                    onPressed: () async {
                      final success = await provider.performCheckin();
                      if (success) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(content: Text('Check-in successful!')),
                        );
                      }
                    },
                    icon: Icon(Icons.login),
                    label: Text('Check-in'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.green,
                      foregroundColor: Colors.white,
                      padding: EdgeInsets.symmetric(vertical: 16),
                    ),
                  ),

                if (provider.isCheckedIn && !provider.isCheckedOut)
                  ElevatedButton.icon(
                    onPressed: () async {
                      final success = await provider.performCheckout();
                      if (success) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(content: Text('Check-out successful!')),
                        );
                      }
                    },
                    icon: Icon(Icons.logout),
                    label: Text('Check-out'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.red,
                      foregroundColor: Colors.white,
                      padding: EdgeInsets.symmetric(vertical: 16),
                    ),
                  ),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildStatusItem(String title, String status, Color color) {
    return Column(
      children: [
        Text(title, style: TextStyle(fontWeight: FontWeight.bold)),
        SizedBox(height: 8),
        Container(
          padding: EdgeInsets.symmetric(horizontal: 12, vertical: 6),
          decoration: BoxDecoration(
            color: color.withOpacity(0.1),
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: color),
          ),
          child: Text(
            status,
            style: TextStyle(color: color, fontWeight: FontWeight.bold),
          ),
        ),
      ],
    );
  }
}
```

### 6. Leave Request Screen Example

```dart
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

class LeaveRequestScreen extends StatefulWidget {
  @override
  _LeaveRequestScreenState createState() => _LeaveRequestScreenState();
}

class _LeaveRequestScreenState extends State<LeaveRequestScreen> {
  final _formKey = GlobalKey<FormState>();
  final _reasonController = TextEditingController();

  int? _selectedLeaveTypeId;
  DateTime? _startDate;
  DateTime? _endDate;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final provider = context.read<LeaveProvider>();
      provider.loadLeaveTypes();
      provider.loadLeaveBalance();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Leave Request'),
      ),
      body: Consumer<LeaveProvider>(
        builder: (context, provider, child) {
          if (provider.isLoading && provider.leaveTypes.isEmpty) {
            return Center(child: CircularProgressIndicator());
          }

          return Form(
            key: _formKey,
            child: Padding(
              padding: EdgeInsets.all(16.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Leave Type Dropdown
                  DropdownButtonFormField<int>(
                    value: _selectedLeaveTypeId,
                    decoration: InputDecoration(
                      labelText: 'Leave Type',
                      border: OutlineInputBorder(),
                    ),
                    items: provider.leaveTypes.map<DropdownMenuItem<int>>((type) {
                      final remainingDays = provider.getRemainingDays(type['id']);
                      return DropdownMenuItem<int>(
                        value: type['id'],
                        child: Text('${type['name']} ($remainingDays days left)'),
                      );
                    }).toList(),
                    onChanged: (value) {
                      setState(() {
                        _selectedLeaveTypeId = value;
                      });
                    },
                    validator: (value) {
                      if (value == null) {
                        return 'Please select a leave type';
                      }
                      return null;
                    },
                  ),

                  SizedBox(height: 16),

                  // Start Date
                  ListTile(
                    title: Text('Start Date'),
                    subtitle: Text(_startDate?.toString().split(' ')[0] ?? 'Select date'),
                    trailing: Icon(Icons.calendar_today),
                    onTap: () async {
                      final date = await showDatePicker(
                        context: context,
                        initialDate: DateTime.now(),
                        firstDate: DateTime.now(),
                        lastDate: DateTime.now().add(Duration(days: 365)),
                      );
                      if (date != null) {
                        setState(() {
                          _startDate = date;
                          if (_endDate != null && _endDate!.isBefore(date)) {
                            _endDate = null;
                          }
                        });
                      }
                    },
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(8),
                      side: BorderSide(color: Colors.grey),
                    ),
                  ),

                  SizedBox(height: 16),

                  // End Date
                  ListTile(
                    title: Text('End Date'),
                    subtitle: Text(_endDate?.toString().split(' ')[0] ?? 'Select date'),
                    trailing: Icon(Icons.calendar_today),
                    onTap: _startDate == null ? null : () async {
                      final date = await showDatePicker(
                        context: context,
                        initialDate: _startDate!,
                        firstDate: _startDate!,
                        lastDate: DateTime.now().add(Duration(days: 365)),
                      );
                      if (date != null) {
                        setState(() {
                          _endDate = date;
                        });
                      }
                    },
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(8),
                      side: BorderSide(color: Colors.grey),
                    ),
                  ),

                  SizedBox(height: 16),

                  // Reason
                  TextFormField(
                    controller: _reasonController,
                    decoration: InputDecoration(
                      labelText: 'Reason (Optional)',
                      border: OutlineInputBorder(),
                    ),
                    maxLines: 3,
                  ),

                  SizedBox(height: 20),

                  // Error Message
                  if (provider.errorMessage != null)
                    Card(
                      color: Colors.red.shade50,
                      child: Padding(
                        padding: EdgeInsets.all(16.0),
                        child: Text(
                          provider.errorMessage!,
                          style: TextStyle(color: Colors.red),
                        ),
                      ),
                    ),

                  SizedBox(height: 20),

                  // Submit Button
                  ElevatedButton(
                    onPressed: provider.isLoading ? null : _submitRequest,
                    child: provider.isLoading
                        ? CircularProgressIndicator(color: Colors.white)
                        : Text('Submit Request'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.blue,
                      foregroundColor: Colors.white,
                      padding: EdgeInsets.symmetric(vertical: 16),
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  void _submitRequest() async {
    if (!_formKey.currentState!.validate() ||
        _startDate == null ||
        _endDate == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Please fill all required fields')),
      );
      return;
    }

    final provider = context.read<LeaveProvider>();
    final success = await provider.createLeaveRequest(
      leaveTypeId: _selectedLeaveTypeId!,
      startDate: _startDate!.toString().split(' ')[0],
      endDate: _endDate!.toString().split(' ')[0],
      reason: _reasonController.text.isNotEmpty ? _reasonController.text : null,
    );

    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Leave request submitted successfully!')),
      );
      Navigator.pop(context);
    }
  }

  @override
  void dispose() {
    _reasonController.dispose();
    super.dispose();
  }
}
```

---

## Testing with Postman

Import the Postman collection from:

```
postman-collection/FIC16-Absensi.postman_collection.json
```

### Environment Variables

Create environment in Postman with:

```
base_url: https://hris.jagoflutter.com/api
token: {{auth_token}} (will be set after login)
```

---

## Rate Limiting

API has rate limiting configured:

-   Login: 5 attempts per minute
-   General API: 60 requests per minute per user

---

## Security Notes

1. **Always use HTTPS** in production
2. **Store tokens securely** using flutter_secure_storage
3. **Handle token expiration** (401 responses)
4. **Validate file uploads** before sending
5. **Implement proper error handling**
6. **Use proper GPS permissions**

---

## Support

For API issues or questions:

-   Email: support@jagohris.com
-   Documentation: This file
-   Postman Collection: Available in project

---

**Last Updated:** October 2, 2024  
**API Version:** v1.0  
**Backend Version:** Laravel 12.31.1
