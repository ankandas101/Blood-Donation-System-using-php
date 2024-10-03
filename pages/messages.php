<?php
// Check user access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], [ROLE_ADMIN, ROLE_MODERATOR, ROLE_RECIPIENT])) {
    header("Location: index.php?page=access_denied");
    exit();
}

// Fetch messages from the database
$query = "SELECT * FROM messages ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Database query failed.");
}

// Handle message deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_message'])) {
    $message_id = $_POST['message_id'];
    $delete_query = "DELETE FROM messages WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, "i", $message_id);
    if (mysqli_stmt_execute($stmt)) {
        // Refresh the page to show updated list
        header("Location: index.php?page=messages");
        exit();
    } else {
        echo "Error deleting message.";
    }
}
?>

<div class="container mt-4">
    <h2>Messages</h2>
    
    <?php if (mysqli_num_rows($result) > 0): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Subject</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['subject']); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($row['created_at'])); ?></td>
                        <td>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#messageModal<?php echo $row['id']; ?>">
                                View
                            </button>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="message_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="delete_message" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this message?')">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <!-- Modal for each message -->
                    <div class="modal fade" id="messageModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="messageModalLabel<?php echo $row['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="messageModalLabel<?php echo $row['id']; ?>">Message Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Name:</strong> <?php echo htmlspecialchars($row['name']); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?></p>
                                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($row['phone']); ?></p>
                                    <p><strong>Subject:</strong> <?php echo htmlspecialchars($row['subject']); ?></p>
                                    <p><strong>Date:</strong> <?php echo date('Y-m-d H:i', strtotime($row['created_at'])); ?></p>
                                    <p><strong>Message:</strong></p>
                                    <p><?php echo nl2br(htmlspecialchars($row['message'])); ?></p>

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No messages found.</p>
    <?php endif; ?>
</div>

<?php
mysqli_free_result($result);
?>
