<?php
// Check if the user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != ROLE_ADMIN) {
    header("Location: index.php?page=login");
    exit();
}

// Function to add a new notice
function addNotice($conn, $notice_text, $is_active) {
    $query = "INSERT INTO notices (notice_text, is_active, created_at) VALUES (?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "si", $notice_text, $is_active);
    return mysqli_stmt_execute($stmt);
}

// Function to update a notice
function updateNotice($conn, $notice_id, $notice_text, $is_active) {
    $query = "UPDATE notices SET notice_text = ?, is_active = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sii", $notice_text, $is_active, $notice_id);
    return mysqli_stmt_execute($stmt);
}

// Function to delete a notice
function deleteNotice($conn, $notice_id) {
    $query = "DELETE FROM notices WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $notice_id);
    return mysqli_stmt_execute($stmt);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_notice'])) {
        $notice_text = $_POST['notice_text'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        if (addNotice($conn, $notice_text, $is_active)) {
            $success_message = "Notice added successfully.";
        } else {
            $error_message = "Failed to add notice.";
        }
    } elseif (isset($_POST['update_notice'])) {
        $notice_id = $_POST['notice_id'];
        $notice_text = $_POST['notice_text'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        if (updateNotice($conn, $notice_id, $notice_text, $is_active)) {
            $success_message = "Notice updated successfully.";
        } else {
            $error_message = "Failed to update notice.";
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

// Fetch all notices
$notices_query = "SELECT * FROM notices ORDER BY created_at DESC";
$notices_result = mysqli_query($conn, $notices_query);
?>

<div class="container mt-5">
    <h2 class="mb-4">Manage Notices</h2>

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

    <h3 class="mt-4 mb-3">Add New Notice</h3>
    <form method="POST">
        <div class="mb-3">
            <label for="notice_text" class="form-label">Notice Text</label>
            <textarea class="form-control" id="notice_text" name="notice_text" rows="3" required></textarea>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" checked>
            <label class="form-check-label" for="is_active">Active</label>
        </div>
        <button type="submit" name="add_notice" class="btn btn-primary">Add Notice</button>
    </form>

    <h3 class="mt-5 mb-3">Current Notices</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Notice</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($notice = mysqli_fetch_assoc($notices_result)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($notice['notice_text']); ?></td>
                    <td><?php echo $notice['is_active'] ? 'Active' : 'Inactive'; ?></td>
                    <td><?php echo htmlspecialchars($notice['created_at']); ?></td>
                    <td>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $notice['id']; ?>">
                            Edit
                        </button>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this notice?');">
                            <input type="hidden" name="notice_id" value="<?php echo $notice['id']; ?>">
                            <button type="submit" name="delete_notice" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
                <!-- Edit Modal -->
                <div class="modal fade" id="editModal<?php echo $notice['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $notice['id']; ?>" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editModalLabel<?php echo $notice['id']; ?>">Edit Notice</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form method="POST">
                                <div class="modal-body">
                                    <input type="hidden" name="notice_id" value="<?php echo $notice['id']; ?>">
                                    <div class="mb-3">
                                        <label for="edit_notice_text<?php echo $notice['id']; ?>" class="form-label">Notice Text</label>
                                        <textarea class="form-control" id="edit_notice_text<?php echo $notice['id']; ?>" name="notice_text" rows="3" required><?php echo htmlspecialchars($notice['notice_text']); ?></textarea>
                                    </div>
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="edit_is_active<?php echo $notice['id']; ?>" name="is_active" <?php echo $notice['is_active'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="edit_is_active<?php echo $notice['id']; ?>">Active</label>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" name="update_notice" class="btn btn-primary">Save changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>




