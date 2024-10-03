<?php
// Check if the user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != ROLE_ADMIN) {
    header("Location: index.php?page=login");
    exit();
}

// Function to update system settings
function updateSettings($conn, $settings) {
    foreach ($settings as $key => $value) {
        $query = "UPDATE system_settings SET value = ? WHERE setting_name = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ss", $value, $key);
        mysqli_stmt_execute($stmt);
    }
}

// Function to add a new notice
function addNotice($conn, $notice_text) {
    $query = "INSERT INTO notices (notice_text, created_at) VALUES (?, NOW())";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $notice_text);
    return mysqli_stmt_execute($stmt);
}

// Function to delete a notice
function deleteNotice($conn, $notice_id) {
    $query = "DELETE FROM notices WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $notice_id);
    return mysqli_stmt_execute($stmt);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_settings'])) {
        $new_settings = [
            'site_name' => $_POST['site_name'],
            'admin_email' => $_POST['admin_email'],
            'min_donation_interval' => $_POST['min_donation_interval'],
            'max_blood_shelf_life' => $_POST['max_blood_shelf_life']
        ];
        updateSettings($conn, $new_settings);
        $success_message = "Settings updated successfully.";
    } elseif (isset($_POST['add_notice'])) {
        $notice_text = $_POST['notice_text'];
        if (addNotice($conn, $notice_text)) {
            $success_message = "Notice added successfully.";
        } else {
            $error_message = "Failed to add notice.";
        }
    } elseif (isset($_POST['delete_notice'])) {
        $notice_id = $_POST['notice_id'];
        if (deleteNotice($conn, $notice_id)) {
            $success_message = "Notice deleted successfully.";
        } else {
            $error_message = "Failed to delete notice.";
        }
    }
}

// Fetch current settings
$query = "SELECT * FROM system_settings";
$result = mysqli_query($conn, $query);

// Check if the query was successful
if ($result === false) {
    die("Error executing query: " . mysqli_error($conn));
}

$settings = [];
while ($row = mysqli_fetch_assoc($result)) {
    $settings[$row['setting_name']] = $row['value'];
}

// Fetch current notices
$notices_query = "SELECT * FROM notices ORDER BY created_at DESC";
$notices_result = mysqli_query($conn, $notices_query);
?>

<div class="container mt-5">
    <h2 class="mb-4">System Settings</h2>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success" role="alert">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="site_name" class="form-label">Site Name</label>
            <input type="text" class="form-control" id="site_name" name="site_name" value="<?php echo htmlspecialchars($settings['site_name']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="admin_email" class="form-label">Admin Email</label>
            <input type="email" class="form-control" id="admin_email" name="admin_email" value="<?php echo htmlspecialchars($settings['admin_email']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="min_donation_interval" class="form-label">Minimum Donation Interval (days)</label>
            <input type="number" class="form-control" id="min_donation_interval" name="min_donation_interval" value="<?php echo htmlspecialchars($settings['min_donation_interval']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="max_blood_shelf_life" class="form-label">Maximum Blood Shelf Life (days)</label>
            <input type="number" class="form-control" id="max_blood_shelf_life" name="max_blood_shelf_life" value="<?php echo htmlspecialchars($settings['max_blood_shelf_life']); ?>" required>
        </div>
        <button type="submit" name="update_settings" class="btn btn-primary">Update Settings</button>
    </form>

    <h2 class="mt-5 mb-4">Add New Notice</h2>
        <a href="index.php?page=notices" class="btn btn-secondary">View Notices</a>
    </form>
</div>