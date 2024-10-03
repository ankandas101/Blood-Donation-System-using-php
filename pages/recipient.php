<?php
// Check if the user is logged in and has the appropriate role
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== ROLE_RECIPIENT && $_SESSION['role'] !== ROLE_ADMIN)) {
    echo '<div class="alert alert-danger">Access denied. You must be a registered recipient or an admin to view this page.</div>';
    exit(); // Exit the script to prevent further content from being displayed
}

$user_id = $_SESSION['user_id'];
$user = get_user_by_id($user_id);

// Function to get all pages with recipient access
function getRecipientPages() {
    return [
        'request_blood' => 'Request Blood',
        'view_requests' => 'View My Requests',
        'edit_profile' => 'Edit Profile',
        'search_donors' => 'Search Donors'
    ];
}

$recipient_pages = getRecipientPages();
?>

<div class="container mt-5">
    <h2 class="mb-4">Recipient Dashboard</h2>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h5>
            <p><strong>Blood Group:</strong> <?php echo htmlspecialchars($user['blood_group']); ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($user['division']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
        </div>
    </div>

    <h3 class="mb-3">Recipient Actions</h3>
    <div class="row">
        <?php foreach ($recipient_pages as $page => $title): ?>
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($title); ?></h5>
                        <a href="index.php?page=<?php echo $page; ?>" class="btn btn-primary">Go to <?php echo htmlspecialchars($title); ?></a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
