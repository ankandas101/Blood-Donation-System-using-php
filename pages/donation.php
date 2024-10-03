<?php
// Check if the user is logged in and has the appropriate role
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'moderator' && $_SESSION['role'] !== 'user')) {
    echo '<div class="alert alert-warning">Access denied. You must be logged in with appropriate permissions to view this page.</div>';
} else {
    $donor_id = $_SESSION['user_id'];
    $user_role = $_SESSION['role'];

    // Fetch user's donation history
    $query = "SELECT * FROM donations WHERE donor_id = ? ORDER BY donation_date DESC";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt === false) {
        die("Error preparing statement: " . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, "i", $donor_id);
    if (!mysqli_stmt_execute($stmt)) {
        die("Error executing statement: " . mysqli_stmt_error($stmt));
    }
    $result = mysqli_stmt_get_result($stmt);

    // Display donation history
    echo '<h2>Donation History</h2>';
    if (mysqli_num_rows($result) > 0) {
        echo '<table class="table table-striped">
                <thead>
                    <tr>
                        <th>Donation Date</th>
                        <th>Blood Group</th>
                        <th>Quantity (ml)</th>
                        <th>Location</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>';
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<tr>
                    <td>' . htmlspecialchars($row['donation_date']) . '</td>
                    <td>' . htmlspecialchars($row['blood_group']) . '</td>
                    <td>' . htmlspecialchars($row['quantity']) . '</td>
                    <td>' . htmlspecialchars($row['location']) . '</td>
                    <td>
                        <a href="index.php?page=edit_donation&id=' . $row['id'] . '" class="btn btn-sm btn-primary">Edit</a>
                        <a href="index.php?page=donation&action=delete&id=' . $row['id'] . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to delete this donation?\')">Delete</a>
                    </td>
                  </tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p>No donation history found.</p>';
    }

    // Donation form (only for users with 'user' role or higher)
    if ($user_role == 'user' || $user_role == 'moderator' || $user_role == 'admin') {
        // Fetch all donors from users table
        $donors_query = "SELECT id, username FROM users WHERE role = 'donor'";
        $donors_result = mysqli_query($conn, $donors_query);

        echo '<h2 class="mt-4">Record New Donation</h2>
        <form action="" method="post">
            <div class="mb-3">
                <label for="donor_id" class="form-label">Donor</label>
                <select class="form-select" id="donor_id" name="donor_id" required>
                    <option value="">Select Donor</option>';
        while ($donor = mysqli_fetch_assoc($donors_result)) {
            echo '<option value="' . $donor['id'] . '">' . htmlspecialchars($donor['username']) . '</option>';
        }
        echo '</select>
            </div>
            <div class="mb-3">
                <label for="donation_date" class="form-label">Donation Date</label>
                <input type="date" class="form-control" id="donation_date" name="donation_date" required>
            </div>
            <div class="mb-3">
                <label for="blood_group" class="form-label">Blood Group</label>
                <select class="form-select" id="blood_group" name="blood_group" required>
                    <option value="">Select Blood Group</option>
                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB-</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity (ml)</label>
                <input type="number" class="form-control" id="quantity" name="quantity" required>
            </div>
            <div class="mb-3">
                <label for="location" class="form-label">Location</label>
                <input type="text" class="form-control" id="location" name="location" required>
            </div>
            <button type="submit" class="btn btn-primary" name="record_donation">Record Donation</button>
        </form>';

        // Process form submission
        if (isset($_POST['record_donation'])) {
            $donor_id = mysqli_real_escape_string($conn, $_POST['donor_id']);
            $donation_date = mysqli_real_escape_string($conn, $_POST['donation_date']);
            $blood_group = isset($_POST['blood_group']) ? mysqli_real_escape_string($conn, $_POST['blood_group']) : '';
            $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
            $location = mysqli_real_escape_string($conn, $_POST['location']);

            $insert_query = "INSERT INTO donations (donor_id, donation_date, blood_group, quantity, location) VALUES (?, ?, ?, ?, ?)";
            $insert_stmt = mysqli_prepare($conn, $insert_query);
            if ($insert_stmt === false) {
                die("Error preparing statement: " . mysqli_error($conn));
            }
            mysqli_stmt_bind_param($insert_stmt, "issss", $donor_id, $donation_date, $blood_group, $quantity, $location);

            if (mysqli_stmt_execute($insert_stmt)) {
                echo '<div class="alert alert-success mt-3">Donation recorded successfully!</div>';
                // Refresh the page to show the updated donation history
                echo '<meta http-equiv="refresh" content="0">';
            } else {
                echo '<div class="alert alert-danger mt-3">Error recording donation: ' . mysqli_stmt_error($insert_stmt) . '</div>';
            }

            mysqli_stmt_close($insert_stmt);
        }
    }

    // Additional features for moderators and admins
    if ($user_role == 'moderator' || $user_role == 'admin') {
        echo '<h2 class="mt-4">Manage All Donations</h2>';
        
        // Fetch all donations
        $all_donations_query = "SELECT d.*, u.username, u.phone FROM donations d JOIN users u ON d.donor_id = u.id ORDER BY d.donation_date DESC";
        $all_donations_result = mysqli_query($conn, $all_donations_query);

        if (mysqli_num_rows($all_donations_result) > 0) {
            echo '<table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Donor</th>
                            <th>Phone</th>
                            <th>Donation Date</th>
                            <th>Blood Group</th>
                            <th>Quantity (ml)</th>
                            <th>Location</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
            while ($row = mysqli_fetch_assoc($all_donations_result)) {
                echo '<tr>
                        <td>' . htmlspecialchars($row['username']) . '</td>
                        <td>' . htmlspecialchars($row['phone']) . '</td>
                        <td>' . htmlspecialchars($row['donation_date']) . '</td>
                        <td>' . htmlspecialchars($row['blood_group']) . '</td>
                        <td>' . htmlspecialchars($row['quantity']) . '</td>
                        <td>' . htmlspecialchars($row['location']) . '</td>
                        <td>
                            <a href="index.php?page=edit_donation&id=' . $row['id'] . '" class="btn btn-sm btn-primary">Edit</a>
                            <a href="index.php?page=donation&action=delete&id=' . $row['id'] . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to delete this donation?\')">Delete</a>
                        </td>
                      </tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<p>No donations found.</p>';
        }
    }

    // Admin-only features
    if ($user_role == 'admin') {
        echo '<h2 class="mt-4">Donation Analytics</h2>';
        
        // Total donations
        $total_donations_query = "SELECT COUNT(*) as total FROM donations";
        $total_donations_result = mysqli_query($conn, $total_donations_query);
        $total_donations = mysqli_fetch_assoc($total_donations_result)['total'];

        // Total blood volume donated
        $total_volume_query = "SELECT SUM(quantity) as total_volume FROM donations";
        $total_volume_result = mysqli_query($conn, $total_volume_query);
        $total_volume = mysqli_fetch_assoc($total_volume_result)['total_volume'];

        // Donations by blood group
        $blood_group_query = "SELECT blood_group, COUNT(*) as count FROM donations GROUP BY blood_group";
        $blood_group_result = mysqli_query($conn, $blood_group_query);

        echo '<div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total Donations</h5>
                            <p class="card-text">' . $total_donations . '</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total Blood Volume Donated</h5>
                            <p class="card-text">' . $total_volume . ' ml</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Donations by Blood Group</h5>
                            <ul>';
        while ($row = mysqli_fetch_assoc($blood_group_result)) {
            echo '<li>' . $row['blood_group'] . ': ' . $row['count'] . '</li>';
        }
        echo '          </ul>
                        </div>
                    </div>
                </div>
              </div>';
    }
}

// Handle edit action
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $donation_id = intval($_GET['id']);
    // Redirect to the edit_donation page with the donation ID
    header("Location: index.php?page=edit_donation&id=" . $donation_id);
    exit();
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $donation_id = intval($_GET['id']);
    // Perform deletion logic here
    $delete_query = "DELETE FROM donations WHERE id = ?";
    $delete_stmt = mysqli_prepare($conn, $delete_query);
    if ($delete_stmt) {
        mysqli_stmt_bind_param($delete_stmt, "i", $donation_id);
        if (mysqli_stmt_execute($delete_stmt)) {
            echo '<div class="alert alert-success mt-3">Donation deleted successfully!</div>';
        } else {
            echo '<div class="alert alert-danger mt-3">Error deleting donation: ' . mysqli_stmt_error($delete_stmt) . '</div>';
        }
        mysqli_stmt_close($delete_stmt);
    }
    // Refresh the page to show the updated donation list
    echo '<meta http-equiv="refresh" content="0;url=index.php?page=donation">';
}
?>
