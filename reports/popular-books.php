<?php
// =====================================================
// reports/popular-books.php
// Shows how many times each book was issued, ranked.
// Uses LEFT JOIN + COUNT + GROUP BY.
// =====================================================

require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../config/helpers.php';

$result = mysqli_query($conn,
    "SELECT b.id, b.book_code, b.title, b.author, b.category,
            COUNT(t.id) AS issue_count
     FROM books b
     LEFT JOIN transactions t ON b.id = t.book_id
     GROUP BY b.id
     ORDER BY issue_count DESC, b.title ASC"
);

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<h1 class="page-title">Popular Books</h1>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Book Code</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Times Issued</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php $rank = 1; while ($b = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>
                                    <?php if ($rank <= 3) { echo '🏆 '; } ?>
                                    <?php echo $rank++; ?>
                                </td>
                                <td><code><?php echo htmlspecialchars($b['book_code']); ?></code></td>
                                <td><?php echo htmlspecialchars($b['title']); ?></td>
                                <td><?php echo htmlspecialchars($b['author']); ?></td>
                                <td><?php echo htmlspecialchars($b['category']); ?></td>
                                <td><span class="badge bg-primary"><?php echo (int)$b['issue_count']; ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No books found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
