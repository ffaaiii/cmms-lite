# Requirement Specification

Prioritas: **M** = Must Have, **S** = Should Have, **C** = Could Have

## Functional Requirements

### Modul: Manajemen Aset

| ID | Nama | Deskripsi | Actor | Priority |
|---|---|---|---|---|
| FR-001 | Registrasi Aset | Admin menambah aset kritis (turbin, sumur, pipa, cooling tower) dengan atribut: nama, kategori, lokasi, tanggal instalasi, kondisi awal | Admin | M |
| FR-002 | Kelola Data Aset | Admin mengubah/menonaktifkan data aset (soft delete, bukan hapus permanen) | Admin | M |
| FR-003 | Lihat Riwayat Aset | Supervisor/Teknisi melihat histori maintenance lengkap per aset | Supervisor, Teknisi | M |
| FR-004 | Set Interval Maintenance | Admin/Supervisor menetapkan interval PM per aset | Admin, Supervisor | M |

**Acceptance Criteria (contoh, pola serupa berlaku untuk FR lain)**: Aset baru tersimpan dengan status default "Aktif"; validasi nama & kategori wajib diisi; muncul di listing segera setelah disimpan.

### Modul: Preventive Maintenance Scheduling

| ID | Nama | Deskripsi | Actor | Priority |
|---|---|---|---|---|
| FR-005 | Generate Jadwal PM Otomatis | Sistem otomatis membuat rencana PM berikutnya berdasarkan interval aset setelah PM terakhir selesai | Sistem | M |
| FR-006 | Notifikasi Jadwal Jatuh Tempo | Sistem mengirim notifikasi in-app ke Supervisor saat PM mendekati/jatuh tempo | Sistem → Supervisor | S |
| FR-007 | Lihat Kalender Maintenance | Supervisor melihat jadwal PM dalam tampilan kalender/list | Supervisor | S |

### Modul: Work Order Lifecycle

| ID | Nama | Deskripsi | Actor | Priority |
|---|---|---|---|---|
| FR-008 | Buat Work Order (Manual/Corrective) | Supervisor membuat WO di luar jadwal PM | Supervisor | M |
| FR-009 | Assign Work Order | Supervisor menugaskan WO ke Teknisi tertentu | Supervisor | M |
| FR-010 | Eksekusi Work Order | Teknisi mengubah status WO (Assigned → In Progress → Completed), mengisi catatan | Teknisi | M |
| FR-011 | Catat Part Digunakan | Teknisi mencatat spare part yang dipakai (nama, jumlah) | Teknisi | S |
| FR-012 | Review & Approve Work Order | Supervisor review WO "Completed" lalu approve (Closed) atau reject (kembali ke In Progress + catatan) | Supervisor | M |
| FR-013 | Lihat Status Semua Work Order | Supervisor melihat listing WO dengan filter status/prioritas/aset | Supervisor | M |

### Modul: Inspection Checklist

| ID | Nama | Deskripsi | Actor | Priority |
|---|---|---|---|---|
| FR-014 | Isi Checklist Inspeksi | Teknisi mengisi checklist per aset (baik/perlu perhatian/rusak + catatan) | Teknisi | S |
| FR-015 | Konfirmasi Work Order dari Temuan | Supervisor mengkonfirmasi usulan WO dari checklist (Teknisi tidak bisa create WO sendiri — lihat ADR-004) | Supervisor | S |

### Modul: Dashboard & Reporting

| ID | Nama | Deskripsi | Actor | Priority |
|---|---|---|---|---|
| FR-016 | Dashboard Reliability | Sistem menghitung & menampilkan metrik: downtime, MTBF, MTTR per periode | Supervisor, Plant Manager | S |
| FR-017 | Dashboard Ringkasan Eksekutif | Plant Manager melihat ringkasan angka tanpa akses detail operasional | Plant Manager | C |
| FR-018 | Export Laporan | Supervisor export laporan WO/aset ke Excel/PDF | Supervisor | C |

### Modul: User & Access Management

| ID | Nama | Deskripsi | Actor | Priority |
|---|---|---|---|---|
| FR-019 | Kelola User & Role | Admin membuat/mengubah akun user beserta role | Admin | M |
| FR-020 | Login & Autentikasi | Semua user login dengan email/password | Semua | M |

## Non-Functional Requirements

| NFR | Deskripsi | Alasan Relevansi |
|---|---|---|
| Security | Password hashing (bcrypt/argon2), proteksi CSRF, role-based middleware | Data operasional aset kritis — akses tidak sah bisa berarti manipulasi WO |
| Data Integrity | Setiap perubahan status WO tercatat dengan timestamp & actor; tidak ada hard delete pada data historis | Industri energi butuh jejak audit untuk investigasi insiden |
| Auditability | Log siapa mengubah apa dan kapan (activity log minimal untuk aksi kritis) | Mendukung compliance & investigasi |
| Availability | Sistem dapat diakses kapan saja teknisi butuh (uptime standar hosting, bukan SLA produksi) | ASUMSI diturunkan — ini portfolio, bukan sistem produksi nyata |
| Usability | Interface Teknisi minim ketikan, mobile-responsive | Dari pain point Persona Deni — akses lapangan pakai HP |
| Performance | Listing WO/aset tetap responsif walau data banyak (pagination, index tepat) | Data maintenance terus bertambah |
| Scalability | Struktur database & kode memungkinkan penambahan modul di masa depan tanpa refactor besar | Modul inventory sengaja ringan sekarang, arsitektur harus terbuka untuk pengembangan |
| Maintainability | Kode mengikuti konvensi Laravel standar (Action pattern untuk business logic kompleks) | Perlu dijelaskan saat interview — struktur rapi = presentasi lebih percaya diri |
