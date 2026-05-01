# Business Management Portal (Complete UI)

## Setup
1. Create database:
   `mysql -u root -p -e "CREATE DATABASE business_portal"`
2. Import schema:
   `mysql -u root -p business_portal < sql/schema.sql`
3. Seed demo users:
   `mysql -u root -p business_portal < sql/seed.sql`
4. Update DB credentials in `config/database.php`.
5. Start server: `./bin/run.sh`
6. Visit `http://127.0.0.1:8080/login.php`

## Demo accounts (password: `password123`)
- admin@example.com
- employee@example.com
- core@example.com
- qa@example.com
- accounts@example.com

## Implemented UI + Workflow
- Admin dashboard: create and assign tasks.
- Employee/Core dashboard: start assigned tasks and submit to QA.
- QA dashboard: approve or send back tasks.
- Accounts dashboard: mark delivered commissions as paid.
- Snapshot logic: salary and commission are frozen when task moves to QA Review.
