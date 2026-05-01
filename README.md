# Business Management Portal

## Steps to run
1. `mysql -u root -p -e "CREATE DATABASE business_portal"`
2. `mysql -u root -p business_portal < sql/schema.sql`
3. `mysql -u root -p business_portal < sql/seed.sql`
4. Update DB creds in `config/database.php`
5. `./bin/run.sh`
6. Open `http://127.0.0.1:8080/login.php`

## Demo users (password: password123)
- admin@example.com
- employee@example.com
- core@example.com
- qa@example.com
- accounts@example.com
