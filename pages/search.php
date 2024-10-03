<?php
// Function to search for donors
function search_donors($division, $blood_group) {
    global $conn;
    if (!$conn) {
        error_log("Database connection is not established.");
        return [];
    }
    $query = "SELECT * FROM users WHERE division = ? AND blood_group = ?";
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        error_log("Failed to prepare statement: " . mysqli_error($conn));
        return [];
    }
    mysqli_stmt_bind_param($stmt, "ss", $division, $blood_group);
    if (!mysqli_stmt_execute($stmt)) {
        error_log("Failed to execute statement: " . mysqli_stmt_error($stmt));
        return [];
    }
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

$search_results = [];
$division = '';
$blood_group = '';

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['division']) && isset($_GET['blood_group'])) {
    $division = $_GET['division'];
    $blood_group = $_GET['blood_group'];
    $search_results = search_donors($division, $blood_group);
}
?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Search for Blood Donors</h2>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="get" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="mb-4">
                <input type="hidden" name="page" value="search">
                <div class="row g-3">
                    <div class="col-md-5">
                        <select class="form-select" name="division" required>
                            <option value="">Select Division</option>
                            <?php
                            $divisions = ['Dhaka', 'Chittagong', 'Rajshahi', 'Khulna', 'Barisal', 'Sylhet', 'Rangpur', 'Mymensingh'];
                            foreach ($divisions as $div) {
                                echo "<option value=\"$div\"" . ($division == $div ? ' selected' : '') . ">$div</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <select class="form-select" name="blood_group" required>
                            <option value="">Select Blood Group</option>
                            <?php
                            $blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                            foreach ($blood_groups as $bg) {
                                echo "<option value=\"$bg\"" . ($blood_group == $bg ? ' selected' : '') . ">$bg</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($search_results)): ?>
        <div class="mt-5">
            <h3 class="mb-4">Search Results</h3>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Username</th>
                            <th>Blood Group</th>
                            <th>Division</th>
                            <th>Contact</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($search_results as $donor): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($donor['username']); ?></td>
                                <td><span class="badge bg-danger"><?php echo htmlspecialchars($donor['blood_group']); ?></span></td>
                                <td><?php echo htmlspecialchars($donor['division']); ?></td>
                                <td><?php echo htmlspecialchars($donor['phone']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['division']) && isset($_GET['blood_group'])): ?>
        <div class="alert alert-info mt-4" role="alert">
            <i class="bi bi-info-circle me-2"></i>No donors found matching your search criteria.
        </div>
    <?php endif; ?>
</div>
