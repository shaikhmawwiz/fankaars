# Business Management Portal (Complete UI)

## Setup
1. Create database:
   `mysql -u root -p -e "CREATE DATABASE business_portal"`
2. Import schema:
   `mysql -u root -p business_portal < sql/schema.sql`
3. Seed/refresh demo users (safe to run multiple times):
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

## If you see “Invalid credentials”
Run this to refresh user passwords and reactivate accounts:
`mysql -u root -p business_portal < sql/seed.sql`

`sql/seed.sql` now uses `ON DUPLICATE KEY UPDATE`, so existing users are corrected in-place.
