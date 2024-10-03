<?php
// Check if the user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != ROLE_ADMIN) {
    header("Location: index.php?page=login");
    exit();
}

// Function to get total number of users
function getTotalUsers($conn) {
    $query = "SELECT COUNT(*) as total FROM users";
    $result = mysqli_query($conn, $query);
    if ($result === false) {
        return "Error: " . mysqli_error($conn);
    }
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

// Function to get total number of donations
function getTotalDonations($conn) {
    $query = "SELECT COUNT(*) as total FROM donations";
    $result = mysqli_query($conn, $query);
    if ($result === false) {
        return "Error: " . mysqli_error($conn);
    }
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

// Function to get total number of blood requests
function getTotalRequests($conn) {
    $query = "SELECT COUNT(*) as total FROM blood_request";
    $result = mysqli_query($conn, $query);
    if ($result === false) {
        return "Error: " . mysqli_error($conn);
    }
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

// Function to get blood group distribution
function getBloodGroupDistribution($conn) {
    $query = "SELECT blood_group, COUNT(*) as count FROM users GROUP BY blood_group";
    $result = mysqli_query($conn, $query);
    if ($result === false) {
        return "Error: " . mysqli_error($conn);
    }
    $distribution = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $distribution[$row['blood_group']] = $row['count'];
    }
    return $distribution;
}

$totalUsers = getTotalUsers($conn);
$totalDonations = getTotalDonations($conn);
$totalRequests = getTotalRequests($conn);
$bloodGroupDistribution = getBloodGroupDistribution($conn);
?>

<div class="container mt-5">
    <h2 class="mb-4">Admin Reports</h2>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <p class="card-text display-4"><?php echo is_numeric($totalUsers) ? $totalUsers : 'Error occurred'; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Donations</h5>
                    <p class="card-text display-4"><?php echo is_numeric($totalDonations) ? $totalDonations : 'Error occurred'; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Blood Requests</h5>
                    <p class="card-text display-4"><?php echo is_numeric($totalRequests) ? $totalRequests : 'Error occurred'; ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <h3>Blood Group Distribution</h3>
            <?php if (is_array($bloodGroupDistribution)): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Blood Group</th>
                        <th>Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bloodGroupDistribution as $group => $count): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($group); ?></td>
                        <td><?php echo $count; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p>Error: Unable to fetch blood group distribution.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
