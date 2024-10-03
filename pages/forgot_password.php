<?php
// Include necessary files
require_once 'config.php';
require_once 'functions.php';

// Initialize variables
$error = '';
$success = '';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);

    // Validate email
    if (empty($email)) {
        $error = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check if the email exists in the database
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 1) {
            // Generate a unique token
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Store the token in the database
            $query = "UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "sss", $token, $expiry, $email);
            
            if (mysqli_stmt_execute($stmt)) {
                // Send password reset email
                $reset_link = BASE_URL . 'index.php?page=reset_password&token=' . $token;
                $to = $email;
                $subject = "Password Reset Request";
                $message = "Click the following link to reset your password: $reset_link";
                $headers = "From: " . SITE_NAME . " <noreply@example.com>";

                if (mail($to, $subject, $message, $headers)) {
                    $success = 'A password reset link has been sent to your email address.';
                } else {
                    $error = 'Unable to send password reset email. Please try again later.';
                }
            } else {
                $error = 'An error occurred. Please try again later.';
            }
        } else {
            $error = 'No account found with that email address.';
        }
    }
}
?>

<h2>Forgot Password</h2>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php else: ?>
    <form method="post" action="">
        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <button type="submit" class="btn btn-primary">Reset Password</button>
    </form>
<?php endif; ?>
