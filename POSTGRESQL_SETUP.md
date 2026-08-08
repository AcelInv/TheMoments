# PostgreSQL setup

1. Install or make a PostgreSQL 14+ server available, then create database `floratica`.
2. Copy `.env.example` to `.env` and fill `FLORATICA_DB_*` with the PostgreSQL connection details. Do not commit `.env`.
   For a managed database, use `FLORATICA_DB_SSLMODE=require`.
3. Enable `extension=pdo_pgsql` and `extension=pgsql` in the `php.ini` used by Apache, then restart Apache.
4. Apply the schema:

   ```powershell
   psql -h 127.0.0.1 -U postgres -d floratica -f backend/migrations/postgresql_schema.sql
   ```

5. Jika memiliki data dari sistem lama, ekspor lalu impor dengan tool migrasi PostgreSQL-aware, kemudian verifikasi jumlah users, products, orders, order_items, payments, carts, wishlists, dan reviews sebelum switch traffic.

The application uses `pgsql` exclusively through `backend/config/Database.php`.
