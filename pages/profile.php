<?php
require_once 'config.php';
require_once 'functions.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=login");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$user = get_user_by_id($user_id);

if (!$user) {
    echo '<div class="alert alert-danger">No user found with ID: ' . $user_id . '</div>';
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $division = sanitize_input($_POST['division']);
    $location = sanitize_input($_POST['location']);
    $blood_group = sanitize_input($_POST['blood_group']);
    
    $profile_data = [
        'username' => $username,
        'email' => $email,
        'phone' => $phone,
        'division' => $division,
        'location' => $location,
        'blood_group' => $blood_group
    ];
    
    if (update_user_profile($user_id, $profile_data)) {
        echo '<div class="alert alert-success">Profile updated successfully.</div>';
        // Refresh user data
        $user = get_user_by_id($user_id);
        if (!$user) {
            echo '<div class="alert alert-danger">Error fetching updated user data.</div>';
        }
    } else {
        echo '<div class="alert alert-danger">Error updating profile. Please try again.</div>';
    }
}

// Fetch user's donations
$donations_result = get_user_donations($user_id);

// Fetch user's blood requests (latest 5 from the same division)
$requests_result = get_blood_requests_by_division($user['division'], 5);

// Calculate days since last donation
$last_donation = null;
$days_since_last_donation = null;

if (!empty($donations_result)) {
    $last_donation = max(array_column($donations_result, 'donation_date'));
    if ($last_donation) {
        $last_donation_date = new DateTime($last_donation);
        $current_date = new DateTime();
        $days_since_last_donation = $current_date->diff($last_donation_date)->days;
    }
}

// Error handling function
function handle_error($message) {
    error_log($message);
    echo '<div class="alert alert-danger">An error occurred. Please try again later.</div>';
}

?>

<div class="container">
    <div class="row">
        <div class="col-md-3">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">User Role</h5>
                    <p class="card-text"><?php echo htmlspecialchars($user['role']); ?></p>
                    <?php if ($user['role'] == ROLE_ADMIN): ?>
                        <a href="index.php?page=admin" class="btn btn-primary">Go to Admin Page</a>
                    <?php elseif ($user['role'] == ROLE_MODERATOR): ?>
                        <a href="index.php?page=moderator" class="btn btn-primary">Go to Moderator Page</a>
                    <?php elseif ($user['role'] == ROLE_RECIPIENT): ?>
                        <a href="index.php?page=recipient" class="btn btn-primary">Go to Recipient Page</a>
                    <?php elseif ($user['role'] == ROLE_DONOR): ?>
                        <a href="index.php?page=donor_dashboard" class="btn btn-primary">Go to Donor Page</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <h2>User Profile</h2>
            <div class="row">
                <div class="col-md-6">
                    <h3>Personal Information</h3>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="division" class="form-label">Division</label>
                            <input type="text" class="form-control" id="division" name="division" value="<?php echo htmlspecialchars($user['division']); ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($user['location']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="blood_group" class="form-label">Blood Group</label>
                            <input type="text" class="form-control" id="blood_group" name="blood_group" value="<?php echo htmlspecialchars($user['blood_group']); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
                <div class="col-md-6">
                    <h3>Donation History</h3>
                    <?php
                    // Fetch the latest donation history
                    $donations_result = get_user_donations($user_id);
                    if (!empty($donations_result)): ?>
                        <ul class="list-group">
                            <?php foreach ($donations_result as $donation): ?>
                                <li class="list-group-item">
                                    Date: <?php echo htmlspecialchars($donation['donation_date']); ?>
                                    Location: <?php echo htmlspecialchars($donation['location']); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <p>Days since last donation: <?php echo $days_since_last_donation; ?></p>
                    <?php else: ?>
                        <p>No donation history available.</p>
                    <?php endif; ?>

                    <h3>Recent Blood Requests in Your Division</h3>
                    <?php if (!empty($requests_result)): ?>
                        <ul class="list-group">
                            <?php foreach ($requests_result as $request): ?>
                                <li class="list-group-item">
                                    <strong>Blood Group: </strong> <?php echo htmlspecialchars($request['blood_group']); ?>
                                    <strong>Distict : </strong> <?php echo htmlspecialchars($request['district']); ?>
                                    <strong>Date: </strong> <?php echo htmlspecialchars($request['required_date']); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No recent blood requests in your division.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var roleLinks = document.querySelectorAll('.card-body a.btn-primary');
    roleLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var href = this.getAttribute('href');
            window.location.href = href;
        });
    });
});
</script>
