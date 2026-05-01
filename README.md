# Business Management Portal

Dark-mode Bootstrap 5 + Vanilla PHP + MySQL(PPDO) monolith with strict RBAC and snapshot-based commission integrity.

## Run
1. `mysql -u root -p -e "CREATE DATABASE business_portal"`
2. `mysql -u root -p business_portal < sql/schema.sql`
3. `mysql -u root -p business_portal < sql/seed.sql`
4. Configure DB in `config/database.php`
5. `./bin/run.sh`
6. Open `http://127.0.0.1:8080/login.php`

## Demo users (password: `password123`)
- admin@example.com (admin)
- employee@example.com (employee)
- core@example.com (core)
- qa@example.com (qa)
- accounts@example.com (accounts)

## Implemented security
- Prepared statements for DB writes/reads in action modules.
- `password_hash/password_verify` auth.
- Session gate + active-user revalidation on each request.
- Soft block (`is_active=0`) instead of deleting users.

## Snapshot logic
On employee/core submission (`in_progress -> completed`):
- Save `snapshot_percentage` from current user percentage
- Save `snapshot_payout = task_value * (snapshot_percentage/100)`

QA approval (`completed -> deliverable`) locks task via `is_locked=1`.
Accounts payout uses frozen `snapshot_payout` and writes to `payouts` ledger.
