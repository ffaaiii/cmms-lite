# Architecture Decision Records (ADR) — Index

## ADR-001: Pemilihan Project Utama
- **Context**: Dibutuhkan project portfolio relevan dengan industri PLTP Darajat, cukup dalam teknis, feasible dikerjakan solo.
- **Decision**: CMMS-Lite berbasis Preventive Maintenance & Work Order, dengan elemen ringan inventory & safety disisipkan sebagai fitur pendukung.
- **Alternatives Considered**: Full Integrated CMMS (3 modul setara) — ditolak, risiko scope creep tinggi untuk solo dev. Inspection & Safety Compliance berdiri sendiri — nilai teknis kurang tinggi. Downtime Analytics berdiri sendiri — scope terlalu sempit.
- **Consequences**: Fokus jelas di work order lifecycle sebagai inti; modul lain harus dijaga ringan atau dipindah ke future development.

## ADR-002: Web Responsive vs PWA
- **Decision**: Web app responsive biasa (bukan PWA) untuk MVP.
- **Alternatives Considered**: PWA dengan offline support — ditolak, kompleksitas tidak sepadan nilai tambah di tahap ini.
- **Consequences**: Butuh koneksi internet untuk akses lapangan; upgrade ke PWA bisa jadi future development.

## ADR-003: Cakupan User Persona & Role
- **Decision**: 4 role: Admin, Supervisor, Teknisi, Plant Manager (view-only, prioritas rendah). Staff Gudang tidak dijadikan role terpisah.
- **Alternatives Considered**: Role Staff Gudang terpisah — ditolak, scope inventory MVP terlalu tipis untuk butuh role sendiri.
- **Consequences**: Role tetap ramping; jika modul inventory diperluas nanti, perlu ADR baru untuk role Staff Gudang.

## ADR-004: Alur Approval & Eskalasi
- **Decision**: (1) Threshold eskalasi WO = 2x reject berturut-turut. (2) Semua usulan WO dari checklist inspeksi wajib dikonfirmasi Supervisor. (3) Eskalasi ditangani sesama Supervisor, Plant Manager tidak terlibat proses operasional.
- **Alternatives Considered**: Teknisi bisa langsung buat WO untuk temuan minor tanpa approval — ditolak, agar titik approval tetap konsisten di satu tempat.
- **Consequences**: RBAC lebih sederhana — hanya Supervisor punya permission create/approve WO; Plant Manager benar-benar terbatas view dashboard.

## ADR-005: Pemilihan Database
- **Decision**: PostgreSQL, bukan MySQL.
- **Alternatives Considered**: MySQL — familiar tapi ENUM/CHECK constraint dan JSONB kurang matang untuk kebutuhan status WO ketat dan checklist dinamis.
- **Consequences**: Perlu belajar sedikit perbedaan syntax PostgreSQL vs MySQL; konsisten dengan pengalaman Supabase sebelumnya.

## ADR-006: Pemilihan Frontend Approach
- **Decision**: Inertia.js + Vue 3, bukan Livewire, bukan SPA+REST API terpisah.
- **Alternatives Considered**: Livewire — ditolak, tidak memberi paparan JS framework modern. Full SPA+API terpisah — ditolak, kompleksitas (CORS, token, API versioning) tidak sepadan kebutuhan sistem ini.
- **Consequences**: Perlu belajar Vue 3 + Inertia dari nol; tetap pakai routing & controller Laravel standar; jika nanti butuh mobile app native, perlu tambah REST API layer terpisah (Sanctum).

## ADR-007: Testing Strategy Level
- **Decision**: Testing (Pest) naik status jadi Must Have, menyatu di tiap milestone — bukan ditumpuk di akhir.
- **Alternatives Considered**: Testing minimal/manual saja — ditolak, user ingin pendalaman skill yang dicari industri.
- **Consequences**: Menambah waktu development per milestone, tapi meningkatkan kualitas portfolio signifikan.

## ADR-008: Arah Visual/Tema UI
- **Decision**: Tema industrial-technical — dark background netral, aksen amber (warning/priority) + teal (normal/success), font sans tegas + monospace untuk data teknis.
- **Alternatives Considered**: Enterprise-clean (putih-biru SaaS) — kurang sesuai konteks industri berat. Gaya navy/cream/gold (seperti project sebelumnya) — ditolak untuk diferensiasi gaya visual antar project portfolio.
- **Consequences**: Perlu perhatian ekstra pada kontras warna teks di background gelap (accessibility); tema konsisten di semua role.
