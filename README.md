# Library Management System

A library management system built with PHP, MySQL, and Bootstrap 5. Handles members, books, issuing/returning, fines, reservations, and reports. Login is **OTP-based** (one-time password sent by email) — no traditional passwords.

## Features

- **Roles:** Administrator (full access) and Member (personal data only)
- **Admin:** manage members, books, issue/return books, fines, reservations, reports
- **Member:** dashboard with own stats, issued books, fines, reservations, and profile
- **OTP login:** users sign in with their email and a 6-digit code sent via SMTP
- **Automatic fines:** calculated on late returns (per-day rate from `.env`)

## Setup

1. Start **Apache** and **MySQL** from the XAMPP Control Panel.

2. Import the database in phpMyAdmin (`http://localhost/phpmyadmin`):
   - Import `database.sql` (creates `library_management` database, tables, and the default admin account).
   - Optionally import `seed-data.sql` for sample data.

3. Configure `.env` (copy from the repository if not present). Required keys:
   ```env
   DB_HOST=localhost
   DB_USERNAME=root
   DB_PASSWORD=
   DB_NAME=library_management

   # SMTP settings for sending OTP emails (e.g. Gmail App Password)
   SMTP_HOST=smtp.gmail.com
   SMTP_PORT=587
   SMTP_USER=your_email@gmail.com
   SMTP_PASS=your_app_password

   # Fine per overdue day
   FINE_PER_DAY=10
   ```

4. Install dependencies (PHPMailer):
   ```
   composer install
   ```

5. Default admin account: `admin@library.com`
   > No password is needed — login sends an OTP to the registered email.

## Access the App

- **On this PC:**
  ```
  http://localhost/library-management/login.php
  ```

- **On another device (same Wi-Fi):**
  1. Get this PC's IP address by running `ipconfig` (copy the `IPv4 Address`, e.g. `192.168.1.5`).
  2. Open this URL on the device:
     ```
     http://192.168.1.5/library-management/login.php
     ```
  3. If it doesn't load, allow XAMPP's Apache through Windows Firewall.

## How Login Works

1. Enter your email on `login.php`.
2. A **6-digit OTP** is sent to that email.
3. Enter the code on the verification screen — you are then signed in as an **admin** or **member** automatically.

## Notes

- Members are created by an admin (member email must be registered and status "Active" to log in).
- Members only see **their own** borrowed books, fines, and reservations.
- Adding members/books, issuing/returning books, recording fine payments, approving reservations, and reports are **admin-only** actions.