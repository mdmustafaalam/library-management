# Library Management System

A library management system built with PHP, MySQL, and Bootstrap 5. Handles members, books, issuing/returning, fines, reservations, and reports. Login is **OTP-based** (one-time password sent by email) — no traditional passwords.

## Features

- **Roles:** Administrator (full access) and Member (personal data only)
- **Admin:** manage members, books, issue/return books, fines, reservations, reports
- **Member:** dashboard with own stats, issued books, fines, reservations, and profile
- **OTP login:** users sign in with their email and a 6-digit code sent via SMTP
- **Automatic fines:** calculated on late returns (per-day rate)

## Setup

1. Start **Apache** and **MySQL** from the XAMPP Control Panel.

2. Import the database in phpMyAdmin (`http://localhost/phpmyadmin`):
   - Import `database.sql` (creates `library_management` database, tables, and the default admin account).
   - Optionally import `seed-data.sql` for sample data.

## How Login Works

1. Enter your email on `login.php`.
2. A **6-digit OTP** is sent to that email.
3. Enter the code on the verification screen — you are then signed in as an **admin** or **member** automatically.

## Notes

- Members are created by an admin (member email must be registered and status "Active" to log in).
- Members only see **their own** borrowed books, fines, and reservations.
- Adding members/books, issuing/returning books, recording fine payments, approving reservations, and reports are **admin-only** actions.