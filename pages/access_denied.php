<?php
// Check if the user is logged in and has the required role
if (isset($_SESSION['user_id'])) {
    $required_role = isset($_GET['required_role']) ? $_GET['required_role'] : '';
    $user_role = $_SESSION['role'] ?? '';

    if ($required_role && $user_role !== $required_role) {
        // User is logged in but doesn't have the required role
        // Continue to show the access denied page
    } else {
        // User has the required role or no specific role was required
        redirect('home');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/custom.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h3 class="text-center">Access Denied</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-center">Sorry, you do not have permission to access this page.</p>
                        <p class="text-center">Please log in with the appropriate role or contact the administrator if you believe this is an error.</p>
                        <div class="text-center mt-4">
                            <a href="index.php?page=login" class="btn btn-primary">Log In</a>
                            <a href="index.php?page=home" class="btn btn-secondary">Go to Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
