<?php
// Check if the user is logged in and has the donor role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== ROLE_USER) {
    echo '<div class="alert alert-danger">Access denied. You must be a registered donor to view this page.</div>';
} else {
    $user_id = $_SESSION['user_id'];
    $user = get_user_by_id($user_id);

    // Display donor information
    ?>
    <h2>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h2>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Your Information</h5>
            <p><strong>Blood Group:</strong> <?php echo htmlspecialchars($user['blood_group']); ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($user['division']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
            <a href="index.php?page=edit_profile" class="btn btn-primary">Edit Profile</a>
        </div>
    </div>

    <!-- Donation History -->
    <h3>Your Donation History</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Date</th>
                <th>Location</th>
                <th>Recipient</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = "SELECT d.donation_date, d.location, u.username as recipient, d.status 
                      FROM donations d 
                      LEFT JOIN users u ON d.recipient_id = u.id 
                      WHERE d.donor_id = ? 
                      ORDER BY d.donation_date DESC";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['donation_date']) . "</td>";
                echo "<td>" . htmlspecialchars($row['location']) . "</td>";
                echo "<td>" . (isset($row['recipient']) ? htmlspecialchars($row['recipient']) : 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                echo "</tr>";
            }
            mysqli_stmt_close($stmt);
            ?>
        </tbody>
    </table>

    <!-- Donation Requests -->
    <h3 class="mt-4">Open Donation Requests</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Requester</th>
                <th>Blood Group</th>
                <th>Location</th>
                <th>Date Needed</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = "SELECT r.id, u.username, r.blood_group, r.location, r.date_needed 
                      FROM donation_requests r 
                      JOIN users u ON r.requester_id = u.id 
                      WHERE r.status = 'open' AND r.blood_group = ? 
                      ORDER BY r.date_needed ASC";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "s", $user['blood_group']);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                echo "<td>" . htmlspecialchars($row['blood_group']) . "</td>";
                echo "<td>" . htmlspecialchars($row['location']) . "</td>";
                echo "<td>" . htmlspecialchars($row['date_needed']) . "</td>";
                echo "<td><a href='index.php?page=respond_request&id=" . $row['id'] . "' class='btn btn-sm btn-primary'>Respond</a></td>";
                echo "</tr>";
            }
            mysqli_stmt_close($stmt);
            ?>
        </tbody>
    </table>

    <?php
}
?>

