<?php
/**
 * MB Internet And Digital Studio | Admin Console Footer (Core PHP)
 */
?>
      </div> <!-- End .admin-content -->
    </div> <!-- End .admin-main -->
  </div> <!-- End .admin-wrapper -->

  <!-- Bootstrap 5 JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    function toggleAdminSidebar() {
      const sidebar = document.getElementById('adminSidebar');
      const backdrop = document.getElementById('adminBackdrop');
      if (sidebar) sidebar.classList.toggle('show');
      if (backdrop) backdrop.classList.toggle('show');
    }
  </script>
</body>
</html>
