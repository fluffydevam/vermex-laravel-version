# Legacy Audit — Vermex → Laravel

Source: `vermex-website/` — plain PHP + PDO + sessions, MySQL `vermex_pest_solutions`, Tailwind via CDN, Lucide icons, html2pdf.js.

## Roles & screens

| Role | Screens |
|---|---|
| Admin | Dashboard, Dispatch, Inspections, Clients/Contracts, Payments, Inventory, Reports, Users, Archived Clients |
| Billing Officer | Payments ledger, Client accounts, Financial reports |
| Field Technician | Task dashboard, Job order execution, Pre-inspection report |
| Chemical Custodian | Warehouse dashboard, Inventory |

## Domain (9 tables)

users, clients, contracts, site_inspections, job_orders, job_order_materials, payments, inventory, inventory_logs

## Known bugs / schema drift (all fixed in migration)

1. `toggleClientStatus.php` — `UPDATE clients SET status=? WHERE client_id=?` (should be `id`)
2. `update_inspection.php` — writes nonexistent columns: `inventory.stock_quantity`, `inventory_logs.inventory_id`, `inventory_logs.reference_id`
3. `update_job_order.php` — writes nonexistent columns: `job_orders.status`, `job_orders.technician_notes`
4. `deleteClient.php` — no authentication guard
5. `config/db.php` — runtime `CREATE DATABASE`/`CREATE TABLE`/`ALTER TABLE` on every request
6. `clients.php` — writes computed balance into `final_balance_notes` (TEXT note) while `payments.php` writes `final_balance` (DECIMAL); dual source of truth
7. Billing pages run `ALTER TABLE contracts ADD COLUMN final_balance` at request time
8. Technician stored as free-text `assigned_tech`/`technician_name`, matched with `LIKE %name%`
9. Client name/address duplicated into jobs and inspections (denormalized)
10. `payments.client_id` duplicates `contracts.client_id`
11. Status casing inconsistent: `Active` vs `active`
12. `users.php` searches non-existent `full_name` column
13. No CSRF, no Form Request validation, no pagination, 20–76 KB monolithic views, no layout/components
14. Duplicate payments logic across admin `payments.php` and billing `payments.php`

## Legacy-to-Laravel mapping

| Legacy | Laravel |
|---|---|
| `auth/login.php` + `middleware/auth.php` | Auth controller + `auth`, `active`, `role:*` middleware |
| `views/dashboard.php` | `Admin\DashboardController` |
| `dispatch.php` | `Admin\JobOrderController` + `SaveJobOrder` action |
| `inspections.php` | `Admin\InspectionController` |
| field `pre_inspection.php` | `Field\InspectionController` + `SaveInspectionReport` action |
| `clients.php` + `archived-clients.php` | `Admin\ClientController`, `ContractController`, `ArchivedClientController` |
| `payments.php` (admin + billing) | Shared `PaymentController`/service, role-scoped |
| `inventory.php` (admin + custodian) | Shared `InventoryController`/actions, role-scoped |
| `users.php` + user controllers | `Admin\UserController` (create/update/reset/toggle) |
| `reports.php` (admin + billing) | `Admin\ReportController`, `Billing\ReportController` |
| `config/db.php` auto-DDL | Migrations + seeders + `vermex:import-legacy` |
| Inline JS, CDN Tailwind | Layouts + Blade components + Vite/Tailwind/Alpine |

## Data import notes

- Legacy DB backed up as `vermex_legacy_backup_*.sql` before any work
- Import is read-only on source; `--dry-run` audit first
- Preserves IDs; normalizes statuses to lowercase; repairs orphan FKs; validates JSON fields
- Verifies row counts, collections, contract values, stock totals post-import
