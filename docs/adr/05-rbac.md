# Role & Permission (RBAC)

## Prinsip: Segregation of Duty

Satu role tidak boleh punya kuasa penuh atas semua hal — supaya jelas siapa bertanggung jawab atas apa, dan satu akun yang kompromi tidak bisa menghancurkan seluruh sistem.

## RBAC Matrix

| Role | Resource | View | Create | Update | Delete | Approve | Export |
|---|---|---|---|---|---|---|---|
| Admin | User & Role | ✅ | ✅ | ✅ | ✅ (soft delete) | – | – |
| Admin | Aset | ✅ | ✅ | ✅ | ✅ (soft delete) | – | – |
| Admin | Interval PM | ✅ | ✅ | ✅ | – | – | – |
| Supervisor | Aset | ✅ | – | ✅ (kondisi/lokasi) | – | – | ✅ |
| Supervisor | Interval PM | ✅ | ✅ | ✅ | – | – | – |
| Supervisor | Work Order | ✅ | ✅ | ✅ | – | ✅ | ✅ |
| Supervisor | Checklist Inspeksi | ✅ | – | – | – | ✅ (konfirmasi usulan WO) | – |
| Supervisor | Dashboard Reliability | ✅ | – | – | – | – | ✅ |
| Teknisi | Work Order (miliknya saja) | ✅ | – | ✅ (status & catatan) | – | – | – |
| Teknisi | Checklist Inspeksi | ✅ | ✅ | – | – | – | – |
| Teknisi | Aset | ✅ (read-only) | – | – | – | – | – |
| Plant Manager | Dashboard Reliability | ✅ | – | – | – | – | ✅ |
| Plant Manager | Semua resource lain | – | – | – | – | – | – |

## Catatan Implementasi Penting

- **Teknisi hanya bisa lihat/update WO miliknya sendiri** (ownership-based access control / row-level permission) — praktik umum di sistem CMMS/ticketing nyata (Jira, ServiceNow). Ini harus diterapkan di level **Policy backend**, bukan hanya UI, supaya tidak bisa dilewati lewat manipulasi URL/request langsung.
- **Supervisor tidak punya akses Kelola User** — pemisahan tugas, mencegah satu role terlalu berkuasa.
- **Plant Manager benar-benar terbatas** pada 1 permission: view + export dashboard.
