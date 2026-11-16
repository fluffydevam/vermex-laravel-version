# Vermex → Laravel Migration Plan

**Source:** `vermex-website/` (plain PHP + PDO + MySQL `vermex_pest_solutions`)
**Target:** Laravel 12 + Blade + Tailwind 4 + Alpine.js + MySQL `vermex_laravel`
**Design:** `DESIGN.md` — Minimalismo Funcional B2B + Vermex crimson identity
**Frontend:** Blade + Alpine.js + Tailwind 4 (client-side print/html2pdf for PDF)

---

## Status

- [x] Phase 0 — Legacy backup + target DB
- [x] Phase 1 — Design foundation (DESIGN.md, tokens, UI kit, layouts, `/styleguide`)
- [x] Phase 2 — Migrations, models, enums, policies, RBAC, import audit
- [x] Phase 3 — Auth + user management
- [x] Phase 4 — Clients & contracts
- [x] Phase 5 — Inventory + audit logs
- [x] Phase 6 — Dispatch / job orders
- [x] Phase 7 — Inspections (admin + field)
- [x] Phase 8 — Payments & billing
- [x] Phase 9 — Dashboards & reports
- [x] Phase 10 — Tests, docs, cutover (ready for go-live)
- [x] UI polish — dark mode, password reveal, client View/Cancel actions
- [x] Billing split — dedicated read-only `/billing/accounts` for Billing Officer; `/admin/clients*` Admin-only

---

## Decisions (locked)

| Area | Decision |
|---|---|
| Data | Import existing `vermex_pest_solutions` into new `vermex_laravel` DB |
| Legacy DB | Never written to; backed up as `vermex_legacy_backup_*.sql` |
| Target DB | `vermex_laravel` (created), legacy untouched |
| Frontend | Blade + Alpine.js + Tailwind 4 via Tailwind CLI (`npm run css:build`) |
| PDF | Client-side print + html2pdf.js |
| Design spec | DESIGN.md (Minimalismo Funcional B2B + Vermex crimson #d32f2f) |
| Auth | Custom guard (no Breeze); username **or** email login; role-aware post-login redirect |
| Logout | POST-only; stale-token tolerance so expired tabs can still sign out |
| Roles | Admin, Billing Officer, Field Technician, Chemical Custodian |
| Theming | Class-based dark mode (`.dark` on `<html>`); OS default on first visit, user choice persisted in `localStorage`; print/PDF forced light |
| Balance | Computed from `payments` via `withSum`; no stored `final_balance` |

---

## Known legacy bugs fixed in migration

- `toggleClientStatus.php`: updates `WHERE client_id` → fixed to `id`
- `update_inspection.php`: writes nonexistent `stock_quantity`, `inventory_logs.inventory_id`, `reference_id` → fixed schema
- `update_job_order.php`: writes nonexistent `job_orders.status`, `technician_notes` → fixed schema
- `deleteClient.php`: no auth → policy-protected
- Runtime `ALTER TABLE`/`CREATE DATABASE` in `config/db.php` → removed (migrations only)
- Stored `final_balance` + `final_balance_notes` drift → computed balance
- Technician stored as free-text name + `LIKE` matching → FK to `users`
- Inconsistent status casing (`Active` vs `active`) → lowercase enum values
- No CSRF/validation/pagination → Form Requests + policies + paginated lists

---

## Route map

```
GET  /                              → role-based redirect
GET  /styleguide                    → StyleguideController
GET|POST /login                     → Auth\AuthenticatedSessionController
POST /logout                        → Auth\AuthenticatedSessionController

── Admin only ─────────────────────────────────────────────────────────
GET  /admin/clients                 → ClientController@index (tabs: clients | contracts | archived)
GET  /admin/clients/create          → ClientController@create
POST /admin/clients                 → ClientController@store
GET  /admin/clients/{client}        → ClientController@show
GET  /admin/clients/{client}/edit   → ClientController@edit
PUT  /admin/clients/{client}        → ClientController@update
DEL  /admin/clients/{client}        → ClientController@archive
PUT  /admin/clients/{client}/restore→ ClientController@restore
POST /admin/contracts               → ContractController@store
PUT  /admin/contracts/{contract}    → ContractController@update
DEL  /admin/contracts/{contract}    → ContractController@destroy
PUT  /admin/contracts/{contract}/archive → ContractController@archive
PUT  /admin/contracts/{contract}/restore → ContractController@restore

── Billing Officer (Admin may also view) ──────────────────────────────
GET  /billing/dashboard             → Billing\DashboardController
GET  /billing/payments              → Billing\PaymentController@index
POST /billing/payments              → Billing\PaymentController@store
GET  /billing/reports               → Billing\ReportController
GET  /billing/accounts              → Billing\AccountController@index (read-only; tabs: clients | contracts | archived)
GET  /billing/accounts/{client}     → Billing\AccountController@show (read-only detail)

── Admin only (operations) ─────────────────────────────────────────────
GET  /admin/dashboard               → Admin\DashboardController
GET  /admin/inventory               → InventoryController@index
GET  /admin/inventory/create        → InventoryController@create
POST /admin/inventory               → InventoryController@store
GET  /admin/inventory/{item}/edit   → InventoryController@edit
PUT  /admin/inventory/{item}        → InventoryController@update
POST /admin/inventory/{item}/adjust → InventoryController@adjust
GET  /admin/job-orders              → JobOrderController@index
GET  /admin/job-orders/create       → JobOrderController@create
POST /admin/job-orders              → JobOrderController@store
GET  /admin/job-orders/{jo}/edit    → JobOrderController@edit
PUT  /admin/job-orders/{jo}         → JobOrderController@update
GET  /admin/inspections             → Admin\InspectionController@index
GET  /admin/inspections/create      → Admin\InspectionController@create
POST /admin/inspections             → Admin\InspectionController@store
GET  /admin/inspections/{i}         → Admin\InspectionController@show
GET  /admin/inspections/{i}/edit    → Admin\InspectionController@edit
PUT  /admin/inspections/{i}         → Admin\InspectionController@update
GET  /admin/payments                → Admin\PaymentController@index
POST /admin/payments                → Admin\PaymentController@store
GET  /admin/reports                 → Admin\ReportController
GET  /admin/users                   → Admin\UserController@index
GET  /admin/users/create            → Admin\UserController@create
POST /admin/users                   → Admin\UserController@store
GET  /admin/users/{user}/edit       → Admin\UserController@edit
PUT  /admin/users/{user}            → Admin\UserController@update
POST /admin/users/{user}/toggle-status    → Admin\UserController@toggleStatus
POST /admin/users/{user}/reset-password   → Admin\UserController@resetPassword

── Field Technician (Admin may also view) ──────────────────────────────
GET  /field/dashboard               → Field\DashboardController
GET  /field/job-orders              → Field\JobOrderController@index
GET  /field/job-orders/{jo}         → Field\JobOrderController@show
POST /field/job-orders/{jo}/status  → Field\JobOrderController@updateStatus
POST /field/job-orders/{jo}/materials → Field\JobOrderController@logMaterials
GET  /field/inspections             → Field\InspectionController@index
GET  /field/inspections/{i}         → Field\InspectionController@show
PUT  /field/inspections/{i}         → Field\InspectionController@update

── Chemical Custodian (Admin may also view) ────────────────────────────
GET  /custodian/dashboard           → Custodian\DashboardController
GET  /custodian/inventory           → Custodian\InventoryController@index
POST /custodian/inventory/{item}/adjust → Custodian\InventoryController@adjust
```

Middleware: `auth`, `active`, `role:Admin|Billing Officer|Field Technician|Chemical Custodian`.
Admin may open Billing, Field, and Custodian dashboards for oversight; Billing Officer may see only
financial views (no operations sections or dispatch/inspection data).

---

## Schema (key tables)

`users` — id, username(u), email(u), password, first_name, last_name, phone, role(enum), sector_region, status(active/disabled), last_login_at, timestamps

`clients` — id, client_name, first_name, last_name, phone_number, email, street_address, barangay, city, client_type(residential/commercial), status(active/inactive/archived), timestamps

`contracts` — id, client_id(FK), contract_name, contract_status(to_be_contracted/active/expiring_soon/expired/cancelled/archived), contract_start_date, contract_end_date, contract_value, timestamps

`site_inspections` — id, contract_id(FK), client_id(FK), technician_id(FK), client_name(snapshot), client_email(snapshot), service_address(snapshot), account_type, inspection_status, inspection_date, time_in, time_out, devices JSON, conditions JSON, findings JSON, signatures, pests_found boolean, timestamps

`job_orders` — id, client_id(FK), contract_id(FK), technician_id(FK), client_name(snapshot), service_address(snapshot), service_type, scheduled_date, service_window, priority, route_status, time_in, time_out, comments, area_findings JSON, timestamps

`job_order_materials` — id, job_order_id(FK), inventory_item_id(FK), quantity_used, unit_used, logged_at

`payments` — id, contract_id(FK), user_id(FK), amount_paid, payment_type, payment_method, reference_number, remarks, payment_date, timestamps

`inventory_items` — id, item_name, category(chemical/device_trap/equipment/ppe), quantity_in_stock, unit, min_threshold, unit_cost, batch_number, storage_location, timestamps (status computed)

`inventory_logs` — id, inventory_item_id(FK), performed_by(FK), job_order_id(FK, nullable), site_inspection_id(FK, nullable), action_type, quantity_changed (signed), remarks, timestamps

---

## Acceptance gates (per phase)

- Role matrix: each role sees only authorized screens
- Imported totals match legacy (row counts, collections, contract value, stock)
- No negative stock; material deduction/restore in transactions
- Archive rule: no contracts → delete; unpaid balance → block; paid → archive
- Disable-account middleware kills session on every request
- Prevent self-disable and removing last active admin
- Responsive at 360/768/1280 px; keyboard-accessible modals/forms
- Print/PDF works for inspections and reports
- Feature tests cover auth, authorization, each domain, importer

---

## Verification (Phase 10)

| Check | Result |
|---|---|
| `php artisan test` | **80 passed, 390 assertions** (isolated `vermex_laravel_test`) |
| `npm run css:build` | `public/css/app.css` ≈ 105 KB, Tailwind 4 CLI |
| `php artisan route:cache` | Succeeds (all routes controller-backed) |
| Authenticated smoke crawl | 36 pages (incl. billing accounts list/detail) return HTTP 200 with imported data, no runtime errors |
| Import reconciliation | users 5, clients 3, inventory items 3, contracts 4, job orders 3, inspections 4, payments 4, stock logs 2, material logs 1 |
| Financial reconciliation | Collections ₱35,000.00 · contract value ₱55,000.00 |
| Balance spot checks | #1 ₱20k paid / ₱0 due · #2 ₱0 / ₱25k · #3 ₱5k / ₱5k · #4 ₱10k / ₱10k |
| Print/PDF | `window.print()` + `downloadPdf()` on admin inspection show and both report views; html2pdf bundle vendored locally (no CDN) |
| Billing read-only accounts | Billing Officer sees `/billing/accounts` + detail (no Add/Edit/Archive/contract writes) and gets 403 on `/admin/clients*`; Admin keeps all controls and may open `/billing/accounts` |

### Test coverage by domain

| Suite | Focus |
|---|---|
| `AuthenticationTest` | username/email login, inactive accounts, CSRF-protected logout |
| `RoleAccessTest` | role matrix, disabled-session middleware, oversight dashboards |
| `ClientManagementTest` | CRUD, archive rules, unpaid-balance blocking, restore |
| `BillingAccountTest` | billing read-only accounts list/detail, hidden write actions, admin oversight |
| `InventoryManagementTest` | adjustments, negative-stock rejection, opening stock, audit logs |
| `JobOrderManagementTest` | dispatch CRUD, status transitions, material deduction, ownership |
| `InspectionManagementTest` | checklist JSON, contract/client consistency, field ownership |
| `PaymentManagementTest` | ledger filters, computed balances, overpayment/archived guards |
| `ReportManagementTest` | financial vs operations sections, period filters |
| `UserManagementTest` | CRUD, self-disable block, last-admin protection, password reset |
| `PrintPdfTest` | print hooks, `#printable-report`, vendored html2pdf asset |
| `ThemeTest` | pre-paint theme script, persisted toggle on login and app shell |

---

## Cutover checklist

1. **Preflight** — PHP 8.2+ with `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`; MySQL 8 / MariaDB; Node 20+ only needed to rebuild CSS.
2. **Environment** — copy `.env`, then set:
   ```dotenv
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://vermex.example
   APP_KEY=            # php artisan key:generate --force

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_DATABASE=vermex_laravel
   DB_USERNAME=vermex_app
   DB_PASSWORD=…

   LEGACY_DB_HOST=127.0.0.1
   LEGACY_DB_DATABASE=vermex_pest_solutions
   LEGACY_DB_USERNAME=vermex_ro   # SELECT-only MySQL user
   LEGACY_DB_PASSWORD=…

   SESSION_DRIVER=file
   ```
3. **Build assets** — `npm ci && npm run css:build`, then confirm `public/css/app.css` and `public/js/vendor/*` exist.
4. **Schema** — `php artisan migrate --force`.
5. **Import** — `php artisan vermex:import-legacy` (dry run), then `php artisan vermex:import-legacy --execute`.
   Re-running is idempotent; add `--force` only for an intentional refresh of existing records. Never point the importer at a writable legacy user.
6. **Credentials** — rotate the bootstrap `admin` password immediately. Imported users keep their legacy hashes; reset any account that cannot authenticate.
7. **Caches** — `php artisan config:cache && php artisan route:cache && php artisan view:cache`. Use `php artisan optimize:clear` before redeploys.
8. **Web server** — document root must be `public/`; serve over HTTPS; deny access to `.env`, `storage/`, and `bootstrap/cache/`; ensure `storage/` and `bootstrap/cache/` are writable by the web user.
9. **Legacy protection** — keep `vermex_pest_solutions` online but read-only for the app account. Retain `vermex_legacy_backup_*.sql`.
10. **UAT script** - log in as each role; create a client + contract; dispatch a job and move it through statuses; log materials and confirm stock deduction; save an inspection; record a payment and confirm overpayment is blocked; print an inspection and a report to PDF; toggle dark mode and confirm the choice persists across reloads and that print/PDF stays light.
11. **Go-live backup** — dump `vermex_laravel` before switching traffic:
    ```powershell
    & "C:\xampp\mysql\bin\mysqldump.exe" -u root vermex_laravel > vermex_laravel_prelaunch.sql
    ```
12. **Rollback** — the legacy app and database remain untouched. Point the docroot/DNS back to the legacy site if needed; restore the pre-launch dump only when a target-data rollback is required.
13. **Monitoring** — watch `storage/logs/laravel.log`, failed login bursts, and nightly database backups. PDF generation is client-side, so it adds no server queue load.

