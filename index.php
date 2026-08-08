<?php
// Main entry point for the modular Floratica application
require_once __DIR__ . '/backend/api/auth_helper.php';
$csrfToken = csrfToken();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; base-uri 'self'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; connect-src 'self'; img-src 'self' data: https:; frame-src https://www.google.com;");
include_once __DIR__ . '/views/_head.php';
?>
<body>
  <?php
  include_once __DIR__ . '/views/_navbar.php';
  include_once __DIR__ . '/views/home.php';
  include_once __DIR__ . '/views/katalog_lengkap.php';
  include_once __DIR__ . '/views/checkout.php';
  include_once __DIR__ . '/views/dashboard.php';
  include_once __DIR__ . '/views/modals.php';
  ?>

  <!-- Main JavaScript File -->
  <script src="floratica.js?v=<?php echo time(); ?>"></script>
</body>
</html>
