# Database Design

Database: **PostgreSQL** (lihat ADR-005)

## 1. roles

| Kolom | Tipe | Constraint |
|---|---|---|
| id | bigint | PK, auto increment |
| name | varchar(50) | not null |
| slug | varchar(50) | unique, not null (`admin`, `supervisor`, `technician`, `plant_manager`) |
| created_at, updated_at | timestamp | |

## 2. users

| Kolom | Tipe | Constraint |
|---|---|---|
| id | bigint | PK |
| role_id | bigint | FK → roles.id, not null |
| name | varchar(100) | not null |
| email | varchar(150) | unique, not null |
| password | varchar(255) | not null (hashed) |
| email_verified_at | timestamp | nullable |
| created_at, updated_at | timestamp | |
| deleted_at | timestamp | nullable (soft delete) |

Index: `role_id`, `email` (unique)

## 3. assets

| Kolom | Tipe | Constraint |
|---|---|---|
| id | bigint | PK |
| name | varchar(150) | not null |
| category | enum | turbine, well, pipe, cooling_tower, other — not null |
| location | varchar(150) | nullable |
| installed_at | date | nullable |
| condition | enum | good, needs_attention, damaged — default good |
| pm_interval_days | integer | not null, default 90 |
| last_pm_at | date | nullable |
| status | enum | active, inactive — default active |
| created_at, updated_at | timestamp | |
| deleted_at | timestamp | nullable (soft delete) |

Index: `category`, `status`

`pm_interval_days` bertipe integer (bukan enum periode) agar fleksibel — tiap aset bisa punya interval berbeda.

## 4. work_orders

| Kolom | Tipe | Constraint |
|---|---|---|
| id | bigint | PK |
| asset_id | bigint | FK → assets.id, not null |
| assigned_to | bigint | FK → users.id, nullable |
| created_by | bigint | FK → users.id, not null |
| approved_by | bigint | FK → users.id, nullable |
| type | enum | preventive, corrective — not null |
| priority | enum | normal, urgent — default normal |
| status | enum | draft, assigned, in_progress, completed, closed, rejected — default draft |
| description | text | nullable |
| rejection_count | smallint | default 0, CHECK (rejection_count >= 0) |
| rejection_note | text | nullable |
| scheduled_at | date | nullable |
| completed_at | timestamp | nullable |
| closed_at | timestamp | nullable |
| created_at, updated_at | timestamp | |

Index: `asset_id`, `assigned_to`, `status`, composite `(status, priority)`

CHECK constraint pada `rejection_count` menjaga validitas di level database, bukan hanya aplikasi.

## 5. work_order_parts

| Kolom | Tipe | Constraint |
|---|---|---|
| id | bigint | PK |
| work_order_id | bigint | FK → work_orders.id, not null, ON DELETE CASCADE |
| part_name | varchar(150) | not null |
| quantity | integer | not null, CHECK (quantity > 0) |
| unit | varchar(20) | nullable |
| created_at | timestamp | |

## 6. work_order_logs (audit trail, append-only)

| Kolom | Tipe | Constraint |
|---|---|---|
| id | bigint | PK |
| work_order_id | bigint | FK → work_orders.id, not null |
| user_id | bigint | FK → users.id, not null |
| from_status | varchar(20) | nullable |
| to_status | varchar(20) | not null |
| note | text | nullable |
| created_at | timestamp | (tidak ada updated_at — immutable) |

Tabel ini tidak pernah di-UPDATE atau DELETE dari sisi aplikasi — hanya INSERT.

## 7. inspection_checklists

| Kolom | Tipe | Constraint |
|---|---|---|
| id | bigint | PK |
| asset_id | bigint | FK → assets.id, not null |
| inspected_by | bigint | FK → users.id, not null |
| reviewed_by | bigint | FK → users.id, nullable |
| generated_work_order_id | bigint | FK → work_orders.id, nullable |
| condition_found | enum | good, needs_attention, damaged — not null |
| notes | text | nullable |
| status | enum | pending_review, confirmed, dismissed — default pending_review |
| created_at | timestamp | |

## 8. notifications

| Kolom | Tipe | Constraint |
|---|---|---|
| id | bigint | PK |
| user_id | bigint | FK → users.id, not null |
| type | varchar(50) | not null (pm_due, wo_assigned, wo_rejected, wo_escalated) |
| message | varchar(255) | not null |
| related_work_order_id | bigint | FK → work_orders.id, nullable |
| is_read | boolean | default false |
| created_at | timestamp | |

Index: composite `(user_id, is_read)`

## Ringkasan Keputusan Teknis

- Semua enum status pakai PostgreSQL native ENUM type.
- Soft delete hanya di `users` dan `assets`.
- Tidak ada `updated_at` di `work_order_logs` — log tidak boleh diubah setelah dibuat.
