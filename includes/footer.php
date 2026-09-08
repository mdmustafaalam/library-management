<?php
// =====================================================
// includes/footer.php
// Closes the content wrapper + layout, loads JavaScript.
// Included at the end of every protected page.
// =====================================================
?>
        </div><!-- /content-wrapper -->
    </div><!-- /main-content -->
    </div><!-- /app-layout -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('show');
            if (overlay) overlay.classList.toggle('show');
        }
    </script>
    <script src="../assets/js/script.js"></script>
</body>
</html>
