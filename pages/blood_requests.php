<?php
// Check if the user is logged in and has the appropriate role
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== ROLE_ADMINs)) {
    echo '<div class="alert alert-warning">Access denied. You must be logged in with appropriate permissions to view this page.</div>';
} else {
    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['role'];

    // Function to get status options based on user role
    function getStatusOptions($current_status, $user_role) {
        $options = array('open', 'in_progress', 'fulfilled', 'cancelled');
        $html = '';
        foreach ($options as $option) {
            $selected = ($option == $current_status) ? 'selected' : '';
            $disabled = ($user_role == ROLE_USER && $option != 'open' && $option != 'cancelled') ? 'disabled' : '';
            $html .= "<option value='$option' $selected $disabled>" . ucfirst(str_replace('_', ' ', $option)) . "</option>";
        }
        return $html;
    }

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['create_request'])) {
            // Handle create request
            $blood_group = mysqli_real_escape_string($conn, $_POST['blood_group']);
            $location = mysqli_real_escape_string($conn, $_POST['location']);
            $date_needed = mysqli_real_escape_string($conn, $_POST['date_needed']);
            $details = mysqli_real_escape_string($conn, $_POST['details']);

            $query = "INSERT INTO donation_requests (requester_id, blood_group, location, date_needed, details, status) VALUES (?, ?, ?, ?, ?, 'open')";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "issss", $user_id, $blood_group, $location, $date_needed, $details);
            
            if (mysqli_stmt_execute($stmt)) {
                echo '<div class="alert alert-success">Blood request created successfully.</div>';
            } else {
                echo '<div class="alert alert-danger">Error creating blood request.</div>';
            }
            mysqli_stmt_close($stmt);
        } elseif (isset($_POST['update_request'])) {
            // Handle update request
            $request_id = mysqli_real_escape_string($conn, $_POST['request_id']);
            $status = mysqli_real_escape_string($conn, $_POST['status']);

            $query = "UPDATE donation_requests SET status = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "si", $status, $request_id);
            
            if (mysqli_stmt_execute($stmt)) {
                echo '<div class="alert alert-success">Blood request updated successfully.</div>';
            } else {
                echo '<div class="alert alert-danger">Error updating blood request.</div>';
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Display blood request form for users
    if ($user_role == ROLE_USER) {
        ?>
        <h2>Create Blood Request</h2>
        <form method="post" action="">
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
                <label for="location" class="form-label">Location</label>
                <input type="text" class="form-control" id="location" name="location" placeholder="আপনার কোন হসপিটালে রক্ত লাগবে?" required>
            </div>
            <div class="mb-3">
                <label for="date_needed" class="form-label">Date Needed</label>
                <input type="date" class="form-control" id="date_needed" name="date_needed" placeholder="আপনার কত তারিখে লাগবে?" required>
            </div>
            <div class="mb-3">
                <label for="details" class="form-label">Additional Details</label>
                <textarea class="form-control" id="details" name="details" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary" name="create_request">Submit Request</button>
        </form>
        <?php
    }

    // Display all blood requests
    $query = "SELECT r.id, u.username, r.blood_group, r.location, r.date_needed, r.status 
              FROM donation_requests r 
              JOIN users u ON r.requester_id = u.id 
              ORDER BY r.date_needed ASC";
    $result = mysqli_query($conn, $query);

    echo '<h2 class="mt-4">Blood Requests</h2>';
    echo '<table class="table table-striped">
            <thead>
                <tr>
                    <th>Requester</th>
                    <th>Blood Group</th>
                    <th>Location</th>
                    <th>Date Needed</th>
                    <th>Status</th>
                    ' . (($user_role == ROLE_ADMIN || $user_role == ROLE_MODERATOR) ? '<th>Actions</th>' : '') . '
                </tr>
            </thead>
            <tbody>';

    while ($row = mysqli_fetch_assoc($result)) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['username']) . '</td>';
        echo '<td>' . htmlspecialchars($row['blood_group']) . '</td>';
        echo '<td>' . htmlspecialchars($row['location']) . '</td>';
        echo '<td>' . htmlspecialchars($row['date_needed']) . '</td>';
        echo '<td>' . htmlspecialchars($row['status']) . '</td>';
        if ($user_role == ROLE_ADMIN || $user_role == ROLE_MODERATOR) {
            echo '<td>
                    <form method="post" action="" class="d-inline">
                        <input type="hidden" name="request_id" value="' . $row['id'] . '">
                        <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                            ' . getStatusOptions($row['status'], $user_role) . '
                        </select>
                        <button type="submit" name="update_request" class="btn btn-sm btn-primary">Update</button>
                    </form>
                  </td>';
        }
        echo '</tr>';
    }

    echo '</tbody></table>';
}
?>
