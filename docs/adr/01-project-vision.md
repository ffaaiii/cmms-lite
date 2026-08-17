# Project Vision — CMMS-Lite untuk PLTP Darajat (Portfolio Project)

> **Catatan penting**: Ini adalah project portfolio/simulasi pribadi, BUKAN sistem resmi milik PLTP Darajat, Star Energy, PGE, atau PLN. Seluruh data operasional bersifat simulasi/dummy untuk kebutuhan demonstrasi kemampuan software engineering.

## Konteks Industri (Fakta vs Asumsi)

**Fakta publik**: PLTP Darajat adalah pembangkit listrik tenaga panas bumi (geothermal) di Kecamatan Pasirwangi, Kabupaten Garut, Jawa Barat, dengan kapasitas terpasang sekitar 270 MW. Dioperasikan oleh Star Energy Geothermal Darajat II (SEGDII) berdasarkan Kontrak Operasi Bersama dengan PT Pertamina Geothermal Energy (PGE), menyuplai uap panas bumi untuk pembangkit 55 MW yang dioperasikan PLN, serta menyediakan uap dan listrik untuk jaringan JAMALI.

**ASUMSI**: Proses bisnis internal spesifik (SOP, struktur organisasi, alur kerja detail) tidak diketahui publik dan tidak dikarang — seluruh business process dalam dokumen ini adalah simulasi berbasis praktik umum industri maintenance/CMMS.

## Problem Statement

Pemeliharaan aset pada fasilitas pembangkit listrik (sumur produksi, turbin, cooling tower, jaringan pipa uap) umumnya masih mengandalkan pencatatan manual atau spreadsheet yang tersebar, sehingga sulit melacak riwayat maintenance, menjadwalkan preventive maintenance secara konsisten, dan mengukur reliability aset secara terstruktur. Tidak adanya sistem terpusat membuat proses corrective maintenance sering reaktif, bukan preventif.

## Background

**ASUMSI**: Banyak fasilitas industri skala menengah masih bergantung pada proses manual/semi-digital sebelum mengadopsi CMMS penuh (seperti IBM Maximo, SAP PM). Project ini mensimulasikan skenario tersebut dalam skala satu unit pembangkit.

## Target User

- **Admin** — kelola master data (aset, user, kategori)
- **Supervisor/Engineer Maintenance** — approve work order, review laporan, pantau dashboard reliability
- **Teknisi Lapangan** — eksekusi work order, isi checklist inspeksi, laporkan temuan/kerusakan
- **Plant Manager** — lihat dashboard ringkasan, read-only

## Goal

Membangun sistem manajemen maintenance terpusat (CMMS-Lite) yang mengelola siklus hidup work order (dari penjadwalan hingga penyelesaian), terhubung dengan histori aset dan penggunaan spare part dasar, serta menyediakan insight reliability sederhana (MTBF/MTTR).

## Objectives

1. Mendigitalkan pencatatan aset kritis dan histori maintenance-nya.
2. Mengotomatiskan penjadwalan preventive maintenance berbasis interval waktu.
3. Menyediakan alur kerja work order yang jelas: create → assign → execute → review/approve → close.
4. Menyediakan checklist inspeksi digital sederhana yang dapat memicu usulan work order.
5. Menampilkan dashboard metrik dasar (downtime, MTBF, MTTR, compliance rate).

## Scope

- Manajemen aset (registry, kategori, kondisi)
- Preventive & corrective work order (lifecycle penuh + state machine)
- Checklist inspeksi ringan yang terhubung ke work order (wajib approval Supervisor)
- Pencatatan part yang digunakan per work order (bukan full warehouse system)
- Dashboard reliability dasar (MTBF/MTTR)
- Role-based access control (Admin, Supervisor, Teknisi, Plant Manager)
- Notifikasi in-app untuk jadwal maintenance jatuh tempo

## Out of Scope

- Integrasi real-time SCADA/IoT/sensor (disimulasikan sebagai input manual/periodic)
- Modul procurement/pembelian spare part penuh
- Payroll/HR penuh (shift scheduling)
- Multi-plant/multi-lokasi (fokus 1 unit/pembangkit saja)
- Native mobile app
- PWA (ditunda sebagai future development — lihat ADR-002)
- Notifikasi email (ditunda — in-app saja untuk MVP)

## Expected Outcome

Sebuah aplikasi web fungsional yang mendemonstrasikan pemahaman end-to-end: dari analisis masalah bisnis, desain sistem, hingga implementasi — didukung dokumentasi teknis (ERD, ADR, dsb.) yang bisa dipresentasikan sebagai portfolio serius.

## Success Criteria

- Seluruh alur utama (create asset → schedule PM → generate work order → assign → execute → close) berjalan tanpa bug kritis.
- RBAC berfungsi sesuai matrix yang didefinisikan.
- Dashboard menampilkan metrik reliability yang dihitung benar secara logika.
- Dokumentasi GitHub lengkap dan bisa dijalankan orang lain dari awal.
- Bisa dijelaskan end-to-end secara lisan tanpa membaca kode line-by-line.

## Portfolio Value

Menunjukkan kemampuan solo development pada domain khusus (industrial maintenance/CMMS) — diferensiasi kuat dibanding project generik (e-commerce/sistem informasi sekolah) saat melamar ke perusahaan energi.
