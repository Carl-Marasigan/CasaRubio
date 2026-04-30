<?php
session_start();
include("../connection_string/connect-db.php");

if (isset($_POST['login'])) {

    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {

        // ⚠️ still plain password (better upgrade later)
        if ($password === $row['password']) {

            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['firstname'] = $row['firstname'];
            $_SESSION['lastname'] = $row['lastname'];

            header("Location: ../admin/casa-rubio-dashboard.php");
            exit;

        } else {
            header("Location: ../admin/login-admin.php?error=invalid_password");
            exit;
        }

    } else {
        header("Location: ../admin/login-admin.php?error=user_not_found");
        exit;
    }
}
?>