# Database Schema Floratica

File ini berisi dokumentasi skema PostgreSQL, penjelasan relasi, dan justifikasi normalisasi untuk aplikasi E-Commerce Floratica. File SQL utama yang dapat langsung dijalankan tersedia di `backend/migrations/postgresql_schema.sql`.

## 1. Entity Relationship Diagram (ERD)

```mermaid
erDiagram
    users {
        bigint id PK
        varchar role "admin, customer"
        varchar name
        varchar email
        varchar password_hash
        varchar phone
        timestamptz created_at
    }
    categories {
        bigint id PK
        varchar name
        varchar slug
        varchar description
        timestamptz created_at
    }
    products {
        bigint id PK
        bigint category_id FK
        varchar name
        varchar slug
        text description
        numeric price
        integer stock
        varchar image_url "legacy URL optional"
        bytea image_data "gambar produk"
        varchar image_mime_type
        timestamptz created_at
    }
    wishlists {
        bigint id PK
        bigint user_id FK
        bigint product_id FK
        timestamptz created_at
    }
    carts {
        bigint id PK
        bigint user_id FK "nullable (for guest)"
        varchar session_id "nullable (for guest)"
        bigint product_id FK
        integer quantity
        timestamptz created_at
    }
    orders {
        bigint id PK
        bigint user_id FK
        varchar invoice_number
        numeric total_amount
        varchar status "pending, processing, shipped, completed, cancelled"
        text shipping_address
        timestamptz created_at
    }
    order_items {
        bigint id PK
        bigint order_id FK
        bigint product_id FK
        integer quantity
        numeric price "Harga saat transaksi"
        numeric subtotal
    }
    payments {
        bigint id PK
        bigint order_id FK
        varchar payment_method
        varchar payment_status "pending, success, failed"
        numeric amount
        varchar transaction_id
        timestamptz created_at
    }
    shipments {
        bigint id PK
        bigint order_id FK
        varchar tracking_number
        varchar courier
        varchar status "pending, shipped, delivered, returned"
        timestamptz created_at
    }
    reviews {
        bigint id PK
        bigint user_id FK
        bigint product_id FK
        smallint rating "1-5"
        text comment
        timestamptz created_at
    }

    users ||--o{ wishlists : "has"
    users ||--o{ carts : "has"
    users ||--o{ orders : "places"
    users ||--o{ reviews : "writes"
    
    categories ||--o{ products : "contains"
    
    products ||--o{ wishlists : "added to"
    products ||--o{ carts : "added to"
    products ||--o{ order_items : "included in"
    products ||--o{ reviews : "receives"
    
    orders ||--|{ order_items : "contains"
    orders ||--o| payments : "has"
    orders ||--o| shipments : "has"
```

## 2. Penjelasan Desain dan Normalisasi (3NF)

Desain tabel ini dibuat dengan mematuhi prinsip Third Normal Form (3NF):

1. **Tidak ada duplikasi array/data (1NF)**: Data setiap entitas dipisah secara atomik di dalam row.
2. **Ketergantungan Kunci Penuh (2NF)**: Setiap kolom benar-benar mendeskripsikan primary key-nya. Contoh: `price` produk ada di tabel `products`, bukan nempel di `categories`.
3. **Pemisahan Harga Historis (3NF)**: Perhatikan tabel `order_items` memiliki kolom `price`. Ini adalah **snapshot harga** pada saat pesanan dibuat. Tujuannya adalah jika besok harga produk naik di tabel `products`, harga di invoice riwayat pesanan (order) lama **tidak boleh ikut berubah**. Ini adalah pilar terpenting dalam relasi database E-Commerce.
4. **Relasi Many-To-Many Dibuatkan Tabel Pivot**: Seperti hubungan antara user dan produk yang disukai, dibuatkan tabel `wishlists`. Begitu juga pesanan dengan detail produk yang dipesan via tabel `order_items`.

## 3. Optimasi & Skalabilitas
- Struktur ini sudah diberikan **index PostgreSQL** (bisa dilihat pada schema utama). Kolom seperti `email`, `slug`, dan `category_id` diberikan index untuk mempercepat kecepatan pencarian/query saat data membesar.
- Penerapan `ON DELETE CASCADE` pada tabel keranjang atau wishlist agar database rapi ketika data referensinya dihapus.
- Penerapan `ON DELETE RESTRICT` pada tabel produk jika masuk ke pesanan (`order_items`). Jika admin menghapus produk yang sudah pernah dibeli orang, database akan menolak untuk menjaga integritas riwayat transaksi customer.
