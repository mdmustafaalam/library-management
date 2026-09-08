# Library Management System

A library management system built with PHP, MySQL, and Bootstrap 5. Handles members, books, issuing/returning, fines, reservations, and reports.

## What I Do (Setup)

1. Start **Apache** and **MySQL** from the XAMPP Control Panel.

2. Import the database in phpMyAdmin (`http://localhost/phpmyadmin`):
   - Import `database.sql` (creates `library_management` database, tables, and the default admin account).
   - Optionally import `seed-data.sql` for sample data.

3. Optional: if your MySQL password isn't empty, update `config/db.php`:
   ```php
   $password = "your_password";
   ```

## Access the App

- **On this PC:**
  ```
  http://localhost/library-management/login.php
  ```

- **On my phone (same Wi-Fi):**
  1. Get this PC's IP address by running `ipconfig` (copy the `IPv4 Address`, e.g. `192.168.1.5`).
  2. Open this URL on the phone:
     ```
     http://192.168.1.5/library-management/login.php
     ```
  3. If it doesn't load, allow XAMPP's Apache through Windows Firewall.

### Default Login

| Email    | Password |
| -------- | -------- |
| `admin@library.com` | `admin123` |

Change the password after first login.