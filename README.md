# SWD Blog CMS

A PHP 8 and MySQL blog CMS for XAMPP, served at `http://localhost/swd/07-php-blog/`.

## Features

- Public homepage with newest-first published posts and six-post pagination.
- Post pages with reading-time estimates and related stories; category pages and full-text search.
- Responsive layouts, clean post/category URLs, and safe paragraph rendering for post bodies.
- Admin sign-in with password hashing, hardened sessions, CSRF checks, and a dashboard.
- Category and post CRUD, status filtering, drafts, excerpts, and validated cover image uploads.
- CLI scripts for creating the first admin and seeding 12 sample posts.

## Screenshots

No screenshots are included yet. Add screenshots of the homepage, an article, and the admin dashboard to a `screenshots/` directory and link them here, for example:

```markdown
![Homepage](screenshots/homepage.png)
![Article](screenshots/article.png)
![Admin dashboard](screenshots/admin-dashboard.png)
```

## Setup with XAMPP

1. Place the project folder at:

   ```text
   C:\xampp\htdocs\swd\07-php-blog
   ```

2. Start **Apache** and **MySQL** in the XAMPP Control Panel. Apache must allow `.htaccess` overrides, and `mod_rewrite` must be enabled for clean URLs.
3. Open `http://localhost/phpmyadmin/`, choose **Import**, select `database.sql` from the project folder, and run the import. It creates the `swd_blog` database, tables, foreign keys, and full-text index.
4. The default database connection is host `127.0.0.1`, port `3306`, database `swd_blog`, user `root`, and an empty password. Configure `SWD_DB_HOST`, `SWD_DB_PORT`, `SWD_DB_NAME`, `SWD_DB_USER`, and `SWD_DB_PASSWORD` if your MySQL settings differ.
5. In Command Prompt, create the first admin:

   ```text
   C:\xampp\php\php.exe C:\xampp\htdocs\swd\07-php-blog\create-admin.php "Your Name" you@example.com
   ```

   Enter a password of at least 12 characters when prompted. This command only creates the first admin.
6. (Optional) Seed the database with categories and 12 example posts:

   ```text
   C:\xampp\php\php.exe C:\xampp\htdocs\swd\07-php-blog\seed.php
   ```

   Create an admin before running the seed script. Existing seed posts are skipped on subsequent runs.
7. Visit `http://localhost/swd/07-php-blog/`. The public site is available at the root; sign in using the **Admin** link.

Cover images must be JPEG, PNG, or WebP and no larger than 2 MB. Make sure `uploads/` is writable by Apache.

### Upgrade an existing database

If the `posts` table predates excerpts, run this once in phpMyAdmin's SQL tab:

```sql
ALTER TABLE posts ADD COLUMN excerpt VARCHAR(500) NULL AFTER slug;
```

### Export the database from phpMyAdmin

1. Open `http://localhost/phpmyadmin/` and select the `swd_blog` database in the left sidebar.
2. Choose **Export**, select **Custom** and **SQL** as the format, and ensure the `admins`, `categories`, and `posts` tables are selected. Include both structure and data if you want a restorable backup.
3. In the output options, choose **Save output to a file**. If available, enable the option to add `CREATE DATABASE` / `USE` statements.
4. Click **Export** and save the downloaded SQL file as:

   ```text
   C:\xampp\htdocs\swd\07-php-blog\database.sql
   ```

   This replaces the project's schema/setup SQL with the exported database snapshot. Keep a separate copy of the original setup file if you want to preserve a clean schema-only import.

## Folder structure

```text
07-php-blog/
├── admin/
│   ├── categories/       # Category list, create, edit, delete
│   ├── posts/            # Post list, create, edit, delete
│   ├── index.php         # Admin dashboard
│   ├── login.php
│   └── logout.php
├── app/                  # Bootstrap, DB, helpers, CSRF, auth (web-denied)
├── assets/
│   └── style.css
├── uploads/              # Cover images (script execution disabled)
├── category.php
├── create-admin.php      # CLI-only first-admin setup
├── database.sql
├── index.php
├── post.php
├── search.php
└── seed.php              # CLI-only sample data
```
