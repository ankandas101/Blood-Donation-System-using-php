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
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

// Function to get total number of donations
function getTotalDonations($conn) {
    $query = "SELECT COUNT(*) as total FROM donations";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

// Function to get total number of blood requests
function getTotalRequests($conn) {
    $query = "SELECT COUNT(*) as total FROM blood_request";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

// Function to get blood group distribution
function getBloodGroupDistribution($conn) {
    $query = "SELECT blood_group, COUNT(*) as count FROM users GROUP BY blood_group";
    $result = mysqli_query($conn, $query);
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
    <h2 class="mb-4">Site Statistics</h2>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <p class="card-text display-4"><?php echo $totalUsers; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Donations</h5>
                    <p class="card-text display-4"><?php echo $totalDonations; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Blood Requests</h5>
                    <p class="card-text display-4"><?php echo $totalRequests; ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <h3>Blood Group Distribution</h3>
            <canvas id="bloodGroupChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var ctx = document.getElementById('bloodGroupChart').getContext('2d');
    var bloodGroupChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_keys($bloodGroupDistribution)); ?>,
            datasets: [{
                label: 'Number of Users',
                data: <?php echo json_encode(array_values($bloodGroupDistribution)); ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
