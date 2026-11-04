# SWD Blog CMS

A PHP 8 and MySQL blog CMS for `http://localhost/swd/07-php-blog/`.

## Setup

1. Start Apache and MySQL in XAMPP.
2. Import `database.sql` in phpMyAdmin or with MySQL. The script creates the `swd_blog` database and its tables, including the post full-text index and foreign keys.
3. If your MySQL connection differs from the XAMPP defaults, set `SWD_DB_HOST`, `SWD_DB_PORT`, `SWD_DB_NAME`, `SWD_DB_USER`, and `SWD_DB_PASSWORD` in the PHP process environment. Defaults are `127.0.0.1:3306`, database `swd_blog`, user `root`, and an empty password.
4. From Command Prompt, create the initial admin:

   ```text
   C:\xampp\php\php.exe C:\xampp\htdocs\swd\07-php-blog\create-admin.php "Your Name" you@example.com
   ```

   Enter a password of at least 12 characters when prompted. The script refuses to create another admin once one exists.
5. Open `http://localhost/swd/07-php-blog/` and use **Admin** to sign in.

The web server must allow `.htaccess` overrides for the app directory protection and upload execution restrictions. Uploaded covers are limited to JPEG, PNG, GIF, or WebP images up to 5 MB.

## Features

- Public post, category, and full-text search pages.
- Admin sign-in, session expiration, CSRF protection, and post/category management.
- Draft and published post states; only published posts appear publicly.
