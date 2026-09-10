<?php
// =====================================================
// books/edit.php
// Edit an existing book.
// URL: edit.php?id=5
// Rule: total copies cannot go below currently issued copies.
//   issued copies = total copies - available copies
//   new available  = new total - issued copies
// =====================================================

require_once '../includes/auth.php';
requireAdmin();
require_once '../config/db.php';
require_once '../config/helpers.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: list.php");
    exit;
}
$id = (int)$_GET['id'];

// --- Fetch the book ---
$stmt = mysqli_prepare($conn, "SELECT * FROM books WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$book = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$book) {
    $_SESSION['success'] = "Book not found.";
    header("Location: list.php");
    exit;
}

$errors    = [];
$title     = $book['title'];
$author    = $book['author'];
$category  = $book['category'];
$isbn      = $book['isbn'];
$publisher = $book['publisher'];
$total     = (int)$book['total_copies'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title     = trim($_POST['title'] ?? '');
    $author    = trim($_POST['author'] ?? '');
    $category  = trim($_POST['category'] ?? '');
    $isbn      = trim($_POST['isbn'] ?? '');
    $publisher = trim($_POST['publisher'] ?? '');
    $newTotal  = (int)($_POST['total_copies'] ?? 0);

    if ($title === '') {
        $errors[] = 'Title is required.';
    }
    if ($author === '') {
        $errors[] = 'Author is required.';
    }

    // Enforce: total copies cannot be lower than currently issued copies
    $issuedCopies   = (int)$book['total_copies'] - (int)$book['available_copies'];
    if ($newTotal < $issuedCopies) {
        $errors[] = "Total copies cannot be lower than currently issued copies ($issuedCopies).";
    }

    // Check duplicate ISBN excluding this book
    if ($isbn !== '') {
        $stmt = mysqli_prepare($conn, "SELECT id FROM books WHERE isbn = ? AND id != ?");
        mysqli_stmt_bind_param($stmt, "si", $isbn, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = 'This ISBN is already used.';
        }
    }

    if (empty($errors)) {
        // Recalculate available copies
        $newAvailable = $newTotal - $issuedCopies;
        $status       = $newAvailable > 0 ? 'Available' : 'Unavailable';

        $stmt = mysqli_prepare($conn,
            "UPDATE books
             SET title = ?, author = ?, category = ?, isbn = ?, publisher = ?,
                 total_copies = ?, available_copies = ?, status = ?
             WHERE id = ?"
        );
        mysqli_stmt_bind_param($stmt, "sssssiisi",
            $title, $author, $category, $isbn, $publisher, $newTotal, $newAvailable, $status, $id
        );

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Book updated successfully.";
            header("Location: list.php");
            exit;
        } else {
            $errors[] = 'Unable to update book. Please try again.';
        }
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap">
    <h1 class="page-title">Edit Book</h1>
    <a href="list.php" class="btn btn-secondary mb-3"><i class="bi bi-arrow-left me-1"></i> Back to List</a>
</div>

<?php if (!empty($errors)): foreach ($errors as $e) flash($e, 'danger'); endif; ?>

<div class="card">
    <div class="card-body">
        <div class="alert alert-info py-2">
            Currently issued copies: <strong><?php echo (int)$book['total_copies'] - (int)$book['available_copies']; ?></strong>
            &nbsp;|&nbsp; Available copies: <strong><?php echo (int)$book['available_copies']; ?></strong>
        </div>
        <form method="POST" action="edit.php?id=<?php echo $id; ?>" novalidate>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="title" class="form-label">Book Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="title" name="title"
                           value="<?php echo htmlspecialchars($title); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Book Code</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($book['book_code']); ?>" disabled>
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
                    <input type="number" class="form-control" id="total_copies" name="total_copies"
                           min="<?php echo (int)$book['total_copies'] - (int)$book['available_copies']; ?>"
                           value="<?php echo (int)$total; ?>">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Update Book</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
