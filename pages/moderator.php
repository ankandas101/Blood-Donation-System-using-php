<?php
// Check if the user is logged in and has moderator role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != ROLE_MODERATOR) {
    header("Location: index.php?page=login");
    exit();
}

// Function to get all pages with moderator access
function getModeratorPages() {
    return [
        'user_management' => 'User Management',
        'manage_requests' => 'Manage Requests',
        'reports' => 'Reports',
        'donation' => 'Donation',
        'manage_donor' => 'Manage Donors',
        'messages' => 'Messages',
        'notices' => 'Notices'
    ];
}

$moderator_pages = getModeratorPages();
?>

<div class="container mt-5">
    <h2 class="mb-4">Moderator Dashboard</h2>
    
    <div class="row">
        <?php foreach ($moderator_pages as $page => $title): ?>
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
