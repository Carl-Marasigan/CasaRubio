<?php
  session_start();
  if (!isset($_SESSION['user_id'])) {
    // Redirect the user to index.php
    header("Location: index.php");
    exit;
  }
  include('connection_string/connect-db.php');
  include ('../includes/header-admin.php');
  include ('../includes/sidebar.php');
  include ('../includes/navbar.php');

  ?>


<!-- Content Wrapper. Contains page content -->

<div class="container-fluid">

  <?php include ('../includes/it-admin-dashboard.php'); ?>

</div>

<?php
  include ('../includes/footer.php');
?>