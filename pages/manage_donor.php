<?php
// Check if the user is logged in and has the appropriate role
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'moderator')) {
    echo '<div class="alert alert-warning">Access denied. You must be an admin or moderator to view this page.</div>';
    exit();
}

// Function to get all donors with optional filtering
function getAllDonors($conn, $division = null, $district = null, $phone = null) {
    $query = "SELECT * FROM users WHERE role = 'donor'";
    if ($division) {
        $query .= " AND division = ?";
    }
    if ($district) {
        $query .= " AND district = ?";
    }
    if ($phone) {
        $query .= " AND phone LIKE ?";
    }
    $query .= " ORDER BY id DESC";
    
    $stmt = mysqli_prepare($conn, $query);
    
    if ($division && $district && $phone) {
        $phone_search = "%$phone%";
        mysqli_stmt_bind_param($stmt, "sss", $division, $district, $phone_search);
    } elseif ($division && $district) {
        mysqli_stmt_bind_param($stmt, "ss", $division, $district);
    } elseif ($division && $phone) {
        $phone_search = "%$phone%";
        mysqli_stmt_bind_param($stmt, "ss", $division, $phone_search);
    } elseif ($district && $phone) {
        $phone_search = "%$phone%";
        mysqli_stmt_bind_param($stmt, "ss", $district, $phone_search);
    } elseif ($division) {
        mysqli_stmt_bind_param($stmt, "s", $division);
    } elseif ($district) {
        mysqli_stmt_bind_param($stmt, "s", $district);
    } elseif ($phone) {
        $phone_search = "%$phone%";
        mysqli_stmt_bind_param($stmt, "s", $phone_search);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Function to update donor information including password
function updateDonor($conn, $id, $username, $email, $blood_group, $phone, $division, $district, $password = null) {
    if ($password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $query = "UPDATE users SET username = ?, email = ?, blood_group = ?, phone = ?, division = ?, district = ?, password = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssssssi", $username, $email, $blood_group, $phone, $division, $district, $hashed_password, $id);
    } else {
        $query = "UPDATE users SET username = ?, email = ?, blood_group = ?, phone = ?, division = ?, district = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssssssi", $username, $email, $blood_group, $phone, $division, $district, $id);
    }
    return mysqli_stmt_execute($stmt);
}

// Handle form submission for updating donor information
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_donor'])) {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $blood_group = $_POST['blood_group'];
    $phone = $_POST['phone'];
    $division = $_POST['division'];
    $district = $_POST['district'];
    $password = !empty($_POST['password']) ? $_POST['password'] : null;

    if (updateDonor($conn, $id, $username, $email, $blood_group, $phone, $division, $district, $password)) {
        echo '<div class="alert alert-success">Donor information updated successfully.</div>';
    } else {
        echo '<div class="alert alert-danger">Error updating donor information.</div>';
    }
}

// Handle donor filtering and search
$filter_division = isset($_GET['filter_division']) ? $_GET['filter_division'] : null;
$filter_district = isset($_GET['filter_district']) ? $_GET['filter_district'] : null;
$search_phone = isset($_GET['search_phone']) ? $_GET['search_phone'] : null;

// Get all donors (filtered and searched if applicable)
$donors = getAllDonors($conn, $filter_division, $filter_district, $search_phone);

// Get unique divisions and districts for dropdowns
$divisions = array_unique(array_column($donors, 'division'));
$districts = array_unique(array_column($donors, 'district'));
?>

<div class="container mt-5">
    <h2>Manage Donors</h2>
    
    <!-- Donor Filter and Search Form -->
    <form method="get" action="index.php" class="mb-4">
        <input type="hidden" name="page" value="manage_donor">
        <div class="row">
            <div class="col-md-3">
                <label for="filter_division" class="form-label">Division</label>
                <select class="form-select" id="filter_division" name="filter_division">
                    <option value="">All Divisions</option>
                    <?php foreach ($divisions as $division): ?>
                        <option value="<?php echo htmlspecialchars($division); ?>" <?php echo $filter_division === $division ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($division); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filter_district" class="form-label">District</label>
                <select class="form-select" id="filter_district" name="filter_district">
                    <option value="">All Districts</option>
                    <?php foreach ($districts as $district): ?>
                        <option value="<?php echo htmlspecialchars($district); ?>" <?php echo $filter_district === $district ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($district); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="search_phone" class="form-label">Search by Phone</label>
                <input type="text" class="form-control" id="search_phone" name="search_phone" value="<?php echo htmlspecialchars($search_phone); ?>" placeholder="Enter phone number">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Filter & Search</button>
            </div>
        </div>
    </form>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Blood Group</th>
                <th>Phone</th>
                <th>Division</th>
                <th>District</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($donors as $donor): ?>
                <tr>
                    <td><?php echo htmlspecialchars($donor['id']); ?></td>
                    <td><?php echo htmlspecialchars($donor['username']); ?></td>
                    <td><?php echo htmlspecialchars($donor['email']); ?></td>
                    <td><?php echo htmlspecialchars($donor['blood_group']); ?></td>
                    <td><?php echo htmlspecialchars($donor['phone']); ?></td>
                    <td><?php echo htmlspecialchars($donor['division']); ?></td>
                    <td><?php echo htmlspecialchars($donor['district']); ?></td>
                    <td>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $donor['id']; ?>">
                            Edit
                        </button>
                    </td>
                </tr>
                <!-- Edit Modal -->
                <div class="modal fade" id="editModal<?php echo $donor['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $donor['id']; ?>" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editModalLabel<?php echo $donor['id']; ?>">Edit Donor Information</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form method="post" action="">
                                    <input type="hidden" name="id" value="<?php echo $donor['id']; ?>">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($donor['username']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($donor['email']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="blood_group" class="form-label">Blood Group</label>
                                        <select class="form-select" id="blood_group" name="blood_group" required>
                                            <?php
                                            $blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                                            foreach ($blood_groups as $bg) {
                                                $selected = ($bg === $donor['blood_group']) ? 'selected' : '';
                                                echo "<option value=\"$bg\" $selected>$bg</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Phone</label>
                                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($donor['phone']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="division" class="form-label">Division</label>
                                        <select class="form-select" id="division" name="division" required>
                                            <?php foreach ($divisions as $division): ?>
                                                <option value="<?php echo htmlspecialchars($division); ?>" <?php echo $donor['division'] === $division ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($division); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="district" class="form-label">District</label>
                                        <select class="form-select" id="district" name="district" required>
                                            <?php foreach ($districts as $district): ?>
                                                <option value="<?php echo htmlspecialchars($district); ?>" <?php echo $donor['district'] === $district ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($district); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">New Password (leave blank to keep current)</label>
                                        <input type="password" class="form-control" id="password" name="password">
                                    </div>
                                    <button type="submit" name="update_donor" class="btn btn-primary">Update Donor</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
