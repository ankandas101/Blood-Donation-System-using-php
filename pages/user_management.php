<?php
// Check if the user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != ROLE_ADMIN) {
    header("Location: index.php?page=login");
    exit();
}

// Function to get all users
function getAllUsers($conn) {
    $query = "SELECT * FROM users ORDER BY id ASC";
    $result = mysqli_query($conn, $query);
    return $result;
}

// Function to update user information
function updateUser($conn, $id, $username, $email, $role, $phone, $division, $district, $password = null) {
    if ($password) {
        $query = "UPDATE users SET username = ?, email = ?, role = ?, phone = ?, division = ?, district = ?, password = ? WHERE id = ?";
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssssssi", $username, $email, $role, $phone, $division, $district, $hashed_password, $id);
    } else {
        $query = "UPDATE users SET username = ?, email = ?, role = ?, phone = ?, division = ?, district = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssssssi", $username, $email, $role, $phone, $division, $district, $id);
    }
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

// Handle user update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $phone = $_POST['phone'];
    $division = $_POST['division'];
    $district = $_POST['district'];
    $password = !empty($_POST['password']) ? $_POST['password'] : null;
    
    if (updateUser($conn, $user_id, $username, $email, $role, $phone, $division, $district, $password)) {
        $success_message = "User updated successfully.";
    } else {
        $error_message = "Failed to update user.";
    }
}

// Get all users
$users = getAllUsers($conn);

// Handle search
if (isset($_GET['search_phone'])) {
    $search_phone = $_GET['search_phone'];
    $query = "SELECT * FROM users WHERE phone LIKE ? ORDER BY id ASC";
    $stmt = mysqli_prepare($conn, $query);
    $search_param = "%$search_phone%";
    mysqli_stmt_bind_param($stmt, "s", $search_param);
    mysqli_stmt_execute($stmt);
    $users = mysqli_stmt_get_result($stmt);
}

// Display success or error message
if (isset($success_message)) {
    echo '<div class="alert alert-success">' . $success_message . '</div>';
} elseif (isset($error_message)) {
    echo '<div class="alert alert-danger">' . $error_message . '</div>';
}

// Display search form
echo '<h2>User Management</h2>';
echo '<form method="GET" class="mb-3">
        <input type="hidden" name="page" value="user_management">
        <div class="input-group">
            <input type="text" class="form-control" name="search_phone" placeholder="Search by phone number" value="' . (isset($_GET['search_phone']) ? htmlspecialchars($_GET['search_phone']) : '') . '">
            <button class="btn btn-outline-secondary" type="submit">Search</button>
        </div>
      </form>';

// Display user management table
echo '<table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Phone</th>
                <th>Division</th>
                <th>District</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>';

while ($user = mysqli_fetch_assoc($users)) {
    echo '<tr>
            <td>' . $user['id'] . '</td>
            <td>' . htmlspecialchars($user['username']) . '</td>
            <td>' . htmlspecialchars($user['email']) . '</td>
            <td>' . htmlspecialchars($user['role']) . '</td>
            <td>' . htmlspecialchars($user['phone']) . '</td>
            <td>' . htmlspecialchars($user['division']) . '</td>
            <td>' . htmlspecialchars($user['district']) . '</td>
            <td>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal' . $user['id'] . '">
                    Edit
                </button>
            </td>
          </tr>';
    
    // Edit User Modal
    echo '<div class="modal fade" id="editModal' . $user['id'] . '" tabindex="-1" aria-labelledby="editModalLabel' . $user['id'] . '" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel' . $user['id'] . '">Edit User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="" method="post">
                        <div class="modal-body">
                            <input type="hidden" name="user_id" value="' . $user['id'] . '">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" value="' . htmlspecialchars($user['username']) . '" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="' . htmlspecialchars($user['email']) . '" required>
                            </div>
                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="user"' . ($user['role'] == 'user' ? ' selected' : '') . '>User</option>
                                    <option value="donor"' . ($user['role'] == 'donor' ? ' selected' : '') . '>Donor</option>
                                    <option value="moderator"' . ($user['role'] == 'moderator' ? ' selected' : '') . '>Moderator</option>
                                    <option value="admin"' . ($user['role'] == 'admin' ? ' selected' : '') . '>Admin</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="' . htmlspecialchars($user['phone']) . '" required>
                            </div>
                            <div class="mb-3">
                                <label for="division" class="form-label">Division</label>
                                <input type="text" class="form-control" id="division" name="division" value="' . htmlspecialchars($user['division']) . '" required>
                            </div>
                            <div class="mb-3">
                                <label for="district" class="form-label">District</label>
                                <input type="text" class="form-control" id="district" name="district" value="' . htmlspecialchars($user['district']) . '" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">New Password (leave blank to keep current)</label>
                                <input type="password" class="form-control" id="password" name="password">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" name="update_user">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
          </div>';
}

echo '</tbody></table>';
?>
