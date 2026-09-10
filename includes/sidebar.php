<?php
// =====================================================
// includes/sidebar.php
// Left navigation menu for all protected pages.
// Highlights the section the user is currently viewing.
// Shows admin-only sections only for admin users.
// =====================================================

$currentDir  = basename(dirname($_SERVER['SCRIPT_NAME']));
$currentPage = basename($_SERVER['SCRIPT_NAME']);
$userType    = $_SESSION['user_type'] ?? 'admin';
$isAdmin     = ($userType === 'admin');
$dashboardUrl = $isAdmin ? '../admin/dashboard.php' : '../member/dashboard.php';

// Helper to mark a nav link as active (matches any page inside a directory)
function activeNav($dir, $currentDir)
{
    return $dir === $currentDir ? ' active' : '';
}

// Helper to mark a nav link as active (matches an exact page name)
function pageActive($page, $currentPage)
{
    return $page === $currentPage ? ' active' : '';
}

// Grouped/orphan pages highlight their parent list link
$memberListActive      = ($currentDir === 'members'      && in_array($currentPage, ['list.php', 'edit.php', 'history.php'])) ? ' active' : '';
$bookListActive        = ($currentDir === 'books'        && in_array($currentPage, ['list.php', 'edit.php'])) ? ' active' : '';
$fineListActive        = ($currentDir === 'fines'        && in_array($currentPage, ['list.php', 'payment.php'])) ? ' active' : '';
$reservationListActive = ($currentDir === 'reservations' && in_array($currentPage, ['list.php', 'approve.php'])) ? ' active' : '';

// The three "Add ..." pages share the same filename (add.php), so match by directory too
$addMemberActive      = ($currentPage === 'add.php' && $currentDir === 'members') ? ' active' : '';
$addBookActive        = ($currentPage === 'add.php' && $currentDir === 'books') ? ' active' : '';
$addReservationActive = ($currentPage === 'add.php' && $currentDir === 'reservations') ? ' active' : '';
?>
<!-- Mobile sidebar overlay -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- Sidebar -->
<nav class="sidebar" id="sidebar">
    <a class="sidebar-brand" href="<?php echo $dashboardUrl; ?>">
        <div class="sidebar-brand-icon">
            <i class="bi bi-book-half"></i>
        </div>
        <div class="sidebar-brand-text">
            Library
            <small>Management System</small>
        </div>
    </a>

    <div class="sidebar-nav">
        <ul class="nav nav-pills flex-column">

            <li class="nav-section">Main</li>
            <li class="nav-item">
                <a class="nav-link<?php echo ($currentPage === 'dashboard.php') ? ' active' : ''; ?>" href="<?php echo $dashboardUrl; ?>">
                    <i class="bi bi-grid-1x2"></i> Dashboard
                </a>
            </li>

            <?php if ($isAdmin): ?>
            <li class="nav-section">Members</li>
            <li class="nav-item">
                <a class="nav-link<?php echo $memberListActive; ?>" href="../members/list.php">
                    <i class="bi bi-people"></i> Member List
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?php echo $addMemberActive; ?>" href="../members/add.php">
                    <i class="bi bi-person-plus"></i> Add Member
                </a>
            </li>
            <?php endif; ?>

            <li class="nav-section">Books</li>
            <li class="nav-item">
                <a class="nav-link<?php echo $bookListActive; ?>" href="../books/list.php">
                    <i class="bi bi-book"></i> Book List
                </a>
            </li>
            <?php if ($isAdmin): ?>
            <li class="nav-item">
                <a class="nav-link<?php echo $addBookActive; ?>" href="../books/add.php">
                    <i class="bi bi-bookmark-plus"></i> Add Book
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdmin): ?>
            <li class="nav-section">Transactions</li>
            <li class="nav-item">
                <a class="nav-link<?php echo pageActive('issue-book.php', $currentPage); ?>" href="../transactions/issue-book.php">
                    <i class="bi bi-arrow-right-circle"></i> Issue Book
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link<?php echo pageActive('issued-books.php', $currentPage); ?>" href="../transactions/issued-books.php">
                    <i class="bi bi-list-check"></i> Issued Books
                </a>
            </li>
            <?php if ($isAdmin): ?>
            <li class="nav-item">
                <a class="nav-link<?php echo pageActive('return-book.php', $currentPage); ?>" href="../transactions/return-book.php">
                    <i class="bi bi-arrow-return-left"></i> Return Book
                </a>
            </li>
            <?php endif; ?>

            <li class="nav-section">Fines</li>
            <li class="nav-item">
                <a class="nav-link<?php echo $fineListActive; ?>" href="../fines/list.php">
                    <i class="bi bi-cash-stack"></i> Fine List
                </a>
            </li>

            <li class="nav-section">Reservations</li>
            <li class="nav-item">
                <a class="nav-link<?php echo $reservationListActive; ?>" href="../reservations/list.php">
                    <i class="bi bi-calendar-check"></i> Reservations
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?php echo $addReservationActive; ?>" href="../reservations/add.php">
                    <i class="bi bi-calendar-plus"></i> Add Reservation
                </a>
            </li>

            <?php if ($isAdmin): ?>
            <li class="nav-section">Reports</li>
            <li class="nav-item">
                <a class="nav-link<?php echo pageActive('overdue.php', $currentPage); ?>" href="../reports/overdue.php">
                    <i class="bi bi-exclamation-triangle"></i> Overdue Books
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?php echo pageActive('popular-books.php', $currentPage); ?>" href="../reports/popular-books.php">
                    <i class="bi bi-trophy"></i> Popular Books
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?php echo pageActive('fine-report.php', $currentPage); ?>" href="../reports/fine-report.php">
                    <i class="bi bi-receipt"></i> Fine Report
                </a>
            </li>
            <?php endif; ?>

            <li class="nav-section">Account</li>
            <?php if ($isAdmin): ?>
            <li class="nav-item">
                <a class="nav-link<?php echo pageActive('profile.php', $currentPage); ?>" href="../admin/profile.php">
                    <i class="bi bi-person"></i> Profile
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?php echo pageActive('change-password.php', $currentPage); ?>" href="../admin/change-password.php">
                    <i class="bi bi-key"></i> Change Password
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="sidebar-footer">
        <a class="nav-link" href="../logout.php">
            <i class="bi bi-box-arrow-left"></i> Logout
        </a>
    </div>
</nav>

<!-- Main Content -->
<div class="main-content">
    <!-- Topbar -->
    <header class="topbar">
        <div class="topbar-left">
            <button class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar">
                <i class="bi bi-list"></i>
            </button>
        </div>
        <div class="topbar-right">
            <a class="topbar-btn" href="<?php echo $isAdmin ? '../admin/profile.php' : '#'; ?>">
                <div class="topbar-user">
                    <div class="topbar-avatar"><?php echo $initials; ?></div>
                    <span class="topbar-user-name"><?php echo htmlspecialchars($displayName); ?></span>
                </div>
            </a>
            <a href="../logout.php" class="topbar-btn btn-danger-outline">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </header>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
