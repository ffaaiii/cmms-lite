# Project Structure

## Alasan Layer Tambahan

Laravel default (Controller → Model) cukup untuk CRUD sederhana. Sistem ini punya business logic yang tidak sekadar simpan-data:
- Generate jadwal PM otomatis (FR-005)
- State transition Work Order yang harus divalidasi ketat
- Eskalasi otomatis setelah 2x reject (ADR-004)

Logic ini ditulis di **Action classes**, bukan menumpuk di Controller.

## Struktur Folder (Backend)

```
app/
├── Actions/
│   ├── WorkOrder/
│   │   ├── CreateWorkOrderAction.php
│   │   ├── AssignWorkOrderAction.php
│   │   ├── TransitionWorkOrderStatusAction.php   (validasi state machine)
│   │   ├── ApproveWorkOrderAction.php
│   │   ├── RejectWorkOrderAction.php              (termasuk logic eskalasi 2x)
│   │   └── GenerateWorkOrderFromChecklistAction.php
│   └── Maintenance/
│       └── GeneratePreventiveScheduleAction.php   (dipanggil scheduler harian)
│
├── Models/
│   ├── User.php
│   ├── Role.php
│   ├── Asset.php
│   ├── WorkOrder.php
│   ├── WorkOrderPart.php
│   ├── WorkOrderLog.php
│   ├── InspectionChecklist.php
│   └── Notification.php
│
├── Http/
│   ├── Controllers/
│   │   ├── AssetController.php
│   │   ├── WorkOrderController.php
│   │   ├── InspectionChecklistController.php
│   │   ├── DashboardController.php
│   │   └── UserController.php
│   ├── Requests/
│   │   ├── StoreAssetRequest.php
│   │   ├── StoreWorkOrderRequest.php
│   │   ├── RejectWorkOrderRequest.php   (wajib isi rejection_note)
│   │   └── ...
│   └── Middleware/ (bawaan Laravel + custom bila perlu)
│
├── Policies/
│   ├── AssetPolicy.php
│   ├── WorkOrderPolicy.php   (implementasi RBAC, termasuk ownership check Teknisi)
│   └── InspectionChecklistPolicy.php
│
├── Notifications/
│   ├── PmDueNotification.php
│   ├── WorkOrderAssignedNotification.php
│   ├── WorkOrderRejectedNotification.php
│   └── WorkOrderEscalatedNotification.php
│
├── Console/
│   └── Commands/
│       └── CheckPreventiveMaintenanceSchedule.php   (dijalankan scheduler harian)
│
└── Jobs/
    └── (kosong dulu — dipakai kalau ada proses berat/async)
```

## Yang Sengaja Tidak Dipakai

| Pattern | Alasan |
|---|---|
| Repository Pattern | Eloquent Model sudah cukup sebagai abstraksi data untuk skala ini — menambah Repository hanya menambah layer tanpa manfaat nyata |
| Services (terpisah dari Actions) | Cukup satu pola konsisten: Actions untuk semua business logic |
| Events/Listeners | BELUM FINAL — bisa dipertimbangkan lagi kalau notifikasi makin kompleks |
| API Resource | Karena pakai Inertia (bukan REST API murni), data dikirim langsung sebagai props ke Vue |

## Struktur Folder (Frontend — Inertia + Vue)

```
resources/
├── js/
│   ├── Pages/
│   │   ├── Dashboard/
│   │   │   ├── SupervisorDashboard.vue
│   │   │   └── ExecutiveDashboard.vue      (Plant Manager)
│   │   ├── Assets/
│   │   │   ├── Index.vue
│   │   │   └── Show.vue
│   │   ├── WorkOrders/
│   │   │   ├── Index.vue
│   │   │   ├── Show.vue
│   │   │   └── Create.vue
│   │   └── Checklists/
│   │       └── Create.vue
│   ├── Components/
│   │   ├── WorkOrderStatusBadge.vue
│   │   ├── AssetConditionBadge.vue
│   │   └── ...
│   └── Layouts/
│       └── AppLayout.vue
```
