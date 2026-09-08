<?php
// =====================================================
// books/list.php
// List all books with search (GET ?search=...).
// =====================================================

require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../config/helpers.php';

$search = trim($_GET['search'] ?? '');

if ($search !== '') {
    // Search by title, author, code, ISBN, or category
    $searchValue = "%" . $search . "%";
    $stmt = mysqli_prepare($conn,
        "SELECT * FROM books
         WHERE title LIKE ?
            OR author LIKE ?
            OR book_code LIKE ?
            OR isbn LIKE ?
            OR category LIKE ?
         ORDER BY book_code ASC"
    );
    mysqli_stmt_bind_param($stmt, "sssss", $searchValue, $searchValue, $searchValue, $searchValue, $searchValue);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, "SELECT * FROM books ORDER BY book_code ASC");
}

include '../includes/header.php';
include '../includes/sidebar.php';

if (isset($_SESSION['success'])) {
    flash($_SESSION['success']);
    unset($_SESSION['success']);
}
?>

<div class="d-flex justify-content-between align-items-center flex-wrap">
    <h1 class="page-title mb-3">Books</h1>
    <a href="add.php" class="btn btn-primary mb-3"><i class="bi bi-bookmark-plus me-1"></i> Add Book</a>
</div>

<!-- Search -->
<form method="GET" action="list.php" class="mb-3 search-form">
    <div class="input-group">
        <input type="text" class="form-control" name="search"
               placeholder="Search title, author, code, ISBN..."
               value="<?php echo htmlspecialchars($search); ?>">
        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
        <?php if ($search !== ''): ?>
            <a href="list.php" class="btn btn-outline-danger"><i class="bi bi-x-lg"></i></a>
        <?php endif; ?>
    </div>
</form>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Code</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>ISBN</th>
                        <th>Total</th>
                        <th>Available</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php $sn = 1; while ($b = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $sn++; ?></td>
                                <td><code><?php echo htmlspecialchars($b['book_code']); ?></code></td>
                                <td><?php echo htmlspecialchars($b['title']); ?></td>
                                <td><?php echo htmlspecialchars($b['author']); ?></td>
                                <td><?php echo htmlspecialchars($b['category']); ?></td>
                                <td><?php echo htmlspecialchars($b['isbn']); ?></td>
                                <td><?php echo (int)$b['total_copies']; ?></td>
                                <td><?php echo (int)$b['available_copies']; ?></td>
                                <td>
                                    <?php if ($b['available_copies'] > 0): ?>
                                        <span class="badge bg-success">Available</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Unavailable</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="edit.php?id=<?php echo $b['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="10" class="text-center text-muted py-4">No books found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
