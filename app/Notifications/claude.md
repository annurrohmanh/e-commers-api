# Task: Refactor Complete Profile & Register Flow

## Context

Gunakan source code dan desain UI yang sudah saya upload sebagai referensi utama.

**Prioritas referensi:**

1. UI Design yang saya upload.
2. Source code `Register` yang sudah ada.
3. Source code `Complete Profile` yang sudah ada.
4. Flow pada `Reset Password` (untuk handling success/error state).

Pastikan hasil akhir tetap mengikuti struktur project dan coding style yang sudah ada.

---

# 1. Complete Profile

## UI

Refactor halaman **Complete Profile** agar tampilannya mengikuti desain UI yang saya upload.

* Gunakan komponen, spacing, typography, dan styling yang konsisten dengan halaman Register.
* Jika terdapat reusable component pada Register, gunakan kembali component tersebut.

---

## Form Changes

### Hapus field berikut

* Line Address 2 (Optional)

### Ganti dengan field baru

* Regency / Kabupaten-Kota

---

## Regency Data Source

Field **Regency** harus menggunakan data wilayah Indonesia.

Usahakan menggunakan API gratis/open source, misalnya:

* https://wilayah.id
* https://wilayah-id-restapi.vercel.app/docs
* atau API free lain yang lebih baik jika ada.

Flow yang diharapkan:

1. User memilih Province.
2. Fetch daftar Regency berdasarkan Province.
3. Tampilkan Regency dalam Select/Dropdown.
4. Loading dan error state harus ditangani dengan baik.
5. Jangan hardcode data wilayah.

---

# 2. Register Flow

Perbaiki flow setelah user berhasil melakukan Register.

Saat ini:
Register → selesai.

Flow baru yang diinginkan:

Register
↓
Submit Register
↓
Tampilkan halaman "Check Your Email"

Jangan langsung redirect ke Login.

---

# 3. Check Your Email Page

Buat halaman baru di dalam flow Register.

Halaman berisi:

* ilustrasi/icon sesuai design

* title:
  "Check Your Email"

* description:
  Informasikan bahwa email verifikasi telah dikirim.

* tombol:

  * Open Email (opsional)
  * Back to Login (opsional)

Halaman ini hanya sebagai informasi setelah register berhasil.

---

# 4. Email Verification Flow

Link yang dikirim melalui email memiliki format:

```text
localhost:3000/verify-email/{token}
```

---

## IMPORTANT

Saat halaman tersebut dibuka:

**Jangan langsung memanggil API verify.**

Token hanya dibaca dari URL.

Halaman harus menampilkan informasi dan tombol:

```
Verify Email
```

Baru setelah user menekan tombol tersebut:

Call API

```text
POST /verify-email/{token}
```

atau sesuai endpoint backend yang sudah tersedia.

---

# 5. Verify Email Result

## Success

Jika verify berhasil:

Tampilkan halaman success.

Gunakan tampilan dan behavior yang mengikuti flow **Reset Password Success**.

Isi halaman:

* icon success
* title

```
Your email was verified
```

* description sesuai kebutuhan

Setelah itu:

* auto login (menggunakan flow yang sudah ada pada Complete Profile)
* redirect ke Login atau halaman sesuai flow existing project.

Gunakan implementasi auto-login yang sama seperti pada Complete Profile.

---

## Invalid / Expired Token

Jika saat user menekan tombol Verify:

* token expired
* token sudah pernah digunakan
* token invalid

Jangan redirect.

Tampilkan halaman error seperti referensi Reset Password Error.

Isi halaman:

```
Your verification link has expired
```

atau

```
This verification link has already been used.
```

sertakan tombol:

```
Back to Login
```

atau tombol lain yang sesuai.

---

# 6. Flow Summary

Flow akhir yang diharapkan:

```text
Register
        │
        ▼
Submit Register
        │
        ▼
Check Your Email
        │
        ▼
User membuka link email

/verify-email/{token}

        │
        ▼
Halaman Verify Email
(TIDAK call API)
        │
Klik tombol Verify
        │
        ▼
Call Verify API
        │
        ├───────────────┐
        │               │
        ▼               ▼
Success            Expired / Used / Invalid
        │               │
        ▼               ▼
Your Email      Your Link Has Expired
Was Verified
        │
Auto Login
        │
        ▼
Redirect Login
```

---

# 7. UI Consistency

Pastikan seluruh halaman berikut memiliki konsistensi UI:

* Register
* Complete Profile
* Check Your Email
* Verify Email
* Verify Success
* Verify Error

Gunakan:

* typography
* spacing
* button
* form component
* loading state
* error state
* animation/transitions (jika sudah ada)

agar konsisten dengan design system project.

---

# 8. Code Quality Requirements

* Reuse existing components sebisa mungkin.
* Hindari duplicate code.
* Ikuti struktur folder project yang sudah ada.
* Gunakan TypeScript type yang benar.
* Tambahkan loading state.
* Tambahkan empty state bila diperlukan.
* Tambahkan proper error handling.
* Jangan mengubah flow lain yang tidak berkaitan.
* Jangan merusak existing validation.

---

# Deliverables

Implementasi harus mencakup:

* Refactor Complete Profile sesuai design.
* Hapus field Line Address 2.
* Tambahkan field Regency dengan fetch dari API wilayah Indonesia.
* Refactor Register flow.
* Tambahkan halaman Check Your Email.
* Tambahkan halaman Verify Email (tanpa auto call API).
* Call API hanya saat tombol Verify ditekan.
* Tambahkan Success Page.
* Tambahkan Expired/Invalid Page.
* Gunakan referensi UI dan behavior dari Reset Password untuk halaman Success dan Error.
* Implementasikan auto-login sesuai flow yang sudah ada pada Complete Profile.
* Pastikan seluruh flow berjalan end-to-end sesuai spesifikasi di atas.
