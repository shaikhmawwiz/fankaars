# Business Management Portal (Vanilla PHP + MySQL)

This repository contains a framework-free, modular starter implementation of a **multi-role Business Management Portal** built with **PHP 8.x**, **MySQL**, and **PDO**.

## Roles
- Admin
- Employee
- Core Employee
- QA
- Accounts

## Core Concepts
- Task state machine: `Pending -> In Progress -> QA Review -> Delivered` or `Needs Revision`
- Point-in-time financial snapshots stored on tasks (`snapshot_salary`, `snapshot_commission_pct`)
- Accounts ledger entries for payout audit trails

## Project layout
```
config/
public/
src/
  actions/
  audit/
  auth/
  finance/
sql/
bin/
```

## Quick Run
1. Ensure PHP 8.x is installed.
2. Start the app:
   ```bash
   ./bin/run.sh
   ```
3. Open:
   - `http://127.0.0.1:8080/health`
   - `http://127.0.0.1:8080/health/db`

> `/health/db` returns `503` until `config/database.php` points to a reachable MySQL instance.

## Database setup
1. Create a MySQL database.
2. Import `sql/schema.sql`.
3. Update connection details in `config/database.php`.

## Security posture
- Passwords are intended to be stored with `password_hash()`
- Session-based role checks (`src/auth/session.php`)
- Prepared statements via PDO
- Audit log helper for sensitive actions
