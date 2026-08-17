# Architecture

## Ringkasan Stack

| Komponen | Pilihan | Alasan Singkat |
|---|---|---|
| Backend Framework | Laravel 12 (PHP) | Sudah dikuasai; matang untuk business logic kompleks (Policy, Job Scheduling, Eloquent relations) |
| Frontend Approach | Inertia.js + Vue 3 | Belajar Vue asli tanpa membangun REST API terpisah — jalan tengah antara Livewire (kurang paparan JS modern) dan full SPA+API (overkill untuk kebutuhan sistem ini) |
| Styling | Tailwind CSS | Cepat & konsisten untuk dashboard admin |
| Database | PostgreSQL | ENUM & CHECK constraint lebih ketat untuk status WO; JSONB lebih matang untuk checklist dinamis; konsisten dengan pengalaman Supabase sebelumnya |
| Queue/Job | Laravel Queue (database driver) | Cukup untuk skala portfolio, Redis belum perlu |
| Scheduler | Laravel Task Scheduling (cron) | Untuk cek interval PM harian |
| Authentication | Laravel Breeze/Fortify | Standar, aman, banyak dipakai industri |
| Authorization | Laravel Policy + Gate | Implementasi RBAC di level backend |
| Storage | Local (dev), Supabase Storage/S3-compatible (production) | Konsisten dengan stack yang sudah dikenal |
| Testing | Pest | Naik jadi Must Have — menyatu di tiap milestone (lihat ADR-007) |
| CI/CD | GitHub Actions | Should Have, dikerjakan menjelang akhir |
| Version Control | Git + GitHub, Conventional Commits | — |

## Yang Sengaja Tidak Ditambahkan (dan Kenapa)

- **Docker** — kompleksitas deployment tidak sepadan manfaat di skala solo portfolio. Kandidat kuat untuk fase lanjutan setelah core selesai.
- **Redis** — belum perlu di skala data portfolio.
- **Microservices/API Gateway** — jelas overkill untuk skala sistem ini.

## Prinsip

> "Architecture should solve complexity, not create complexity." Setiap teknologi harus menjawab masalah konkret, bukan ditambahkan agar terlihat canggih.

Lihat ADR-005, ADR-006, ADR-007 untuk detail argumentasi tiap keputusan besar.
