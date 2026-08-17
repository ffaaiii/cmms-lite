# System Flow

## A. Authentication Flow

```mermaid
flowchart TD
    A[User: Buka halaman login] --> B[User: Input email + password]
    B --> C[Sistem: Validasi kredensial]
    C --> D{Valid?}
    D -- Tidak --> E[Sistem: Tampilkan error, hitung percobaan gagal]
    D -- Ya --> F[Sistem: Buat session + tentukan role]
    F --> G{Role?}
    G -- Admin --> H[Redirect ke Admin Dashboard]
    G -- Supervisor --> I[Redirect ke Supervisor Dashboard]
    G -- Teknisi --> J[Redirect ke Teknisi Task List]
    G -- Plant Manager --> K[Redirect ke Executive Dashboard]
```

Percobaan login gagal berulang (misal 5x) di-throttle sementara (rate limiting) — fitur bawaan Laravel `RateLimiter`.

## B. Authorization Flow (setiap request)

```mermaid
flowchart TD
    A[User: Request suatu action] --> B[Middleware: Cek session/login]
    B --> C{Login valid?}
    C -- Tidak --> D[Redirect ke Login]
    C -- Ya --> E[Middleware/Policy: Cek role & ownership]
    E --> F{Diizinkan?}
    F -- Tidak --> G[Response 403 Forbidden]
    F -- Ya --> H[Lanjutkan ke Controller/Action]
```

Ini mengimplementasikan RBAC secara teknis — setiap action (bukan cuma halaman) dicek lewat Policy Laravel.

## C. Data Flow: Siklus Work Order

```mermaid
flowchart LR
    A[Jadwal PM / Checklist Temuan / Input Manual] --> B[(Work Order Table)]
    B --> C[Assignment ke Teknisi]
    C --> D[Update Status oleh Teknisi]
    D --> E[(Part Usage Table)]
    D --> F[Review Supervisor]
    F --> G[(Asset History Table)]
    G --> H[Dashboard Reliability - Kalkulasi MTBF/MTTR]
```

## D. Notification Flow (in-app saja untuk MVP — lihat ADR)

| Trigger | Penerima | Jenis |
|---|---|---|
| PM jatuh tempo | Supervisor | In-app notification |
| WO baru di-assign | Teknisi | In-app notification |
| WO direject | Teknisi | In-app notification + catatan alasan |
| WO perlu eskalasi (2x reject) | Semua Supervisor | In-app notification |

Email notification ditunda sebagai future development (Should Have).
