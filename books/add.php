<?php
// =====================================================
// books/add.php
// Add a new book.
// - Auto-generates the book code (BOOK001, BOOK002...)
// - Sets available_copies = total_copies
// - Validates input server-side
// =====================================================

require_once '../includes/auth.php';
requireAdmin();
require_once '../config/db.php';
require_once '../config/helpers.php';

$errors   = [];
$title    = '';
$author   = '';
$category = '';
$isbn     = '';
$publisher= '';
$total    = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title'] ?? '');
    $author   = trim($_POST['author'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $isbn     = trim($_POST['isbn'] ?? '');
    $publisher= trim($_POST['publisher'] ?? '');
    $total    = (int)($_POST['total_copies'] ?? 1);

    if ($title === '') {
        $errors[] = 'Title is required.';
    }
    if ($author === '') {
        $errors[] = 'Author is required.';
    }
    if ($total < 1) {
        $errors[] = 'Total copies must be at least 1.';
    }

    // Check duplicate ISBN
    if ($isbn !== '') {
        $stmt = mysqli_prepare($conn, "SELECT id FROM books WHERE isbn = ?");
        mysqli_stmt_bind_param($stmt, "s", $isbn);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = 'This ISBN is already used.';
        }
    }

    if (empty($errors)) {
        $bookCode = generateBookCode($conn);
        $available = $total; // initially all copies available
        $status    = 'Available';

        $stmt = mysqli_prepare($conn,
            "INSERT INTO books
                (book_code, title, author, category, isbn, publisher, total_copies, available_copies, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "ssssssiis",
            $bookCode, $title, $author, $category, $isbn, $publisher, $total, $available, $status
        );

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Book added successfully. Code: " . $bookCode;
            header("Location: list.php");
            exit;
        } else {
            $errors[] = 'Unable to add book. Please try again.';
        }
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';

if (isset($_SESSION['success'])) {
    flash($_SESSION['success']);
    unset($_SESSION['success']);
}
?>

<div class="d-flex justify-content-between align-items-center flex-wrap">
    <h1 class="page-title">Add Book</h1>
    <a href="list.php" class="btn btn-secondary mb-3"><i class="bi bi-arrow-left me-1"></i> Back to List</a>
</div>

<?php if (!empty($errors)): foreach ($errors as $e) flash($e, 'danger'); endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="add.php" novalidate>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="title" class="form-label">Book Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="title" name="title"
                           value="<?php echo htmlspecialchars($title); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="author" class="form-label">Author <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="author" name="author"
                           value="<?php echo htmlspecialchars($author); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="category" class="form-label">Category</label>
                    <input type="text" class="form-control" id="category" name="category"
                           value="<?php echo htmlspecialchars($category); ?>">
                </div>
                <div class="col-md-6">
                    <label for="isbn" class="form-label">ISBN</label>
                    <input type="text" class="form-control" id="isbn" name="isbn"
                           value="<?php echo htmlspecialchars($isbn); ?>">
                </div>
                <div class="col-md-6">
                    <label for="publisher" class="form-label">Publisher</label>
                    <input type="text" class="form-control" id="publisher" name="publisher"
                           value="<?php echo htmlspecialchars($publisher); ?>">
                </div>
                <div class="col-md-6">
                    <label for="total_copies" class="form-label">Total Copies</label>
                    <input type="number" class="form-control" id="total_copies" name="total_copies" min="1"
                           value="<?php echo (int)$total; ?>">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-bookmark-plus me-1"></i> Add Book</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
