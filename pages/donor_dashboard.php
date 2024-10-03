<?php
// Check if the user is logged in and has the donor role
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=login");
    exit();
}


// Fetch donor information
$donor_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $donor_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$donor = mysqli_fetch_assoc($result);

// Fetch donation history
$query = "SELECT * FROM donations WHERE donor_id = ? ORDER BY donation_date DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $donor_id);
mysqli_stmt_execute($stmt);
$donation_history = mysqli_stmt_get_result($stmt);

// Calculate when the donor can donate next
$last_donation_date = null;
$can_donate = true;
$next_donation_date = null;

if (mysqli_num_rows($donation_history) > 0) {
    $last_donation = mysqli_fetch_assoc($donation_history);
    $last_donation_date = new DateTime($last_donation['donation_date']);
    $next_donation_date = clone $last_donation_date;
    $next_donation_date->add(new DateInterval('P' . MIN_DONATION_INTERVAL_DAYS . 'D'));
    $today = new DateTime();
    $can_donate = $today >= $next_donation_date;
    
    // Reset result pointer
    mysqli_data_seek($donation_history, 0);
}
?>

<div class="container mt-5">
    <h2 class="mb-4">Donor Dashboard</h2>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">Personal Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($donor['username']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($donor['email']); ?></p>
                    <p><strong>Blood Group:</strong> <?php echo htmlspecialchars($donor['blood_group']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($donor['phone']); ?></p>
                    <p><strong>Division:</strong> <?php echo htmlspecialchars($donor['division']); ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">Donation Status</h5>
                </div>
                <div class="card-body">
                    <?php if ($can_donate): ?>
                        <div class="alert alert-success">
                            আপনি রক্তদান করার যোগ্য।
                        </div>
                        <a href="index.php?page=donate" class="btn btn-primary">Schedule Donation</a>
                    <?php else: ?>
                        <div class="alert alert-info" style="background-color: red; color: white;">
                            আপনি এখনো রক্তদান করার যোগ্য নন। <br>
                            আপনার পরবর্তী রক্তদানের তারিখ: <?php echo $next_donation_date->format('Y-m-d'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title">Donation History</h5>
        </div>
        <div class="card-body">
            <?php if (mysqli_num_rows($donation_history) > 0): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Location</th>
                            <th>Amount (ml)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($donation = mysqli_fetch_assoc($donation_history)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($donation['donation_date']); ?></td>
                                <td><?php echo htmlspecialchars($donation['location']); ?></td>
                                <td><?php echo htmlspecialchars($donation['quantity']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No donation history available.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
