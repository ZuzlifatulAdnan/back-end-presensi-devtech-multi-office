# API Fitur Baru — WFH, Izin, Ubah Password, Pengaturan Aplikasi & Peta

Dokumen ini menjelaskan endpoint **baru dan yang berubah** untuk integrasi aplikasi mobile.
Untuk endpoint lama yang tidak disebut di sini, lihat [`API_DOCUMENTATION.md`](../API_DOCUMENTATION.md).

## Daftar Isi

1. [Ringkasan Perubahan](#ringkasan-perubahan)
2. [Format Respons Baru](#format-respons-baru)
3. [Tabel Endpoint](#tabel-endpoint)
4. [Pengaturan Aplikasi (Nama & Logo)](#1-pengaturan-aplikasi-nama--logo)
5. [Login & Sesi](#2-login--sesi)
6. [Peta Sebelum Presensi](#3-peta-sebelum-presensi)
7. [Presensi WFO / WFH / WFA](#4-presensi-wfo--wfh--wfa)
8. [Riwayat & Rekap Presensi](#5-riwayat--rekap-presensi)
9. [Izin & Cuti (Upload Dokumen)](#6-izin--cuti-upload-dokumen)
10. [Ubah Password](#7-ubah-password)
11. [Profil Pengguna](#8-profil-pengguna)
12. [Aturan Mode Kerja](#aturan-mode-kerja)
13. [Kode Status & Penanganan Error](#kode-status--penanganan-error)
14. [Contoh Integrasi Flutter](#contoh-integrasi-flutter)
15. [Pengaturan di Panel Admin](#pengaturan-di-panel-admin)
16. [Perubahan Database](#perubahan-database)
17. [Catatan Upgrade Aplikasi Lama](#catatan-upgrade-aplikasi-lama)

---

## Ringkasan Perubahan

| Fitur | Status |
| --- | --- |
| Presensi WFH/WFA masuk ke tabel `attendances` (foto, catatan, alamat, jarak) | Baru |
| Endpoint peta + kelayakan presensi sebelum tombol "Presensi" ditekan | Baru |
| Upload lampiran izin/cuti (JPG, PNG, WEBP, PDF s/d 5 MB) | Diperbaiki |
| Ubah password dengan validasi kuat + cabut sesi perangkat lain | Diperbaiki |
| Nama aplikasi, logo, warna, versi minimum diambil dari API | Baru |
| Deteksi fake GPS, blokir presensi saat cuti disetujui, anti double check-in | Baru |
| Format respons seragam `{success, message, data, meta}` | Baru |
| Rate limit login & presensi | Baru |

---

## Format Respons Baru

Semua endpoint di bawah memakai satu bentuk respons.

**Sukses**

```json
{
  "success": true,
  "message": "Absen masuk berhasil.",
  "data": { },
  "meta": { }
}
```

`meta` hanya muncul bila relevan (paginasi / jumlah total).

**Gagal**

```json
{
  "success": false,
  "message": "Anda berada di luar radius kantor.",
  "errors": { "field": ["pesan validasi"] },
  "data": { "distance_meters": 11793.23 }
}
```

- `message` selalu berbahasa Indonesia dan **aman ditampilkan langsung** ke pengguna.
- `errors` hanya muncul pada error validasi (422).
- `data` pada respons gagal berisi konteks tambahan (mis. jarak ke kantor).

> **Kompatibilitas:** endpoint lama (`/checkin`, `/checkout`, `/is-checkin`, `/api-attendances`, `/login`, `/me`, `/company`) tetap mengirim key lamanya (`attendance`, `user`, `role`, `company`, `checkedin`, dll.) selain key baru, sehingga build aplikasi yang sekarang tidak perlu langsung diubah.

---

## Tabel Endpoint

| Method | Endpoint | Auth | Keterangan |
| --- | --- | :---: | --- |
| GET | `/api/app-settings` | — | Nama, logo, warna, versi, aturan presensi |
| POST | `/api/login` | — | Login (rate limit 5 percobaan/menit per email+IP) |
| POST | `/api/logout` | ✅ | Keluar dari perangkat ini |
| POST | `/api/logout-all` | ✅ | Keluar dari semua perangkat |
| GET | `/api/me` | ✅ | Profil + pengaturan aplikasi |
| GET | `/api/attendance/pre-check` | ✅ | **Data peta & kelayakan presensi** |
| GET | `/api/attendance/locations` | ✅ | Daftar pin kantor untuk peta |
| POST | `/api/attendance/check-in` | ✅ | Absen masuk (WFO/WFH/WFA) |
| POST | `/api/attendance/check-out` | ✅ | Absen pulang |
| GET | `/api/attendance/today` | ✅ | Status presensi hari ini |
| GET | `/api/attendance/history` | ✅ | Riwayat presensi (filter + paginasi) |
| GET | `/api/attendance/summary` | ✅ | Rekap bulanan |
| GET | `/api/companies` | ✅ | Semua kantor yang boleh dipakai user |
| GET | `/api/leave-types` | ✅ | Jenis izin/cuti |
| GET | `/api/leave-balance` | ✅ | Sisa kuota |
| GET | `/api/leaves` | ✅ | Daftar pengajuan |
| POST | `/api/leaves` | ✅ | Ajukan izin/cuti + lampiran |
| GET | `/api/leaves/{id}` | ✅ | Detail pengajuan |
| POST | `/api/leaves/{id}` | ✅ | Ubah pengajuan (multipart) |
| POST | `/api/leaves/{id}/cancel` | ✅ | Batalkan pengajuan |
| POST | `/api/leaves/{id}/approve` | ✅ | Setujui (admin/manager/hr) |
| POST | `/api/leaves/{id}/reject` | ✅ | Tolak (admin/manager/hr) |
| POST | `/api/change-password` | ✅ | Ubah password |
| POST | `/api/api-user/edit` | ✅ | Ubah profil sendiri |

Alias lama yang tetap berfungsi: `POST /api/checkin`, `POST /api/checkout`, `GET /api/is-checkin`, `GET /api/api-attendances`, `POST /api/api-user/update-password`, `GET /api/user`.

---

## 1. Pengaturan Aplikasi (Nama & Logo)

**`GET /api/app-settings`** — tanpa token. Panggil saat splash screen, sebelum login.

```json
{
  "success": true,
  "message": "Pengaturan aplikasi berhasil dimuat.",
  "data": {
    "app_name": "AbsenDav Tech KI",
    "app_short_name": "AbsenDav",
    "tagline": "Presensi cepat, akurat, dan transparan",
    "logo_url": "https://host/storage/branding/logo.png",
    "logo_dark_url": "https://host/storage/branding/logo-dark.png",
    "favicon_url": null,
    "login_banner_url": null,
    "theme": {
      "primary_color": "#2563eb",
      "secondary_color": null
    },
    "company": {
      "name": "PT. ABC Technology",
      "address": "Jl. Sudirman No. 123, Jakarta Pusat",
      "support_email": "hr@abc.co.id",
      "support_phone": "02112345678",
      "website": "https://abc.co.id"
    },
    "version": {
      "android_latest": "1.4.0",
      "android_minimum": "1.2.0",
      "ios_latest": null,
      "ios_minimum": null,
      "force_update": false
    },
    "maintenance": {
      "enabled": false,
      "message": null
    },
    "attendance": {
      "photo_required": false,
      "wfh_enabled": true,
      "wfh_photo_required": true,
      "wfh_notes_required": true,
      "block_mock_location": true,
      "accuracy_tolerance_meters": 50
    },
    "map": {
      "default_zoom": 17,
      "tile_url": "https://tile.openstreetmap.org/{z}/{x}/{y}.png",
      "attribution": "OpenStreetMap contributors"
    },
    "updated_at": "2026-09-01T19:55:19+07:00"
  }
}
```

Cara pakai di aplikasi:

- `app_name` / `logo_url` → judul & logo splash, login, drawer.
- `theme.primary_color` → warna utama tema (parse hex).
- `version.android_minimum` + `force_update` → paksa update jika versi aplikasi di bawahnya.
- `maintenance.enabled` → tampilkan halaman pemeliharaan, sembunyikan tombol presensi.
- `attendance.*` → tentukan apakah kamera & field catatan wajib ditampilkan.
- `map.*` → konfigurasi `flutter_map`.

Objek yang sama juga ikut di respons `/api/login` dan `/api/me` pada `data.app`, jadi tidak perlu request terpisah setelah login.

Semua nilai di atas diubah dari panel admin, menu **Pengaturan → Pengaturan Aplikasi**. Nilai di-cache 1 jam di server dan otomatis di-refresh saat admin menyimpan.

---

## 2. Login & Sesi

**`POST /api/login`**

```json
{
  "email": "john@company.com",
  "password": "password",
  "fcm_token": "opsional-token-firebase",
  "device_name": "Pixel 8"
}
```

Respons berisi `token`, `data.user`, `data.app`, plus key lama (`user`, `role`, `work_mode`, `company`, `position`, `default_shift`, `default_shift_detail`, `department`).

- Salah kredensial → **401** `"Email atau password salah."`
- Lebih dari 5 percobaan gagal per email+IP → **429** dengan sisa detik di pesan.
- `fcm_token` bila dikirim langsung disimpan, tidak perlu panggil `/api/update-fcm-token` lagi.

**`POST /api/logout-all`** mencabut token di semua perangkat (berguna untuk tombol "Keluar dari semua perangkat").

---

## 3. Peta Sebelum Presensi

**`GET /api/attendance/pre-check?latitude=-6.208763&longitude=106.845599`**

Panggil endpoint ini saat halaman presensi dibuka dan setiap kali posisi GPS berubah. Satu request memberi semua yang dibutuhkan untuk menggambar peta dan menentukan tombol aktif atau tidak.

```json
{
  "success": true,
  "message": "Status presensi berhasil dimuat.",
  "data": {
    "server_time": "2026-09-01T19:56:27+07:00",
    "date": "2026-09-01",
    "is_weekend": false,
    "is_holiday": false,
    "next_action": "check_in",
    "can_check_in": true,
    "can_check_out": false,
    "blockers": [],
    "work_mode": {
      "default": "wfo",
      "allowed": ["wfo"],
      "requires_location": true,
      "location_ready": true
    },
    "requirements": {
      "photo_required": false,
      "remote_photo_required": true,
      "remote_notes_required": true,
      "block_mock_location": true,
      "accuracy_tolerance_meters": 50
    },
    "map": {
      "default_zoom": 17,
      "tile_url": "https://tile.openstreetmap.org/{z}/{x}/{y}.png",
      "attribution": "OpenStreetMap contributors",
      "user_position": { "latitude": -6.208763, "longitude": 106.845599 }
    },
    "nearest_location": {
      "id": 1,
      "name": "PT. ABC Technology",
      "address": "Jl. Sudirman No. 123, Jakarta Pusat, DKI Jakarta",
      "latitude": -6.208763,
      "longitude": 106.845599,
      "radius_meters": 500,
      "distance_meters": 0,
      "within_radius": true
    },
    "locations": [ { "...sama seperti nearest_location..." } ],
    "shift": {
      "id": 1,
      "name": "Shift Pagi",
      "start_time": "08:00",
      "end_time": "17:00",
      "grace_period_minutes": 10,
      "is_cross_day": false,
      "starts_at": "2026-09-01T08:00:00+07:00",
      "ends_at": "2026-09-01T17:00:00+07:00"
    },
    "attendance": null
  }
}
```

Panduan pemakaian:

| Field | Kegunaan di UI |
| --- | --- |
| `map.tile_url`, `map.default_zoom` | Konfigurasi `TileLayer` dan zoom awal `flutter_map` |
| `map.user_position` | Marker posisi pengguna |
| `locations[]` | Marker kantor + `CircleMarker` radius (`radius_meters`) |
| `nearest_location.distance_meters` | Teks "Jarak ke kantor: 120 m" |
| `nearest_location.within_radius` | Warna lingkaran hijau / merah |
| `next_action` | `check_in`, `check_out`, atau `done` — menentukan label tombol |
| `can_check_in` / `can_check_out` | Enable / disable tombol |
| `blockers[]` | Daftar alasan tombol nonaktif, tampilkan sebagai peringatan |
| `work_mode.allowed` | Pilihan mode kerja yang boleh dipilih pengguna |
| `requirements.*` | Menentukan kamera & field catatan wajib atau tidak |
| `server_time` | Jam yang ditampilkan (jangan pakai jam perangkat) |

`latitude` dan `longitude` boleh dikosongkan (mis. izin lokasi belum diberikan). Daftar kantor tetap dikirim, hanya `distance_meters` bernilai `null`.

**`GET /api/attendance/locations?latitude=&longitude=`** mengembalikan hanya bagian `locations`, `nearest_location`, dan `map` — untuk halaman peta yang berdiri sendiri.

---

## 4. Presensi WFO / WFH / WFA

### Absen Masuk

**`POST /api/attendance/check-in`** (alias lama: `POST /api/checkin`)

Kirim sebagai **`multipart/form-data`** bila menyertakan foto.

| Field | Tipe | Wajib | Keterangan |
| --- | --- | :---: | --- |
| `latitude` | number | ✅ | -90 s/d 90 |
| `longitude` | number | ✅ | -180 s/d 180 |
| `work_mode` | string | — | `wfo` \| `wfh` \| `wfa`. Default: `work_mode` pengguna |
| `photo` | file | Kondisional | JPG/PNG/WEBP, maks 4 MB |
| `notes` | string | Kondisional | Catatan aktivitas, maks 1000 karakter |
| `address` | string | — | Hasil reverse geocoding di aplikasi, maks 500 karakter |
| `is_mock_location` | boolean | — | Hasil deteksi fake GPS di aplikasi |
| `device_info` | string | — | Contoh: `Pixel 8 / Android 15` |
| `accuracy` | number | — | Akurasi GPS dalam meter |

Kapan `photo` dan `notes` wajib:

- `photo` wajib jika `requirements.photo_required = true`, **atau** mode `wfh`/`wfa` dan `requirements.remote_photo_required = true`.
- `notes` wajib jika mode `wfh`/`wfa` dan `requirements.remote_notes_required = true`.

**Respons sukses (201)**

```json
{
  "success": true,
  "message": "Absen masuk berhasil.",
  "data": {
    "id": 788,
    "user_id": 3,
    "date": "2026-09-01",
    "time_in": "08:05",
    "time_out": null,
    "status": "on_time",
    "status_label": "Tepat Waktu",
    "work_mode": "wfh",
    "work_mode_label": "WFH",
    "is_remote": true,
    "is_checked_out": false,
    "late_minutes": 0,
    "early_leave_minutes": 0,
    "work_duration_minutes": null,
    "is_weekend": false,
    "is_holiday": false,
    "holiday_work": false,
    "is_mock_location": false,
    "shift_id": 1,
    "company_id": null,
    "latlon_in": "-6.3,106.9",
    "latlon_out": null,
    "check_in": {
      "latlon": "-6.3,106.9",
      "coordinates": { "latitude": -6.3, "longitude": 106.9 },
      "address": "Jl. Mawar No. 5, Depok",
      "photo_url": "https://host/storage/attendances/2026/09/3-in-20260901195735.jpg",
      "notes": "Mengerjakan laporan bulanan dari rumah",
      "distance_meters": 11793
    },
    "check_out": { "latlon": null, "coordinates": null, "address": null, "photo_url": null, "notes": null, "distance_meters": null },
    "shift": { "id": 1, "name": "Shift Pagi", "start_time": "08:00", "end_time": "17:00", "is_cross_day": false },
    "company": null,
    "created_at": "2026-09-01T08:05:00+07:00",
    "updated_at": "2026-09-01T08:05:00+07:00"
  },
  "attendance": { "…sama dengan data, key lama untuk kompatibilitas…" }
}
```

**Kemungkinan penolakan**

| Status | Pesan | Sebab |
| :---: | --- | --- |
| 403 | `Mode kerja WFH tidak diizinkan untuk akun Anda.` | `work_mode` pengguna hanya `wfo`. `data.allowed_work_modes` berisi mode yang boleh |
| 409 | `Anda sudah melakukan absen masuk hari ini.` | Sudah ada data hari ini |
| 409 | `Anda sedang dalam masa Sick Leave yang telah disetujui pada tanggal ini.` | Ada cuti disetujui yang mencakup tanggal ini |
| 422 | `Anda berada di luar radius kantor.` | Mode WFO di luar radius. `data` berisi `distance_meters`, `nearest_location`, `radius_meters` |
| 422 | `Foto bukti absen masuk wajib dilampirkan.` | Foto wajib tapi tidak dikirim |
| 422 | `Catatan aktivitas wajib diisi untuk presensi WFH/WFA.` | Catatan wajib tapi kosong |
| 422 | `Lokasi palsu (fake GPS) terdeteksi…` | `is_mock_location = true` dan admin memblokirnya |
| 422 | `Belum ada lokasi kantor aktif yang dikonfigurasi. Hubungi admin.` | Tidak ada kantor aktif |
| 503 | Pesan pemeliharaan dari admin | Mode pemeliharaan aktif |

### Absen Pulang

**`POST /api/attendance/check-out`** (alias lama: `POST /api/checkout`)

Field sama dengan absen masuk, tanpa `work_mode` (diambil dari data absen masuk). Foto pada absen pulang hanya wajib bila `requirements.photo_required = true`.

Catatan penting:

- Validasi radius **hanya** berlaku untuk presensi yang masuk dengan mode `wfo`.
- Untuk shift lintas hari (`is_cross_day`), absen pulang setelah lewat tengah malam otomatis dicocokkan ke data absen masuk hari sebelumnya.
- `early_leave_minutes` dan `work_duration_minutes` dihitung otomatis.
- Absen pulang dua kali → **409** `Anda sudah melakukan absen pulang hari ini.`
- Belum absen masuk → **409** `Anda belum melakukan absen masuk.`

### Status Hari Ini

**`GET /api/attendance/today`**

```json
{
  "success": true,
  "message": "Status presensi hari ini berhasil dimuat.",
  "data": {
    "checkedin": true,
    "checkedout": false,
    "next_action": "check_out",
    "attendance": { "…objek presensi…" }
  }
}
```

Endpoint lama `GET /api/is-checkin` tetap tersedia dan masih mengirim `checkedin` / `checkedout` di level atas.

---

## 5. Riwayat & Rekap Presensi

**`GET /api/attendance/history`** (alias lama: `GET /api/api-attendances`)

Query string yang didukung:

| Parameter | Contoh | Keterangan |
| --- | --- | --- |
| `date` | `2026-09-01` | Satu tanggal |
| `from` / `to` | `2026-09-01` / `2026-09-30` | Rentang tanggal |
| `month` / `year` | `9` / `2026` | Filter bulan/tahun |
| `status` | `late` | `on_time`, `late`, `absent` |
| `work_mode` | `wfh` | `wfo`, `wfh`, `wfa` |
| `page`, `per_page` | `1`, `25` | Aktifkan paginasi (maks 200/halaman) |

Tanpa `page`/`per_page`, respons mengirim maksimal 500 data terbaru sekaligus (perilaku lama). Dengan paginasi:

```json
{
  "success": true,
  "message": "Riwayat presensi berhasil dimuat.",
  "data": [ ],
  "meta": { "current_page": 2, "from": 3, "last_page": 131, "per_page": 2, "to": 4, "total": 262 }
}
```

**`GET /api/attendance/summary?month=9&year=2026`**

```json
{
  "success": true,
  "message": "Rekap presensi berhasil dimuat.",
  "data": {
    "year": 2026,
    "month": 9,
    "period": { "start": "2026-09-01", "end": "2026-09-30" },
    "total_present": 20,
    "on_time": 18,
    "late": 2,
    "late_minutes": 37,
    "work_minutes": 9600,
    "by_work_mode": { "wfo": 15, "wfh": 5, "wfa": 0 },
    "approved_leave_days": 2
  }
}
```

Cocok untuk kartu statistik di halaman beranda aplikasi.

---

## 6. Izin & Cuti (Upload Dokumen)

### Ajukan

**`POST /api/leaves`** — `multipart/form-data` bila ada lampiran.

| Field | Tipe | Wajib | Keterangan |
| --- | --- | :---: | --- |
| `leave_type_id` | integer | ✅ | Dari `/api/leave-types` |
| `start_date` | `YYYY-MM-DD` | ✅ | |
| `end_date` | `YYYY-MM-DD` | ✅ | ≥ `start_date` |
| `reason` | string | ✅ | 5–1000 karakter |
| `attachment` | file | — | JPG, PNG, WEBP, PDF — maks **5 MB** |

**Sukses (201)**

```json
{
  "success": true,
  "message": "Pengajuan berhasil dikirim.",
  "data": {
    "id": 11,
    "employee_id": 3,
    "leave_type_id": 2,
    "start_date": "2026-09-10",
    "end_date": "2026-09-11",
    "total_days": 2,
    "reason": "Sakit demam, surat dokter terlampir",
    "status": "pending",
    "status_label": "Menunggu",
    "can_edit": true,
    "can_cancel": true,
    "attachment": {
      "url": "https://host/storage/leave-attachments/2026/09/3-20260901195836.jpg",
      "name": "surat-dokter.jpg",
      "mime": "image/jpeg",
      "size": 184320
    },
    "notes": null,
    "approved_at": null,
    "cancelled_at": null,
    "leave_type": { "id": 2, "name": "Sick Leave", "is_paid": true },
    "employee": { "id": 3, "name": "John Doe" },
    "created_at": "2026-09-01T19:58:36+07:00",
    "updated_at": "2026-09-01T19:58:36+07:00"
  }
}
```

`total_days` dihitung server dengan mengecualikan akhir pekan dan hari libur, jadi jangan hitung di aplikasi.

**Kemungkinan penolakan**

| Status | Pesan |
| :---: | --- |
| 409 | `Anda sudah memiliki pengajuan pada rentang tanggal tersebut.` |
| 422 | `Sisa kuota Annual Leave tidak mencukupi.` — `data` berisi `remaining_days` & `requested_days` |
| 422 | `Rentang tanggal yang dipilih tidak mengandung hari kerja.` |
| 422 | `Lampiran harus berformat JPG, PNG, WEBP, atau PDF.` / `Ukuran lampiran maksimal 5 MB.` |

### Ubah

**`POST /api/leaves/{id}`** — gunakan `POST` (bukan `PUT`) bila mengirim file, karena PHP tidak mem-parse `multipart` pada `PUT`. Field sama seperti pengajuan, semuanya opsional, plus:

- `attachment` — mengganti lampiran lama (file lama dihapus dari storage).
- `remove_attachment=true` — menghapus lampiran tanpa menggantinya.

Hanya bisa diubah oleh pemiliknya dan hanya saat status `pending`.

### Batalkan

**`POST /api/leaves/{id}/cancel`** → status menjadi `cancelled` dan `cancelled_at` terisi.

### Daftar & Detail

- **`GET /api/leaves?status=pending&year=2026&page=1&per_page=25`** — hanya pengajuan milik sendiri. Tanpa `page`/`per_page` mengirim maks 300 data terbaru.
- **`GET /api/leaves/{id}`** — pemilik, atau `admin`/`manager`/`hr`. Selain itu **403**.
- **`GET /api/leave-balance?year=2026`** — sisa kuota per jenis.

### Persetujuan (khusus admin / manager / hr)

- **`POST /api/leaves/{id}/approve`** — kuota otomatis dipotong dalam transaksi terkunci.
- **`POST /api/leaves/{id}/reject`** — wajib mengirim `notes` (alasan penolakan).

Nilai `status`: `pending`, `approved`, `rejected`, `cancelled`. Gunakan `status_label` untuk teks siap tampil.

---

## 7. Ubah Password

**`POST /api/change-password`** (alias lama: `POST /api/api-user/update-password`)

```json
{
  "current_password": "password",
  "password": "Rahasia123",
  "password_confirmation": "Rahasia123"
}
```

Aturan password baru: minimal **8 karakter**, mengandung **huruf** dan **angka**, dan **berbeda** dari password lama.

**Sukses (200)**

```json
{
  "success": true,
  "message": "Password berhasil diubah. Perangkat lain telah dikeluarkan.",
  "data": { "password_changed_at": "2026-09-01T19:59:01+07:00" }
}
```

Perilaku penting: **token perangkat lain dicabut**, token yang sedang dipakai tetap valid — pengguna tidak perlu login ulang di perangkat ini.

**Error (422)** — `errors.current_password` atau `errors.password`:

- `Password lama tidak sesuai.`
- `Password baru harus mengandung angka.`
- `Password baru harus berbeda dari password lama.`
- `Konfirmasi password baru tidak sesuai.`

Endpoint ini dibatasi 10 permintaan per menit.

---

## 8. Profil Pengguna

**`POST /api/api-user/edit`** — `multipart/form-data`

| Field | Wajib | Keterangan |
| --- | :---: | --- |
| `name` | — | |
| `email` | — | Harus unik |
| `phone` | — | |
| `image` | — | JPG/PNG/WEBP, maks 2 MB |

> **Perubahan keamanan:** field `id` pada body **diabaikan**. Endpoint selalu mengubah pemilik token. Foto lama otomatis dihapus dari storage saat diganti.

**`GET /api/api-user/{id}`** hanya bisa diakses oleh pemilik akun atau `admin`/`manager`/`hr`; selain itu **403**.

---

## Aturan Mode Kerja

Mode kerja pegawai diatur admin di menu **Pegawai** (kolom Mode). Nilai ini menentukan apa yang boleh dipilih di aplikasi:

| `users.work_mode` | Mode yang boleh dipakai | Validasi radius |
| --- | --- | --- |
| `wfo` | `wfo` | Wajib di dalam radius kantor |
| `wfh` | `wfo`, `wfh` | Hanya saat memilih `wfo` |
| `wfa` | `wfo`, `wfh`, `wfa` | Hanya saat memilih `wfo` |

Bila admin mematikan **Aktifkan presensi WFH/WFA** di Pengaturan Aplikasi, semua pegawai kembali hanya bisa `wfo`.

Aplikasi sebaiknya membaca `work_mode.allowed` dari `/api/attendance/pre-check` untuk mengisi pilihan mode, bukan menebak sendiri — server tetap memvalidasi ulang.

---

## Kode Status & Penanganan Error

| Kode | Arti | Saran penanganan di aplikasi |
| :---: | --- | --- |
| 200 / 201 | Berhasil | — |
| 401 | Token tidak valid / kedaluwarsa (`Sesi Anda telah berakhir…`) | Hapus token, arahkan ke login |
| 403 | Tidak berhak (mode kerja / bukan pemilik data) | Tampilkan `message` |
| 404 | Data tidak ditemukan | Tampilkan `message` |
| 409 | Konflik keadaan (sudah absen, sudah diproses) | Tampilkan `message`, refresh `pre-check` |
| 422 | Validasi / aturan bisnis gagal | Tampilkan `message`; petakan `errors` ke field form |
| 429 | Terlalu banyak permintaan | Tampilkan `message`, beri jeda |
| 503 | Mode pemeliharaan | Tampilkan halaman pemeliharaan |

Karena `message` sudah berbahasa Indonesia dan aman, penanganan paling sederhana adalah menampilkan `message` apa adanya, lalu memakai `errors` hanya untuk menyorot field form.

---

## Contoh Integrasi Flutter

### Membaca pengaturan saat splash

```dart
final res = await dio.get('/api/app-settings');
final app = res.data['data'];

appName = app['app_name'];
logoUrl = app['logo_url'];
primaryColor = Color(int.parse(app['theme']['primary_color'].replaceFirst('#', '0xff')));

if (app['maintenance']['enabled'] == true) {
  showMaintenance(app['maintenance']['message']);
}
```

### Halaman peta sebelum presensi

```dart
final res = await dio.get('/api/attendance/pre-check', queryParameters: {
  'latitude': position.latitude,
  'longitude': position.longitude,
});
final s = res.data['data'];

// Peta
TileLayer(urlTemplate: s['map']['tile_url']);
for (final loc in s['locations']) {
  markers.add(Marker(point: LatLng(loc['latitude'], loc['longitude'])));
  circles.add(CircleMarker(
    point: LatLng(loc['latitude'], loc['longitude']),
    radius: (loc['radius_meters'] as num).toDouble(),
    useRadiusInMeter: true,
    color: loc['within_radius'] ? Colors.green.withOpacity(.2) : Colors.red.withOpacity(.2),
  ));
}

// Tombol
final label = switch (s['next_action']) {
  'check_in' => 'Absen Masuk',
  'check_out' => 'Absen Pulang',
  _ => 'Presensi Selesai',
};
final enabled = s['next_action'] == 'check_in' ? s['can_check_in'] : s['can_check_out'];
final blockers = List<String>.from(s['blockers']); // tampilkan sebagai alasan
final allowedModes = List<String>.from(s['work_mode']['allowed']);
```

### Absen masuk WFH dengan foto

```dart
final form = FormData.fromMap({
  'latitude': position.latitude,
  'longitude': position.longitude,
  'work_mode': 'wfh',
  'notes': notesController.text,
  'address': address,
  'is_mock_location': position.isMocked,
  'device_info': '$deviceModel / $osVersion',
  'photo': await MultipartFile.fromFile(photo.path, filename: 'selfie.jpg'),
});

try {
  final res = await dio.post('/api/attendance/check-in', data: form);
  showSuccess(res.data['message']);
} on DioException catch (e) {
  final body = e.response?.data;
  showError(body?['message'] ?? 'Gagal melakukan presensi.');

  // Konteks tambahan, mis. jarak ke kantor saat di luar radius
  final distance = body?['data']?['distance_meters'];
}
```

### Upload izin dengan lampiran

```dart
final form = FormData.fromMap({
  'leave_type_id': selectedType.id,
  'start_date': DateFormat('yyyy-MM-dd').format(startDate),
  'end_date': DateFormat('yyyy-MM-dd').format(endDate),
  'reason': reasonController.text,
  if (file != null)
    'attachment': await MultipartFile.fromFile(file.path, filename: file.name),
});

final res = await dio.post('/api/leaves', data: form);
final attachmentUrl = res.data['data']['attachment']?['url'];
```

---

## Pengaturan di Panel Admin

Menu **Pengaturan → Pengaturan Aplikasi** (khusus role `admin`), terdiri dari 4 tab:

| Tab | Isi |
| --- | --- |
| **Identitas** | Nama aplikasi, nama singkat, tagline, logo terang/gelap, favicon, banner login, warna utama & sekunder |
| **Perusahaan** | Nama perusahaan, website, email & telepon bantuan, alamat |
| **Presensi** | Wajib foto, tolak fake GPS, aktifkan WFH/WFA, wajib foto & catatan WFH, toleransi akurasi GPS, tile URL & zoom peta |
| **Versi & Pemeliharaan** | Versi terbaru/minimum Android & iOS, paksa update, mode pemeliharaan + pesannya |

Perubahan langsung terbaca aplikasi lewat `/api/app-settings`, dan nama serta logo panel admin ikut mengikuti pengaturan ini.

Menu lain yang berubah:

- **Lokasi Kantor** — ada kolom **Aktif**. Lokasi non-aktif tidak dipakai lagi untuk validasi radius.
- **Data Absensi** — halaman detail menampilkan foto bukti, catatan aktivitas, alamat, jarak dari kantor, dan indikator fake GPS. Tersedia filter "Terindikasi Fake GPS".
- **Cuti/Izin** — upload lampiran aktif, ada aksi **Batalkan Persetujuan** yang mengembalikan kuota.
- **Pegawai** — mode kerja bisa diubah langsung di tabel dan secara massal.

---

## Perubahan Database

Kolom dan index yang ditambahkan fitur ini:

| Tabel | Isi |
| --- | --- |
| `app_settings` *(baru)* | Pengaturan aplikasi, satu baris: branding, aturan presensi, konfigurasi peta, versi minimum, maintenance |
| `attendances` | `photo_in/out`, `notes_in/out`, `address_in/out`, `distance_in/out_meters`, `is_mock_location`, `work_duration_minutes`, `device_info`; **unique index `(user_id, date)`**; index `(date, status)` dan `(company_id, date)` |
| `leaves` | `attachment_name`, `attachment_mime`, `attachment_size`, `cancelled_at`; enum `status` ditambah `cancelled`; index `(employee_id, status)` dan `(start_date, end_date)` |
| `companies` | `is_active`, `logo_path`, index `is_active` |
| `users` | `password_changed_at` |

Sejak versi 2.1 seluruh migrasi sudah di-squash menjadi 15 file baseline — lihat [Struktur Migrasi](../README.md#-struktur-migrasi) di README.

Cara menerapkan:

```bash
# Instalasi baru
php artisan migrate --seed

# Instalasi yang sudah berjalan (schema sudah ada, hanya pencatatan yang disamakan)
php artisan db:baseline-migrations --dry-run   # tinjau dulu
php artisan db:baseline-migrations

php artisan db:seed --class=AppSettingSeeder   # opsional, mengisi nilai default
php artisan storage:link                        # bila belum pernah dijalankan
php artisan optimize:clear
```

> `storage:link` wajib agar `photo_url`, `logo_url`, dan `attachment.url` bisa diakses aplikasi.

---

## Catatan Upgrade Aplikasi Lama

Build aplikasi yang sekarang **tetap berjalan** tanpa perubahan. Yang perlu diperhatikan saat memperbarui aplikasi:

1. **Ambil `message` dari respons error.** Format error lama (`{"message": "..."}`) masih ada di dalam envelope baru, jadi pembacaan `response['message']` tetap bekerja.
2. **`/api/api-user/edit` tidak lagi memakai `id`.** Boleh tetap dikirim, tapi diabaikan.
3. **`/api/user` sekarang identik dengan `/api/me`.** Field `position` berubah dari teks menjadi objek `{id, name}` — gunakan `data.user.position` bila butuh teks jabatan lama.
4. **`/api/api-attendances` tetap mengirim array penuh** selama `page`/`per_page` tidak dikirim. Bila mau paginasi, baca `meta`.
5. **Presensi kini bisa ditolak dengan 409** saat pegawai sedang cuti disetujui atau sudah absen — tangani agar tidak dianggap error jaringan.
6. **Jangan mengandalkan jam perangkat.** Gunakan `server_time` dari `/api/attendance/pre-check`.
