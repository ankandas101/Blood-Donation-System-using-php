<?php
// Check if the user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != ROLE_ADMIN) {
    header("Location: index.php?page=login");
    exit();
}
?>

<div class="container mt-5">
    <h2 class="mb-4">Admin Dashboard</h2>
    
    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">User Management</h5>
                    <p class="card-text">Manage user accounts, roles, and permissions.</p>
                    <a href="index.php?page=user_management" class="btn btn-primary">Go to User Management</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Manage Blood Requests</h5>
                    <p class="card-text">View and manage all blood donation requests.</p>
                    <a href="index.php?page=manage_requests" class="btn btn-primary">Manage Requests</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Donor Dashboard</h5>
                    <p class="card-text">Access and manage donor information.</p>
                    <a href="index.php?page=donor_dashboard" class="btn btn-primary">Go to Donor Dashboard</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Manage Donors</h5>
                    <p class="card-text">Access and manage donor information.</p>
                    <a href="index.php?page=manage_donor" class="btn btn-primary">Go to Donor Dashboard</a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Manage Donation</h5>
                    <p class="card-text">Access and manage All donation information.</p>
                    <a href="index.php?page=donation" class="btn btn-primary">Go to Donation Dashboard</a>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row mt-4">
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Site Statistics</h5>
                    <p class="card-text">View overall site statistics and analytics.</p>
                    <a href="index.php?page=statistics" class="btn btn-primary">View Statistics</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">System Settings</h5>
                    <p class="card-text">Manage system-wide settings and configurations.</p>
                    <a href="index.php?page=settings" class="btn btn-primary">Manage Settings</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Manage Notices</h5>
                    <p class="card-text">Manage system-wide settings and configurations.</p>
                    <a href="index.php?page=notices" class="btn btn-primary">Manage Settings</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Manage Message</h5>
                    <p class="card-text">See all message.</p>
                    <a href="index.php?page=messages" class="btn btn-primary">Manage Settings</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Reports</h5>
                    <p class="card-text">Generate and view various system reports.</p>
                    <a href="index.php?page=reports" class="btn btn-primary">View Reports</a>
                </div>
            </div>
        </div>
    </div>
</div>
