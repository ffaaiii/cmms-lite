# Development Roadmap

Testing (Pest) menyatu di tiap milestone (ADR-007), bukan ditumpuk di akhir. Milestone 3 boleh dipecah lebih halus di dalamnya jika terasa berat saat dikerjakan.

## Milestone 0: Foundation & Setup
- **Goal**: Project bisa jalan lokal, struktur dasar siap
- **Task**: Install Laravel 12, setup PostgreSQL, install Inertia+Vue+Tailwind, setup `.env`, buat repo GitHub, susun `/docs/adr/`
- **Output**: Repo GitHub dengan skeleton project + dokumentasi
- **Definition of Done**: `php artisan serve` jalan, halaman welcome Inertia+Vue tampil tanpa error, commit awal ter-push
- **Skill**: Setup Laravel+Inertia+Vue, PostgreSQL connection

## Milestone 1: Authentication & Authorization
- **Goal**: Login berjalan, RBAC terimplementasi di backend
- **Task**: Migration roles & users, seeder role default, Laravel Breeze (adaptasi Inertia), 4 Policy, middleware redirect sesuai role
- **Output**: Login + redirect sesuai role; akses tanpa izin dapat 403
- **Definition of Done**: Test Pest login sukses/gagal + akses ditolak sesuai RBAC matrix
- **Skill**: Laravel Policy & Gate, testing autentikasi Pest

## Milestone 2: Asset Management
- **Goal**: CRUD aset penuh sesuai RBAC
- **Task**: Migration assets, Controller+Request+Policy, halaman Vue (Index, Create, Edit, Show + riwayat)
- **Output**: Admin kelola aset, Supervisor/Teknisi lihat
- **Definition of Done**: Test Pest CRUD + validasi RBAC; soft delete berfungsi
- **Skill**: Eloquent soft delete, Form Request validation, Inertia form handling

## Milestone 3: Work Order Lifecycle
- **Goal**: Siklus hidup WO penuh: create → assign → execute → approve/reject, state machine & eskalasi
- **Task**: Migration work_orders, work_order_parts, work_order_logs; semua Action class; halaman Vue List+Detail+Create
- **Output**: Alur WO penuh + eskalasi otomatis setelah 2x reject
- **Definition of Done**: Test Pest transisi status tervalidasi, test eskalasi, test ownership
- **Skill**: State machine pattern, Action class pattern, testing business logic kompleks
- **Catatan**: Milestone paling berat — boleh dipecah jadi 3a (CRUD dasar) dan 3b (state machine + eskalasi) jika perlu

## Milestone 4: Preventive Maintenance Scheduling
- **Goal**: Generate rencana PM & notifikasi jatuh tempo otomatis
- **Task**: GeneratePreventiveScheduleAction, Console Command, daftarkan di Scheduler, Notification class
- **Output**: Command otomatis cek interval PM & generate notifikasi
- **Definition of Done**: Test Pest command menghasilkan notifikasi tepat sesuai kondisi jatuh tempo
- **Skill**: Laravel Task Scheduling, Console Command, Notification system

## Milestone 5: Inspection Checklist
- **Goal**: Teknisi isi checklist, Supervisor konfirmasi jadi WO
- **Task**: Migration inspection_checklists, Controller+Policy, GenerateWorkOrderFromChecklistAction, halaman Vue mobile-friendly
- **Output**: Alur penuh checklist → review Supervisor → jadi WO
- **Definition of Done**: Test Pest: checklist "rusak" hasilkan usulan WO, hanya Supervisor bisa confirm
- **Skill**: Relasi optional, UI mobile-first Tailwind

## Milestone 6: Dashboard & Reporting
- **Goal**: Dashboard reliability (MTBF/MTTR) dan executive dashboard
- **Task**: Query aggregasi metrik, halaman Vue Dashboard Supervisor + Executive, export Excel/PDF (Could Have)
- **Output**: Supervisor & Plant Manager lihat insight reliability
- **Definition of Done**: Test Pest kalkulasi MTBF/MTTR benar terhadap data dummy
- **Skill**: Query aggregation, perhitungan metrik reliability, library export

## Milestone 7: Polish, Notification Refinement & CI/CD
- **Goal**: Sistem terasa matang — notifikasi lengkap, UI konsisten, testing otomatis
- **Task**: Lengkapi semua Notification, review UI konsisten tema, setup GitHub Actions
- **Output**: Sistem lengkap dengan CI berjalan
- **Definition of Done**: Semua test lulus di CI
- **Skill**: GitHub Actions basics, CI/CD workflow

## Milestone 8: Deployment
- **Goal**: Aplikasi live dan bisa diakses publik
- **Task**: Setup environment production, migrate database, deploy (detail di dokumen Deployment)
- **Output**: URL live untuk demo
- **Definition of Done**: Semua fitur utama berfungsi di production
- **Skill**: Deployment Laravel+Inertia, environment config

## Milestone 9: Documentation & Portfolio Packaging
- **Goal**: README lengkap, siap dipresentasikan
- **Task**: Detail di dokumen Documentation & Portfolio
- **Output**: Repo siap dilihat HRD/IT, CV bullet points siap
- **Definition of Done**: Orang lain bisa clone & jalankan dari README tanpa bertanya
- **Skill**: Technical writing

## Estimasi Kasar

| Milestone | Fokus | Estimasi |
|---|---|---|
| 0 | Setup | 3-5 hari |
| 1 | Auth & RBAC | 1 minggu |
| 2 | Asset Management | 1 minggu |
| 3 | Work Order Lifecycle | 2-3 minggu |
| 4 | PM Scheduling | 1 minggu |
| 5 | Inspection Checklist | 1 minggu |
| 6 | Dashboard & Reporting | 1-2 minggu |
| 7 | Polish & CI/CD | 1 minggu |
| 8 | Deployment | 3-5 hari |
| 9 | Documentation | 3-5 hari |

**Total kasar: ~10-13 minggu**
