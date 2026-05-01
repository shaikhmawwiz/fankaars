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
1. Rerun seed:
   `mysql -u root -p business_portal < sql/seed.sql`
2. Login once again using demo password `password123`.

The login flow also has a demo self-heal path for `@example.com` demo users: if stale hashes are found and password is `password123`, it refreshes the stored hash automatically.
