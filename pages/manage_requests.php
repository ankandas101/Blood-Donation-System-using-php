<?php
// Check if the user is logged in and has admin or moderator role
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != ROLE_ADMIN && $_SESSION['role'] != ROLE_MODERATOR)) {
    header("Location: index.php?page=login");
    exit();
}

// Function to update request status
function updateRequestStatus($conn, $request_id, $new_status) {
    $query = "UPDATE blood_request SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt === false) {
        error_log("Error preparing statement: " . mysqli_error($conn));
        return false;
    }
    mysqli_stmt_bind_param($stmt, "si", $new_status, $request_id);
    $result = mysqli_stmt_execute($stmt);
    if (!$result) {
        error_log("Error executing statement: " . mysqli_stmt_error($stmt));
    }
    mysqli_stmt_close($stmt);
    return $result;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_id']) && isset($_POST['new_status'])) {
    $request_id = intval($_POST['request_id']);
    $new_status = $_POST['new_status'];
    if (updateRequestStatus($conn, $request_id, $new_status)) {
        $success_message = "Request status updated successfully.";
    } else {
        $error_message = "Failed to update request status.";
    }
}

// Function to get all blood requests with requester username, filtered by division, district, and status
function get_blood_requests_with_username($conn, $division = null, $district = null, $status = null) {
    $query = "SELECT br.*, u.username 
              FROM blood_request br 
              JOIN users u ON br.requester_id = u.id 
              WHERE 1=1";
    
    if ($division) {
        $query .= " AND br.division = '" . mysqli_real_escape_string($conn, $division) . "'";
    }
    
    if ($district) {
        $query .= " AND br.district = '" . mysqli_real_escape_string($conn, $district) . "'";
    }
    
    if ($status) {
        $query .= " AND br.status = '" . mysqli_real_escape_string($conn, $status) . "'";
    }
    
    $query .= " ORDER BY br.created_at DESC";
    
    $result = mysqli_query($conn, $query);
    if ($result === false) {
        error_log("Database query failed: " . mysqli_error($conn));
        return [];
    }
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Get unique divisions, districts, and statuses
$divisions_query = "SELECT DISTINCT division FROM blood_request ORDER BY division";
$districts_query = "SELECT DISTINCT district FROM blood_request ORDER BY district";
$statuses_query = "SELECT DISTINCT status FROM blood_request ORDER BY status";
$divisions = mysqli_query($conn, $divisions_query);
$districts = mysqli_query($conn, $districts_query);
$statuses = mysqli_query($conn, $statuses_query);

// Get filter values
$filter_division = isset($_GET['division']) ? $_GET['division'] : null;
$filter_district = isset($_GET['district']) ? $_GET['district'] : null;
$filter_status = isset($_GET['status']) ? $_GET['status'] : null;

// Fetch all blood requests with requester username, filtered by division, district, and status
$blood_requests = get_blood_requests_with_username($conn, $filter_division, $filter_district, $filter_status);
?>

<div class="container mt-5">
    <h2 class="mb-4">Manage Blood Requests</h2>

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

    <form method="GET" class="mb-4">
        <input type="hidden" name="page" value="manage_requests">
        <div class="row">
            <div class="col-md-3">
                <select name="division" class="form-select">
                    <option value="">All Divisions</option>
                    <?php while ($row = mysqli_fetch_assoc($divisions)): ?>
                        <option value="<?php echo htmlspecialchars($row['division']); ?>" <?php echo ($filter_division == $row['division']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($row['division']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="district" class="form-select">
                    <option value="">All Districts</option>
                    <?php while ($row = mysqli_fetch_assoc($districts)): ?>
                        <option value="<?php echo htmlspecialchars($row['district']); ?>" <?php echo ($filter_district == $row['district']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($row['district']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <?php while ($row = mysqli_fetch_assoc($statuses)): ?>
                        <option value="<?php echo htmlspecialchars($row['status']); ?>" <?php echo ($filter_status == $row['status']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($row['status']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Requester</th>
                <th>Blood Group</th>
                <th>Quantity</th>
                <th>Hospital</th>
                <th>Date</th>
                <th>Division</th>
                <th>District</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($blood_requests as $request): ?>
                <tr>
                    <td><?php echo $request['id']; ?></td>
                    <td>
                        <?php echo htmlspecialchars($request['username']); ?><br>
                        <small><?php echo htmlspecialchars($request['contact_number']); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($request['blood_group']); ?></td>
                    <td><?php echo $request['quantity']; ?></td>
                    <td><?php echo htmlspecialchars($request['hospital_name']); ?></td>
                    <td><?php echo date('Y-m-d', strtotime($request['required_date'])); ?></td>
                    <td><?php echo htmlspecialchars($request['division']); ?></td>
                    <td><?php echo htmlspecialchars($request['district']); ?></td>
                    <td <?php echo ($request['status'] == 'Approved') ? 'style="color: green; background-color: #ccffcc;"' : (($request['status'] == 'Pending') ? 'style="color: blue; background-color: #cce5ff;"' : 'style="color: red; background-color: #ffcccc;"'); ?>><?php echo htmlspecialchars($request['status']); ?></td>
                    <td>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                            <select name="new_status" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Change Status</option>
                                <option value="Pending" <?php echo ($request['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="Approved" <?php echo ($request['status'] == 'Approved') ? 'selected' : ''; ?>>Approved</option>
                                <option value="Fulfilled" <?php echo ($request['status'] == 'Fulfilled') ? 'selected' : ''; ?>>Fulfilled</option>
                                <option value="Cancelled" <?php echo ($request['status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>