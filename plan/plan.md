# Library Management System — Complete Project Plan

Build a complete **Library Management System** using:

* PHP
* MySQL
* HTML5
* CSS3
* JavaScript
* Bootstrap 5
* MySQLi prepared statements
* PHP Sessions

The project should be beginner-friendly, modular, secure, responsive, and easy to understand.

The system is mainly operated by an **Admin/Librarian**.

---

# 1. Project Name

Library Management System

---

# 2. Main Objectives

The application should allow an administrator/librarian to:

* Login securely
* Manage profile
* Change password
* Add members
* View/search members
* Edit members
* View member borrowing history
* Add books
* View/search books
* Edit books
* Issue books
* Return books
* View currently issued books
* Automatically track available book copies
* Track overdue books
* Calculate fines
* Record fine payments
* Manage book reservations
* Approve or reject reservations
* Generate reports
* View dashboard statistics
* Logout securely

---

# 3. Project Folder Structure

```text
library-management/
│
├── index.php
├── login.php
├── logout.php
│
├── config/
│   └── db.php
│
├── admin/
│   ├── dashboard.php
│   ├── profile.php
│   └── change-password.php
│
├── members/
│   ├── add.php
│   ├── list.php
│   ├── edit.php
│   └── history.php
│
├── books/
│   ├── add.php
│   ├── list.php
│   └── edit.php
│
├── transactions/
│   ├── issue-book.php
│   ├── return-book.php
│   └── issued-books.php
│
├── fines/
│   ├── list.php
│   └── payment.php
│
├── reservations/
│   ├── list.php
│   └── approve.php
│
├── reports/
│   ├── overdue.php
│   ├── popular-books.php
│   └── fine-report.php
│
├── assets/
│   ├── css/
│   │   └── style.css
│   │
│   ├── js/
│   │   └── script.js
│   │
│   └── images/
│
└── includes/
    ├── header.php
    ├── sidebar.php
    ├── footer.php
    └── auth.php
```

---

# 4. File Connection Architecture

Most protected pages should follow this structure:

```php
<?php

require_once '../includes/auth.php';
require_once '../config/db.php';

include '../includes/header.php';
include '../includes/sidebar.php';

?>

<!-- Page content -->

<?php

include '../includes/footer.php';

?>
```

Use:

```php
require_once
```

for important functionality such as:

```text
db.php
auth.php
```

Use:

```php
include
```

for layout files such as:

```text
header.php
sidebar.php
footer.php
```

---

# 5. Database

Database name:

```sql
library_management
```

Create it using:

```sql
CREATE DATABASE library_management;

USE library_management;
```

---

# 6. Database Tables

The database should contain the following tables:

```text
admins
members
books
transactions
fines
reservations
```

---

# 7. Admin Table

```sql
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

Purpose:

Stores administrator/librarian login information.

Password must always be stored using:

```php
password_hash()
```

Login verification must use:

```php
password_verify()
```

Never store plain-text passwords.

---

# 8. Members Table

```sql
CREATE TABLE members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_code VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE,
    phone VARCHAR(20),
    address TEXT,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

Generate member codes automatically:

```text
MEM001
MEM002
MEM003
MEM004
```

---

# 9. Books Table

```sql
CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_code VARCHAR(30) NOT NULL UNIQUE,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(150) NOT NULL,
    category VARCHAR(100),
    isbn VARCHAR(50) UNIQUE,
    publisher VARCHAR(150),
    total_copies INT NOT NULL DEFAULT 1,
    available_copies INT NOT NULL DEFAULT 1,
    status ENUM('Available', 'Unavailable') DEFAULT 'Available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

Generate book codes automatically:

```text
BOOK001
BOOK002
BOOK003
```

When adding a book:

```text
available_copies = total_copies
```

---

# 10. Transactions Table

```sql
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    book_id INT NOT NULL,
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE NULL,
    status ENUM(
        'Issued',
        'Returned',
        'Overdue'
    ) DEFAULT 'Issued',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (member_id)
        REFERENCES members(id),

    FOREIGN KEY (book_id)
        REFERENCES books(id)
);
```

Purpose:

Connects members and books.

Relationship:

```text
members.id
     ↓
transactions.member_id

books.id
     ↓
transactions.book_id
```

Therefore:

```text
Member
   ↓
Transaction
   ↓
Book
```

---

# 11. Fines Table

```sql
CREATE TABLE fines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    member_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    paid_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    status ENUM(
        'Pending',
        'Partial',
        'Paid'
    ) DEFAULT 'Pending',
    payment_date DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (transaction_id)
        REFERENCES transactions(id),

    FOREIGN KEY (member_id)
        REFERENCES members(id)
);
```

Fine should be associated with:

```text
transaction
member
```

---

# 12. Reservations Table

```sql
CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    book_id INT NOT NULL,
    reservation_date DATE NOT NULL,
    status ENUM(
        'Pending',
        'Approved',
        'Rejected',
        'Completed'
    ) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (member_id)
        REFERENCES members(id),

    FOREIGN KEY (book_id)
        REFERENCES books(id)
);
```

---

# 13. Database Relationship Diagram

```text
                     admins


members
   │
   ├──────────────┐
   │              │
   ▼              ▼
transactions    reservations
   │              ▲
   │              │
   ▼              │
fines            books
   ▲              ▲
   │              │
   └──── transactions
```

More clearly:

```text
members.id
   │
   ├────────→ transactions.member_id
   │
   ├────────→ fines.member_id
   │
   └────────→ reservations.member_id


books.id
   │
   ├────────→ transactions.book_id
   │
   └────────→ reservations.book_id


transactions.id
   │
   └────────→ fines.transaction_id
```

---

# 14. config/db.php

This file connects PHP with MySQL.

Example:

```php
<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "library_management";

$conn = mysqli_connect(
    $host,
    $username,
    $password,
    $database
);

if (!$conn) {
    die(
        "Database connection failed: " .
        mysqli_connect_error()
    );
}

?>
```

Pages inside the root folder use:

```php
require_once 'config/db.php';
```

Pages one folder deeper use:

```php
require_once '../config/db.php';
```

---

# 15. Authentication

Create:

```text
includes/auth.php
```

It must:

* Start the session
* Check `$_SESSION['admin_id']`
* Redirect unauthorized users to login.php

Example flow:

```text
Protected Page
     ↓
auth.php
     ↓
admin_id exists?
   /      \
 YES      NO
 ↓         ↓
Page     login.php
```

---

# 16. Login System

File:

```text
login.php
```

Features:

* Email field
* Password field
* Bootstrap login design
* Fetch admin using prepared statement
* Use `password_verify()`
* Store session values after successful login

Session values:

```php
$_SESSION['admin_id']
$_SESSION['admin_name']
$_SESSION['admin_email']
```

Successful login:

```text
login.php
    ↓
admin/dashboard.php
```

Invalid credentials should show an error message.

---

# 17. Logout

File:

```text
logout.php
```

Should:

```php
session_start();
session_unset();
session_destroy();
```

Then redirect:

```text
login.php
```

---

# 18. Reusable Layout

## header.php

Should contain:

* HTML `<head>`
* Bootstrap CSS
* Custom CSS
* Navbar
* Logged-in admin name
* Logout button

---

## sidebar.php

Navigation links:

```text
Dashboard

Members
  - Member List
  - Add Member

Books
  - Book List
  - Add Book

Transactions
  - Issue Book
  - Issued Books

Fines
  - Fine List

Reservations
  - Reservations

Reports
  - Overdue Books
  - Popular Books
  - Fine Report

Profile
Change Password
Logout
```

---

## footer.php

Should contain:

* Closing HTML elements
* Bootstrap JS
* Custom JS

---

# 19. Dashboard

File:

```text
admin/dashboard.php
```

Display cards containing:

```text
Total Members
Total Book Titles
Total Book Copies
Available Copies
Currently Issued Books
Overdue Books
Pending Reservations
Pending Fine Amount
```

Use SQL aggregate functions such as:

```sql
COUNT()
SUM()
```

Examples:

```sql
SELECT COUNT(*) AS total
FROM members;
```

```sql
SELECT COUNT(*) AS total
FROM books;
```

```sql
SELECT SUM(total_copies) AS total
FROM books;
```

```sql
SELECT SUM(available_copies) AS total
FROM books;
```

---

# 20. Members Module

Files:

```text
members/add.php
members/list.php
members/edit.php
members/history.php
```

---

## members/add.php

Features:

* Member name
* Email
* Phone
* Address
* Auto-generated member code
* Default Active status
* Server-side validation
* Prepared statements
* Success/error alerts

Example member code:

```text
MEM001
```

---

## members/list.php

Display:

```text
#
Member Code
Name
Email
Phone
Status
Join Date
Actions
```

Actions:

```text
Edit
History
```

Include search by:

```text
member code
name
email
phone
```

Search URL example:

```text
members/list.php?search=Mustafa
```

---

## members/edit.php

URL:

```text
members/edit.php?id=5
```

Use:

```php
$_GET['id']
```

to identify the selected member.

Allow editing:

```text
name
email
phone
address
status
```

Use prepared statements for update.

---

## members/history.php

URL:

```text
members/history.php?id=5
```

Display member information and complete borrowing history.

Use SQL JOIN:

```sql
SELECT
    transactions.*,
    books.book_code,
    books.title,
    books.author

FROM transactions

INNER JOIN books
ON transactions.book_id = books.id

WHERE transactions.member_id = ?
```

Display:

```text
Book Code
Book Title
Author
Issue Date
Due Date
Return Date
Status
```

---

# 21. Books Module

Files:

```text
books/add.php
books/list.php
books/edit.php
```

---

## books/add.php

Fields:

```text
Title
Author
Category
ISBN
Publisher
Total Copies
```

Generate:

```text
BOOK001
BOOK002
```

Initially:

```text
available_copies = total_copies
```

---

## books/list.php

Display:

```text
#
Book Code
Title
Author
Category
ISBN
Total Copies
Available Copies
Status
Action
```

Search by:

```text
title
author
book code
ISBN
category
```

Availability:

```text
available_copies > 0
    → Available

available_copies = 0
    → Unavailable
```

---

## books/edit.php

Allow editing:

```text
Title
Author
Category
ISBN
Publisher
Total Copies
```

Do not allow total copies to become lower than currently issued copies.

Calculate:

```text
issued copies
=
total copies - available copies
```

Then:

```text
new available copies
=
new total copies - issued copies
```

---

# 22. Transactions Module

Files:

```text
transactions/issue-book.php
transactions/issued-books.php
transactions/return-book.php
```

This is the main business logic of the project.

---

# 23. Issue Book

File:

```text
transactions/issue-book.php
```

Form fields:

```text
Member
Book
Issue Date
Due Date
```

Member dropdown should show only:

```text
Active members
```

Book dropdown should show only books where:

```text
available_copies > 0
```

Example dropdown:

```text
MEM001 - Mustafa

BOOK003 - Java Programming (3 available)
```

---

# 24. Issue Book Logic

When admin submits the form:

### Step 1

Verify member exists and is Active.

### Step 2

Verify book exists.

### Step 3

Verify:

```text
available_copies > 0
```

### Step 4

Insert transaction:

```sql
INSERT INTO transactions (
    member_id,
    book_id,
    issue_date,
    due_date,
    status
)
VALUES (?, ?, ?, ?, 'Issued');
```

### Step 5

Reduce available copies:

```sql
UPDATE books
SET available_copies = available_copies - 1
WHERE id = ?;
```

Use a MySQL database transaction where possible:

```php
mysqli_begin_transaction($conn);
```

If every operation succeeds:

```php
mysqli_commit($conn);
```

If something fails:

```php
mysqli_rollback($conn);
```

---

# 25. Issued Books Page

File:

```text
transactions/issued-books.php
```

Use JOIN between:

```text
transactions
members
books
```

Example:

```sql
SELECT
    transactions.*,
    members.member_code,
    members.name AS member_name,
    books.book_code,
    books.title

FROM transactions

INNER JOIN members
ON transactions.member_id = members.id

INNER JOIN books
ON transactions.book_id = books.id

WHERE transactions.status IN (
    'Issued',
    'Overdue'
)

ORDER BY transactions.id DESC;
```

Display:

```text
Transaction ID
Member
Book
Issue Date
Due Date
Current Status
Return Button
```

---

# 26. Return Book

File:

```text
transactions/return-book.php
```

URL example:

```text
return-book.php?id=15
```

where:

```text
15 = transaction ID
```

Retrieve the transaction.

Prevent returning a book that is already Returned.

On return:

### Step 1

Set:

```text
return_date = today
```

### Step 2

Update transaction:

```text
status = Returned
```

### Step 3

Increase:

```text
books.available_copies + 1
```

### Step 4

Check overdue days.

### Step 5

Calculate fine if necessary.

Use a database transaction to ensure all changes succeed together.

---

# 27. Overdue Logic

A book is overdue when:

```text
today > due_date
AND
status = Issued
```

The application can dynamically show overdue status.

It may also update status to:

```text
Overdue
```

Example SQL:

```sql
UPDATE transactions
SET status = 'Overdue'
WHERE status = 'Issued'
AND due_date < CURDATE();
```

This can run when dashboard/issued-book/report pages load.

---

# 28. Fine Calculation

Set a simple fine rule:

```text
₹10 per overdue day
```

Store this in code as:

```php
$finePerDay = 10;
```

Example:

```text
Due date: 1 September
Return date: 6 September

Overdue = 5 days

Fine:
5 × ₹10
=
₹50
```

Calculation concept:

```php
$overdueDays = ...;

$fineAmount =
    $overdueDays * $finePerDay;
```

Fine should only be generated if:

```text
return_date > due_date
```

Avoid creating duplicate fine records for the same transaction.

---

# 29. Fines Module

Files:

```text
fines/list.php
fines/payment.php
```

---

## fines/list.php

Display:

```text
Fine ID
Member
Book
Due Date
Return Date
Overdue Days
Fine Amount
Paid Amount
Balance
Status
Action
```

Filters:

```text
All
Pending
Partial
Paid
```

---

# 30. Fine Payment

File:

```text
fines/payment.php
```

URL:

```text
payment.php?id=5
```

Display:

```text
Member
Fine Amount
Previously Paid
Remaining Amount
```

Allow admin to enter payment amount.

Validation:

```text
payment > 0
payment <= remaining balance
```

Update:

```text
paid_amount
```

Statuses:

```text
paid_amount = 0
→ Pending

0 < paid_amount < amount
→ Partial

paid_amount >= amount
→ Paid
```

Set payment date when fully paid.

---

# 31. Reservations Module

Files:

```text
reservations/list.php
reservations/approve.php
```

Reservations represent a member requesting a book.

Even though members don't have a separate login in this first version, admin should be able to create/manage reservations.

Reservation fields:

```text
Member
Book
Reservation Date
Status
```

Statuses:

```text
Pending
Approved
Rejected
Completed
```

---

# 32. Reservation List

File:

```text
reservations/list.php
```

Use JOIN:

```text
reservations
members
books
```

Display:

```text
Reservation ID
Member
Book
Reservation Date
Status
Actions
```

Actions:

```text
Approve
Reject
Complete
```

---

# 33. Reservation Approval

File:

```text
reservations/approve.php
```

Verify:

```text
book.available_copies > 0
```

before approval.

Possible flow:

```text
Pending
   ↓
Approved
   ↓
Book issued
   ↓
Completed
```

Do not automatically reduce available copies simply because a reservation is approved unless the reservation is converted into an actual issue transaction.

---

# 34. Reports Module

Files:

```text
reports/overdue.php
reports/popular-books.php
reports/fine-report.php
```

---

# 35. Overdue Report

File:

```text
reports/overdue.php
```

Show transactions where:

```text
due_date < CURDATE()
AND
status IN ('Issued', 'Overdue')
```

Display:

```text
Member
Book
Issue Date
Due Date
Days Overdue
Estimated Fine
```

---

# 36. Popular Books Report

File:

```text
reports/popular-books.php
```

Count how many times each book was issued.

Example SQL:

```sql
SELECT
    books.id,
    books.book_code,
    books.title,
    books.author,
    COUNT(transactions.id) AS issue_count

FROM books

LEFT JOIN transactions
ON books.id = transactions.book_id

GROUP BY books.id

ORDER BY issue_count DESC;
```

Display:

```text
Rank
Book
Author
Times Issued
```

---

# 37. Fine Report

File:

```text
reports/fine-report.php
```

Display:

```text
Total Fine Generated
Total Fine Collected
Outstanding Fine
```

Calculate:

```text
Outstanding
=
Total Fine - Total Paid
```

Also show detailed fine records.

Optional filters:

```text
start date
end date
status
```

---

# 38. Admin Profile

File:

```text
admin/profile.php
```

Display:

```text
Admin Name
Admin Email
Joined Date
```

Allow:

```text
Name update
Email update
```

Use logged-in admin ID:

```php
$_SESSION['admin_id']
```

After profile update also update:

```php
$_SESSION['admin_name']
$_SESSION['admin_email']
```

---

# 39. Change Password

File:

```text
admin/change-password.php
```

Fields:

```text
Current Password
New Password
Confirm New Password
```

Logic:

1. Fetch current password hash.
2. Verify current password with `password_verify()`.
3. Make sure new password and confirmation match.
4. Require reasonable password length.
5. Hash new password using `password_hash()`.
6. Update database.

Never compare stored hashes directly with plain passwords.

---

# 40. Search Functionality

Use GET-based search.

Example:

```text
books/list.php?search=java
```

Use:

```sql
LIKE ?
```

with:

```php
$searchValue = "%" . $search . "%";
```

Always use prepared statements.

---

# 41. Form Validation

All forms should use both:

```text
HTML validation
PHP validation
```

Never rely only on JavaScript.

Validate:

```text
required fields
email format
numeric fields
IDs
status values
dates
copy quantities
payment amounts
```

---

# 42. Security Requirements

Use:

```text
prepared statements
password_hash()
password_verify()
htmlspecialchars()
sessions
server-side validation
```

Protect all internal pages using:

```text
includes/auth.php
```

Never trust:

```php
$_GET
$_POST
```

without validation.

IDs should be checked:

```php
if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
) {
    // redirect
}
```

---

# 43. Prevent SQL Injection

Do not write queries like:

```php
$sql = "SELECT * FROM members WHERE id = " . $_GET['id'];
```

Instead use:

```php
$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM members WHERE id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);
```

---

# 44. Prevent XSS

Whenever database/user data is printed into HTML, use:

```php
htmlspecialchars()
```

Example:

```php
echo htmlspecialchars(
    $member['name']
);
```

---

# 45. Bootstrap UI

Use Bootstrap 5.

Design should contain:

```text
Dark navbar
Light sidebar
Responsive dashboard cards
Bootstrap tables
Bootstrap forms
Badges for statuses
Alert messages
Mobile responsive design
```

Suggested status styles:

```text
Active       → green
Inactive     → gray
Issued       → blue
Returned     → green
Overdue      → red
Pending      → yellow
Paid         → green
Rejected     → red
```

---

# 46. JavaScript

File:

```text
assets/js/script.js
```

Use JavaScript mainly for:

```text
delete/action confirmations
form interactions
optional live search
optional validation
sidebar behavior
```

Do not put important business rules only in JavaScript.

PHP must always validate again.

---

# 47. CSS

File:

```text
assets/css/style.css
```

Add custom styling for:

```text
sidebar
navbar
dashboard cards
tables
forms
responsive spacing
hover effects
status appearance
```

Keep Bootstrap as the primary UI framework.

---

# 48. Dashboard Flow

```text
login.php
   ↓
dashboard.php
   │
   ├── Members
   ├── Books
   ├── Transactions
   ├── Fines
   ├── Reservations
   ├── Reports
   ├── Profile
   └── Logout
```

---

# 49. Member Flow

```text
Member List
   │
   ├── Add Member
   │
   ├── Edit Member
   │
   └── Member History
```

---

# 50. Book Flow

```text
Book List
   │
   ├── Add Book
   │
   └── Edit Book
```

---

# 51. Complete Issue Flow

```text
Admin
  ↓
Issue Book
  ↓
Select Member
  ↓
Select Available Book
  ↓
Choose Issue Date
  ↓
Choose Due Date
  ↓
Validate
  ↓
Create Transaction
  ↓
available_copies - 1
  ↓
Issued Books
```

---

# 52. Complete Return Flow

```text
Issued Books
   ↓
Return
   ↓
Find Transaction
   ↓
Set Return Date
   ↓
Check Due Date
   ↓
Overdue?
 /      \
No      Yes
↓        ↓
No fine Calculate fine
 \       /
  ↓     ↓
Set Returned
   ↓
available_copies + 1
```

---

# 53. Fine Flow

```text
Returned Late
     ↓
Calculate overdue days
     ↓
overdue days × ₹10
     ↓
Create Fine
     ↓
Pending
     ↓
Payment
     ↓
Partial / Paid
```

---

# 54. Reservation Flow

```text
Reservation Created
      ↓
Pending
      ↓
Admin reviews
   /       \
Approve    Reject
   ↓
Approved
   ↓
Issue book later
   ↓
Completed
```

---

# 55. SQL JOIN Concepts Used

The application should teach and demonstrate:

```text
INNER JOIN
LEFT JOIN
COUNT()
SUM()
GROUP BY
ORDER BY
WHERE
LIKE
INSERT
UPDATE
SELECT
```

Example transaction listing:

```sql
SELECT
    transactions.*,
    members.name AS member_name,
    books.title AS book_title

FROM transactions

INNER JOIN members
ON transactions.member_id = members.id

INNER JOIN books
ON transactions.book_id = books.id;
```

---

# 56. Important PHP Concepts Used

Explain these throughout the project:

```text
session_start()

$_SESSION

$_POST

$_GET

$_SERVER

require_once

include

header()

exit

mysqli_connect()

mysqli_prepare()

mysqli_stmt_bind_param()

mysqli_stmt_execute()

mysqli_stmt_get_result()

mysqli_fetch_assoc()

mysqli_query()

password_hash()

password_verify()

htmlspecialchars()

date()

strtotime()
```

---

# 57. File Path Teaching

Explain relative paths clearly.

Example:

Current file:

```text
members/add.php
```

Database file:

```text
config/db.php
```

Because `add.php` is inside `members`, go one folder backward:

```php
require_once '../config/db.php';
```

Meaning:

```text
members/add.php
      ↑
      ../
      ↑
library-management/
      ↓
config/db.php
```

For root file:

```text
login.php
```

use:

```php
require_once 'config/db.php';
```

No `../` is required.

---

# 58. Important Business Rules

The application must enforce these rules:

1. An inactive member cannot borrow a book.

2. A book cannot be issued when:

```text
available_copies <= 0
```

3. Issuing a book decreases:

```text
available_copies
```

by 1.

4. Returning a book increases:

```text
available_copies
```

by 1.

5. A transaction cannot be returned twice.

6. Book total copies cannot become lower than currently issued copies.

7. Fine should only be generated for overdue returns.

8. A fine should not be created twice for one transaction.

9. Payment cannot be greater than outstanding fine.

10. Unauthorized users cannot access dashboard pages.

11. All database writes should use prepared statements.

12. User-entered data must be escaped when displayed.

---

# 59. Recommended Improvement: Database Transactions

Use database transactions for operations involving multiple SQL queries.

For example, when issuing:

```text
INSERT transaction
+
UPDATE available book copies
```

should act as one operation.

Example:

```php
mysqli_begin_transaction($conn);

try {

    // insert transaction

    // update book quantity

    mysqli_commit($conn);

} catch (Throwable $e) {

    mysqli_rollback($conn);
}
```

Similarly use transactions when returning books.

---

# 60. Recommended Due-Date Rule

Default borrowing period:

```text
14 days
```

When selecting issue date, automatically suggest:

```text
due date = issue date + 14 days
```

But allow the admin to change it if required.

---

# 61. Dashboard Additional Section

Below statistics cards, display:

### Recently Issued Books

Columns:

```text
Member
Book
Issue Date
Due Date
Status
```

Limit:

```sql
LIMIT 5
```

Also optionally display:

### Recently Added Members

### Recently Added Books

---

# 62. Empty State Messages

When no records exist, display friendly messages such as:

```text
No members found.

No books found.

No issued books found.

No fines found.

No reservations found.
```

Never show an empty broken table.

---

# 63. Error Handling

Show readable Bootstrap errors.

Examples:

```text
Book not found.

Member not found.

No copies available.

Member account is inactive.

Book already returned.

Invalid payment amount.

Unable to complete transaction.
```

Do not display unnecessary sensitive database details in production.

---

# 64. Date Formatting

Store dates in MySQL format:

```text
YYYY-MM-DD
```

Display dates using:

```php
date(
    "d M Y",
    strtotime($date)
);
```

Example:

```text
2026-09-08
```

displayed as:

```text
08 Sep 2026
```

---

# 65. Recommended Development Order

Build the project in this exact order.

## Phase 1 — Setup

```text
1. Folder structure
2. Database
3. db.php
4. Bootstrap/CSS/JS
```

## Phase 2 — Authentication

```text
5. Admin table
6. Create initial admin
7. Login
8. Sessions
9. auth.php
10. Logout
```

## Phase 3 — Layout

```text
11. header.php
12. sidebar.php
13. footer.php
```

## Phase 4 — Dashboard

```text
14. dashboard.php
15. statistic cards
```

## Phase 5 — Members

```text
16. members table
17. add.php
18. list.php
19. edit.php
20. history.php
```

## Phase 6 — Books

```text
21. books table
22. add.php
23. list.php
24. edit.php
```

## Phase 7 — Transactions

```text
25. transactions table
26. issue-book.php
27. issued-books.php
28. return-book.php
```

## Phase 8 — Fines

```text
29. fines table
30. fine calculation
31. fines/list.php
32. fines/payment.php
```

## Phase 9 — Reservations

```text
33. reservations table
34. reservations/list.php
35. reservations/approve.php
```

## Phase 10 — Reports

```text
36. overdue.php
37. popular-books.php
38. fine-report.php
```

## Phase 11 — Admin

```text
39. profile.php
40. change-password.php
```

## Phase 12 — Final Improvements

```text
41. Search
42. Filters
43. Validation
44. Security
45. Responsive design
46. Testing
47. Error handling
48. Code cleanup
```

---

# 66. Testing Checklist

Test authentication:

```text
Correct login
Wrong email
Wrong password
Logout
Opening dashboard without login
```

Test members:

```text
Add member
Duplicate email
Edit member
Inactive member
Search member
View history
```

Test books:

```text
Add book
Edit book
Search book
Multiple copies
Zero available copies
```

Test transactions:

```text
Issue book
Issue unavailable book
Issue to inactive member
Return book
Return same transaction twice
Book quantity updates correctly
```

Test fines:

```text
Return before due date
Return on due date
Return after due date
Correct overdue days
Correct fine amount
Partial payment
Full payment
Invalid payment
```

Test reports:

```text
Overdue books
Popular books
Fine totals
```

---

# 67. Final Project Connection

```text
                    login.php
                        │
                        ▼
                   dashboard
                        │
        ┌───────────────┼──────────────────┐
        │               │                  │
        ▼               ▼                  ▼
     Members          Books            Transactions
        │               │                  │
        └───────────────┼──────────────────┘
                        │
                        ▼
                  Issue / Return
                        │
                 ┌──────┴──────┐
                 ▼             ▼
               Fines      Reservations
                 │
                 ▼
               Reports
```

Database connection:

```text
PHP Pages
   ↓
config/db.php
   ↓
MySQL
   ↓
library_management
   │
   ├── admins
   ├── members
   ├── books
   ├── transactions
   ├── fines
   └── reservations
```

Authentication connection:

```text
Protected Page
     ↓
includes/auth.php
     ↓
$_SESSION['admin_id']
```

Layout connection:

```text
Protected Page
   │
   ├── header.php
   ├── sidebar.php
   └── footer.php
```

---

# 68. Instructions for Building the Project

Build the entire application according to this specification.

Important requirements:

* Do not put the entire application in one file.
* Follow the exact folder structure.
* Write clean, beginner-friendly PHP.
* Use comments to explain important logic.
* Use MySQLi, not PDO.
* Use prepared statements.
* Use Bootstrap 5.
* Use reusable header/sidebar/footer files.
* Protect admin pages using sessions.
* Use proper foreign keys.
* Use SQL JOINs instead of unnecessary repeated queries.
* Keep database logic understandable.
* Use responsive design.
* Validate every important user input.
* Escape displayed user/database data.
* Use database transactions for issue/return operations.
* Do not break existing modules while adding new modules.

While generating the project, create files **one by one**.

For every file, explain:

1. Where the file should be created.
2. What the file does.
3. Which other files it connects to.
4. Why that connection is required.
5. Provide the complete code.
6. Explain important PHP code.
7. Explain important SQL.
8. Explain the request flow.
9. Explain `GET`, `POST`, sessions, JOINs and foreign keys whenever they appear.
10. Tell me how to test the file before continuing.

Do not skip ahead.

The goal is not only to generate the project but also to teach me how the complete PHP project is connected.

After completing one module, explain its flow diagram before moving to the next module.

The final application should be suitable for a student portfolio/project demonstration.
