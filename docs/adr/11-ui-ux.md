# UI/UX

## Sitemap (berdasarkan role)

```
/login
/dashboard                     → redirect otomatis sesuai role

# Admin
/admin/users                   → kelola user & role
/admin/assets                  → kelola aset (CRUD penuh)

# Supervisor
/supervisor/dashboard          → ringkasan WO, jadwal PM, notifikasi
/supervisor/assets             → lihat & update kondisi aset
/supervisor/work-orders        → listing, filter, assign, approve/reject
/supervisor/work-orders/create → buat WO manual (corrective)
/supervisor/checklists         → review usulan dari checklist inspeksi

# Teknisi
/technician/tasks               → daftar WO miliknya
/technician/tasks/{id}          → detail WO, update status, catat part
/technician/checklists/create   → isi checklist inspeksi

# Plant Manager
/executive/dashboard            → ringkasan reliability, read-only
```

## Navigation per Role

Navigasi tidak seragam — tiap role punya menu sesuai job desk:

- **Teknisi**: Sidebar minimal — "Tugas Saya", "Isi Checklist", "Riwayat". Fokus mobile-friendly.
- **Supervisor**: Sidebar lengkap — "Dashboard", "Aset", "Work Order", "Checklist Review", "Notifikasi".
- **Admin**: Sidebar terpisah — "Kelola User", "Kelola Aset".
- **Plant Manager**: Tidak ada sidebar kompleks — 1 halaman dashboard dengan filter tanggal/periode.

## Key Screens (Konsep)

### 1. Dashboard Supervisor
- Stat cards: Total WO Open, WO Urgent, PM Jatuh Tempo Minggu Ini, Compliance Rate Inspeksi
- List/table WO terbaru dengan filter cepat (status, prioritas, aset)
- Empty state: pesan + tombol "Buat Work Order Pertama" jika belum ada data

### 2. List Work Order (Supervisor)
- Table: Aset, Tipe (badge), Prioritas, Teknisi, Status (badge warna), Tanggal
- Filter panel: status, prioritas, rentang tanggal, aset
- Search: nama aset/deskripsi

### 3. Detail & Eksekusi Work Order (Teknisi)
- Header: info aset (nama, lokasi, kategori)
- Instruksi/deskripsi kerja
- Tombol aksi besar sesuai status (Assigned → "Mulai Kerjakan"; In Progress → form catatan + part + "Tandai Selesai")
- Loading state saat submit (tombol disable + spinner)

### 4. Checklist Inspeksi (Teknisi, mobile-first)
- Pilih aset (dropdown/search)
- 3 tombol besar kondisi: Baik / Perlu Perhatian / Rusak
- Field catatan wajib jika "Perlu Perhatian"/"Rusak"
- Konfirmasi submit: "Checklist terkirim, menunggu review Supervisor"

## State yang Wajib Dipikirkan

| State | Contoh |
|---|---|
| Empty state | Belum ada WO/aset — ilustrasi ringan + CTA |
| Loading state | Submit form/fetch data — skeleton/spinner, tombol disable |
| Error state | Validasi gagal — pesan dekat field terkait |
| Permission state | Akses di luar role → halaman 403 yang jelas |

## Tema Visual: Industrial-Technical (lihat ADR-008)

| Elemen | Pilihan |
|---|---|
| Background utama | Abu-abu gelap netral (~#1a1d21–#22262b) |
| Surface/card | Abu-abu sedikit lebih terang dari background |
| Aksen warna | Amber/orange (prioritas/warning), Teal/cyan (normal/selesai) |
| Font | Sans-serif tegas (Inter/Space Grotesk) untuk heading, monospace (JetBrains Mono) untuk data numerik/ID/kode aset |
| Badge status WO | Draft (abu), Assigned (biru), In Progress (amber), Completed (teal), Closed (hijau gelap), Rejected (merah) |

Konsistensi tema di semua role — tidak berubah-ubah per halaman.
