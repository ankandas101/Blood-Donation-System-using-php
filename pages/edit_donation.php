<?php
// Check if the user is logged in and has the appropriate role
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'moderator' && $_SESSION['role'] !== 'user')) {
    echo '<div class="alert alert-warning">Access denied. You must be logged in with appropriate permissions to view this page.</div>';
    exit();
}

$donation_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($donation_id === 0) {
    echo '<div class="alert alert-danger">Invalid donation ID.</div>';
    exit();
}

// Fetch the donation details
$query = "SELECT * FROM donations WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
if ($stmt === false) {
    die("Error preparing statement: " . mysqli_error($conn));
}
mysqli_stmt_bind_param($stmt, "i", $donation_id);
if (!mysqli_stmt_execute($stmt)) {
    die("Error executing statement: " . mysqli_stmt_error($stmt));
}
$result = mysqli_stmt_get_result($stmt);
$donation = mysqli_fetch_assoc($result);

if (!$donation) {
    echo '<div class="alert alert-danger">Donation not found.</div>';
    exit();
}

// Check if the logged-in user is the donor or has admin/moderator privileges
if ($_SESSION['user_id'] != $donation['donor_id'] && $_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'moderator') {
    echo '<div class="alert alert-warning">You do not have permission to edit this donation.</div>';
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $donation_date = sanitize_input($_POST['donation_date']);
    $blood_group = sanitize_input($_POST['blood_group']);
    $quantity = intval($_POST['quantity']);
    $location = sanitize_input($_POST['location']);

    $update_query = "UPDATE donations SET donation_date = ?, blood_group = ?, quantity = ?, location = ? WHERE id = ?";
    $update_stmt = mysqli_prepare($conn, $update_query);
    if ($update_stmt === false) {
        die("Error preparing update statement: " . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($update_stmt, "ssisi", $donation_date, $blood_group, $quantity, $location, $donation_id);
    if (mysqli_stmt_execute($update_stmt)) {
        echo '<div class="alert alert-success">Donation updated successfully.</div>';
        // Refresh donation data
        $result = mysqli_stmt_get_result($stmt);
        $donation = mysqli_fetch_assoc($result);
    } else {
        echo '<div class="alert alert-danger">Error updating donation: ' . mysqli_stmt_error($update_stmt) . '</div>';
    }
    mysqli_stmt_close($update_stmt);
}
?>

<div class="container">
    <h2>Edit Donation</h2>
    <form method="POST" action="">
        <div class="mb-3">
            <label for="donation_date" class="form-label">Donation Date</label>
            <input type="date" class="form-control" id="donation_date" name="donation_date" value="<?php echo htmlspecialchars($donation['donation_date']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="blood_group" class="form-label">Blood Group</label>
            <input type="text" class="form-control" id="blood_group" name="blood_group" value="<?php echo htmlspecialchars($donation['blood_group']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="quantity" class="form-label">Quantity (ml)</label>
            <input type="number" class="form-control" id="quantity" name="quantity" value="<?php echo htmlspecialchars($donation['quantity']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="location" class="form-label">Location</label>
            <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($donation['location']); ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Donation</button>
        <a href="index.php?page=donation" class="btn btn-secondary">Cancel</a>
    </form>
</div>
