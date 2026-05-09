# Overtime API Integration

Panduan integrasi endpoint lembur (`/api/*`) untuk aplikasi klien.

## Autentikasi

Semua endpoint di bawah ini membutuhkan token Bearer dari Laravel Sanctum. Sertakan header berikut pada setiap permintaan:

```http
Authorization: Bearer {token_sanctum_anda}
Accept: application/json
```

## Endpoint

### 1. Mulai Lembur

- **Method:** `POST`
- **URL:** `/api/start-overtime`
- **Konten:** `multipart/form-data` jika mengunggah dokumen, jika tidak cukup `application/json`

| Field               | Tipe               | Wajib | Keterangan                                                       |
|---------------------|--------------------|-------|-------------------------------------------------------------------|
| `notes`             | string, max 255    | ❌    | Catatan tambahan terkait lembur                                   |
| `reason`            | string, max 255    | ❌    | Alasan lembur                                                     |
| `start_document_path` | file (pdf/jpg/jpeg/png), max 2 MB | ❌ | Bukti pendukung saat mulai lembur                                 |

**Contoh cURL (multipart):**

```bash
curl -X POST https://hris.jagoflutter.com/api/start-overtime \
  -H "Authorization: Bearer $TOKEN" \
  -F "notes=Lembur untuk deployment" \
  -F "reason=Mendampingi go-live" \
  -F "start_document_path=@/path/to/bukti.png"
```

**Respons sukses (201):**

```json
{
  "message": "Lembur berhasil dimulai"
}
```

**Respons gagal (422):**

```json
{
  "message": "Anda sudah memulai lembur hari ini."
}
```

### 2. Akhiri Lembur

- **Method:** `POST`
- **URL:** `/api/end-overtime`
- **Konten:** `application/json`

| Field    | Tipe            | Wajib | Keterangan                                                 |
|----------|-----------------|-------|-------------------------------------------------------------|
| `id`     | integer         | ✅    | ID lembur yang sedang berjalan                             |
| `reason` | string, max 255 | ❌    | Alasan terbaru (menimpa alasan sebelumnya jika dikirimkan) |

**Contoh cURL:**

```bash
curl -X POST https://hris.jagoflutter.com/api/end-overtime \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "id": 42,
    "reason": "Selesai mengawal deployment"
  }'
```

**Respons sukses (200):**

```json
{
  "data": {
    "id": 42,
    "user_id": 7,
    "date": "2024-10-10",
    "start_time": "18:05",
    "end_time": "22:15",
    "reason": "Selesai mengawal deployment",
    "document": "overtime_documents/1728209000_bukti.png",
    "status": "pending",
    "notes": "Lembur untuk deployment",
    "approved_at": null,
    "approved_by": null,
    "created_at": "2024-10-10T11:05:00.000000Z",
    "updated_at": "2024-10-10T15:15:00.000000Z"
  },
  "message": "Lembur berhasil diselesaikan dan menunggu persetujuan"
}
```

**Respons gagal (404):**

```json
{
  "message": "Data lembur tidak ditemukan."
}
```

### 3. Status Lembur Hari Ini

- **Method:** `GET`
- **URL:** `/api/overtime-status`

**Respons sukses (200):**

```json
{
  "status": "in_progress",
  "message": "Lembur sedang berlangsung"
}
```

Nilai `status` yang mungkin:

- `not_started` – belum ada lembur untuk hari ini
- `in_progress` – lembur sudah dimulai tetapi belum diakhiri
- `completed` – lembur hari ini telah diakhiri dan menunggu persetujuan

### 4. Daftar Lembur

- **Method:** `GET`
- **URL:** `/api/overtimes`
- **Query opsional:** `month=YYYY-MM` (contoh `2024-10`) untuk memfilter berdasarkan bulan

**Contoh permintaan:**

```bash
curl -X GET "https://hris.jagoflutter.com/api/overtimes?month=2024-10" \
  -H "Authorization: Bearer $TOKEN"
```

**Respons sukses (200):**

```json
{
  "data": [
    {
      "id": 41,
      "user_id": 7,
      "date": "2024-10-09",
      "start_time": "19:00",
      "end_time": "21:30",
      "reason": "Persiapan laporan",
      "document": null,
      "status": "pending",
      "notes": "Menyelesaikan laporan audit",
      "approved_at": null,
      "approved_by": null,
      "created_at": "2024-10-09T12:00:00.000000Z",
      "updated_at": "2024-10-09T14:30:00.000000Z"
    }
  ],
  "message": "Daftar lembur"
}
```

## Catatan Penting

- Server menggunakan zona waktu aplikasi (`Asia/Jakarta`) ketika mencatat `start_time` dan `end_time`.
- Field `status` akan tetap `pending` sampai supervisor/atasan meninjau dan menyetujui.
- Simpan ID lembur yang dikembalikan saat mulai lembur untuk digunakan ketika mengakhiri lembur.
*** End Patch
