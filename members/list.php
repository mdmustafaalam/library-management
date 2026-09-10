<?php
// =====================================================
// members/list.php
// List all members with search (GET ?search=...).
// =====================================================

require_once '../includes/auth.php';
requireAdmin();
require_once '../config/db.php';
require_once '../config/helpers.php';

$search = trim($_GET['search'] ?? '');

if ($search !== '') {
    // Search by member code, name, email, or phone
    $searchValue = "%" . $search . "%";
    $stmt = mysqli_prepare($conn,
        "SELECT * FROM members
         WHERE member_code LIKE ?
            OR name LIKE ?
            OR email LIKE ?
            OR phone LIKE ?
         ORDER BY member_code ASC"
    );
    mysqli_stmt_bind_param($stmt, "ssss", $searchValue, $searchValue, $searchValue, $searchValue);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, "SELECT * FROM members ORDER BY member_code ASC");
}

include '../includes/header.php';
include '../includes/sidebar.php';

if (isset($_SESSION['success'])) {
    flash($_SESSION['success']);
    unset($_SESSION['success']);
}
?>

<div class="d-flex justify-content-between align-items-center flex-wrap">
    <h1 class="page-title mb-3">Members</h1>
    <a href="add.php" class="btn btn-primary mb-3"><i class="bi bi-person-plus me-1"></i> Add Member</a>
</div>

<!-- Search -->
<form method="GET" action="list.php" class="mb-3 search-form">
    <div class="input-group">
        <input type="text" class="form-control" name="search"
               placeholder="Search code, name, email, phone"
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
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php $sn = 1; while ($m = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $sn++; ?></td>
                                <td><code><?php echo htmlspecialchars($m['member_code']); ?></code></td>
                                <td><?php echo htmlspecialchars($m['name']); ?></td>
                                <td><?php echo htmlspecialchars($m['email']); ?></td>
                                <td><?php echo htmlspecialchars($m['phone']); ?></td>
                                <td>
                                    <?php if ($m['status'] === 'Active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d M Y', strtotime($m['created_at'])); ?></td>
                                <td>
                                    <a href="edit.php?id=<?php echo $m['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="history.php?id=<?php echo $m['id']; ?>" class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-clock-history"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No members found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
