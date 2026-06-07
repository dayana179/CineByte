# CineByte — PHP + MySQL Setup

## Requirements
- PHP 8.0+
- MySQL 5.7+ or MariaDB
- XAMPP, WAMP, or MAMP (local server)

## Quick Start

### 1. Place files
Copy the project folder to your server's web root:
- **XAMPP** → `C:/xampp/htdocs/CineByte/`
- **MAMP**  → `/Applications/MAMP/htdocs/CineByte/`

### 2. Create the database
Open **phpMyAdmin** → click **Import** → select `db/schema.sql` → click **Go**

### 3. Configure connection
Copy `db/connection.example.php` → rename it to `db/connection.php`, then fill in:
```php
define('DB_USER',      'root');
define('DB_PASS',      '');           // your MySQL password
define('TMDB_API_KEY', 'YOUR_KEY');   // get free key at themoviedb.org
```

### 4. Get your free TMDb API key
1. Create account at https://www.themoviedb.org
2. Go to Settings → API → Create → Developer
3. Copy the **API Key (v3 auth)** into `db/connection.php`

### 5. Open in browser
```
http://localhost/CineByte/index.php
```

## File Structure
```
CineByte/
├── db/
│   ├── connection.php         ← YOUR secrets (gitignored)
│   ├── connection.example.php ← Template to commit
│   └── schema.sql             ← Run once in phpMyAdmin
├── includes/
│   ├── init.php               ← Session + helper functions
│   ├── header.php             ← Shared nav
│   └── footer.php             ← Shared footer
├── index.php
├── films.php                  ← Browse + search (TMDb API)
├── film-detail.php            ← Film info + trailer + watchlist
├── lists.php                  ← Featured / Top 5 / Most Watched + user lists
├── journal.php                ← Add/view journals (DB)
├── profile.php                ← Watchlist, journals, settings (DB)
├── auth.php                   ← Login / Signup / Logout
├── style.css                  ← All styles
└── script.js                  ← Minimal JS (menu toggle only)
```

## What's stored in MySQL
| Table | What it stores |
|---|---|
| `users` | Accounts with hashed passwords |
| `watchlist` | Per-user film watchlist |
| `user_lists` | User-created named lists |
| `journals` | Journal entries with rating + review |
