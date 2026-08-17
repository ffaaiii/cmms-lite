# Business Process

## 1. Main Workflow: Preventive Maintenance (PM) Terjadwal

```mermaid
flowchart TD
    A[Sistem: Cek interval PM setiap aset] --> B{Interval jatuh tempo?}
    B -- Tidak --> A
    B -- Ya --> C[Sistem: Generate rencana PM]
    C --> D[Sistem: Kirim notifikasi ke Supervisor]
    D --> E[Supervisor: Review rencana PM]
    E --> F[Supervisor: Buat Work Order dari rencana PM]
    F --> G[Supervisor: Assign ke Teknisi]
    G --> H[Teknisi: Terima notifikasi WO baru]
    H --> I[Teknisi: Eksekusi maintenance di lapangan]
    I --> J[Teknisi: Isi catatan + part digunakan]
    J --> K[Teknisi: Ubah status ke Completed]
    K --> L[Supervisor: Review hasil kerja]
    L --> M{Hasil sesuai?}
    M -- Ya --> N[Supervisor: Approve - Status Closed]
    M -- Tidak --> O[Supervisor: Reject dengan catatan]
    O --> I
    N --> P[Sistem: Update riwayat aset + reset interval PM berikutnya]
```

## 2. Alternative Flow: Corrective Maintenance (Kerusakan Mendadak)

```mermaid
flowchart TD
    A[Teknisi/Supervisor: Temukan kerusakan di luar jadwal] --> B[Supervisor: Buat WO manual - tipe Corrective]
    B --> C[Supervisor: Set prioritas - Urgent/Normal]
    C --> D[Supervisor: Assign ke Teknisi]
    D --> E[Teknisi: Eksekusi perbaikan]
    E --> F[Teknisi: Isi catatan + part digunakan]
    F --> G[Teknisi: Ubah status Completed]
    G --> H[Supervisor: Review & Approve/Reject]
    H --> I[Sistem: Update riwayat aset]
```

## 3. Alternative Flow: Inspeksi → Work Order (revisi final)

Semua usulan WO dari checklist inspeksi (baik minor "perlu perhatian" maupun major "rusak") wajib dikonfirmasi Supervisor — Teknisi tidak bisa membuat WO sendiri (lihat ADR-004).

```mermaid
flowchart TD
    A[Teknisi: Isi checklist inspeksi rutin per aset] --> B{Kondisi aset?}
    B -- Baik --> C[Sistem: Simpan checklist, selesai]
    B -- Perlu Perhatian / Rusak --> D[Sistem: Simpan checklist + usulan WO]
    D --> E[Supervisor: Review usulan]
    E --> F{Supervisor setujui?}
    F -- Ya --> G[Sistem: Generate WO Corrective]
    G --> H[Supervisor: Assign ke Teknisi - masuk Alternative Flow Corrective]
    F -- Tidak --> C
```

## 4. Exception Flow: Work Order Ditolak Berulang (Eskalasi)

Threshold eskalasi = 2x reject berturut-turut. Eskalasi ditangani sesama Supervisor (reassign), Plant Manager tidak terlibat proses operasional (lihat ADR-004).

```mermaid
flowchart TD
    A[Supervisor: Reject WO ke-1] --> B[Teknisi: Perbaiki, submit ulang]
    B --> C[Supervisor: Reject WO ke-2]
    C --> D[Sistem: Flag WO Perlu Eskalasi]
    D --> E[Supervisor: Reassign ke Teknisi lain]
```

## 5. Approval Flow: Ringkasan Titik Approval

| Titik Approval | Siapa | Aksi |
|---|---|---|
| Rencana PM → Work Order | Supervisor | Approve rencana jadi WO resmi |
| WO Completed → Closed | Supervisor | Approve/Reject hasil kerja Teknisi |
| Temuan Inspeksi → WO baru | Supervisor | Konfirmasi pembuatan WO dari temuan |
| WO Eskalasi (reject 2x) | Sesama Supervisor | Reassign ke Teknisi lain |
