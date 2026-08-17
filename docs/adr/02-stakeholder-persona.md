# Stakeholder Analysis & User Persona

## Stakeholder Analysis

| Stakeholder | Kepentingan | Level Pengaruh ke Sistem |
|---|---|---|
| Admin/System Owner | Sistem berjalan stabil, data master terkontrol | Tinggi |
| Supervisor/Engineer Maintenance | Work order terkelola rapi, bisa ambil keputusan approve/reject cepat | Tinggi |
| Teknisi Lapangan | Instruksi kerja jelas, mudah lapor progress/temuan | Tinggi (user harian terbanyak) |
| Plant Manager | Insight cepat soal reliability tanpa perlu detail teknis | Sedang |
| *(Di luar scope)* Vendor Spare Part | Tidak berinteraksi langsung dengan sistem | Rendah/Out of Scope |

## User Persona

### Persona 1: Deni — Teknisi Lapangan
- **Usia/latar**: 28 tahun, D3 Teknik Mesin, 3 tahun pengalaman
- **Tugas harian**: Menerima work order, ke lokasi (sumur/turbin/pipa), eksekusi maintenance, isi checklist, lapor selesai
- **Pain point (ASUMSI)**: Sering bingung part apa yang dipakai sebelumnya untuk aset yang sama; laporan manual gampang hilang/tidak konsisten formatnya
- **Kebutuhan dari sistem**: Interface simpel, cepat diisi dari HP, minim ketikan (dropdown/checkbox)

### Persona 2: Rina — Supervisor Maintenance
- **Usia/latar**: 35 tahun, S1 Teknik, 8 tahun pengalaman
- **Tugas harian**: Assign work order ke teknisi, review hasil kerja, approve/reject, pantau jadwal PM
- **Pain point (ASUMSI)**: Sulit tahu status semua WO tanpa tanya satu-satu; tidak ada data historis untuk evaluasi performa aset
- **Kebutuhan dari sistem**: Dashboard status real-time, filter prioritas/status, notifikasi jadwal jatuh tempo

### Persona 3: Pak Hendra — Plant Manager (view-only)
- **Usia/latar**: 45 tahun, lama di industri energi
- **Tugas**: Melihat ringkasan performa maintenance berkala, tidak input data
- **Pain point (ASUMSI)**: Laporan manual butuh waktu lama disusun tim sebelum sampai ke dia
- **Kebutuhan dari sistem**: Dashboard ringkas, angka reliability (MTBF/MTTR/downtime) tanpa detail teknis

*(Admin tidak dibuatkan persona naratif — perannya administratif, requirement cukup fungsional standar)*

*(Staff Gudang sengaja tidak dijadikan role terpisah — lihat ADR-003, modul inventory hanya ringan di MVP)*

## Rantai Justifikasi

Persona → Pain Point → Requirement → Fitur

Setiap fitur yang dibangun harus bisa ditarik garis lurus balik ke salah satu persona di atas. Fitur yang tidak bisa dijelaskan asalnya dari persona/pain point adalah indikasi overengineering.
