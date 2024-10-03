<?php
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo '<div class="alert alert-warning">Please log in to donate blood.</div>';
    echo '<p>If you don\'t have an account, please <a href="index.php?page=register">register</a> first.</p>';
} else {
    // Fetch the user's blood group from the database
    $user_id = $_SESSION['user_id'];
    $query = "SELECT blood_group FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    
    if ($stmt === false) {
        echo '<div class="alert alert-danger">Error preparing statement: ' . mysqli_error($conn) . '</div>';
    } else {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $user_blood_group);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
    }

    // Check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Process the donation form
        $donation_date = sanitize_input($_POST['donation_date']);
        $blood_group = $user_blood_group; // Use the user's blood group
        $amount = sanitize_input($_POST['amount']);
        $location = isset($_POST['location']) ? sanitize_input($_POST['location']) : '';

        // Validate inputs
        if (empty($donation_date) || empty($blood_group) || empty($amount) || empty($location)) {
            echo '<div class="alert alert-danger">Please fill in all fields.</div>';
        } else {
            // Insert donation record into the database
            $query = "INSERT INTO donations (donor_id, donation_date, blood_group, quantity, location) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            
            if ($stmt === false) {
                echo '<div class="alert alert-danger">Error preparing statement: ' . mysqli_error($conn) . '</div>';
            } else {
                mysqli_stmt_bind_param($stmt, "issis", $user_id, $donation_date, $blood_group, $amount, $location);
                
                if (mysqli_stmt_execute($stmt)) {
                    echo '<div class="alert alert-success">Thank you for your donation!</div>';
                } else {
                    echo '<div class="alert alert-danger">Error executing statement: ' . mysqli_stmt_error($stmt) . '</div>';
                }
                mysqli_stmt_close($stmt);
            }
        }
    }

    // Display the donation form
    ?>
    <h2>Update your Last Blood Donation Data</h2>
    <form method="post" action="">
        <div class="mb-3">
            <label for="donation_date" class="form-label">Donation Date</label>
            <input type="date" class="form-control" id="donation_date" name="donation_date" placeholder="আপনি রক্ত দিয়েছেন কবে?" required>
        </div>
        <div class="mb-3">
            <label for="blood_group" class="form-label">Blood Group (From Your Profile)</label>
            <input type="text" class="form-control" id="blood_group" name="blood_group" value="<?php echo htmlspecialchars($user_blood_group); ?>" readonly>
        </div>
        <div class="mb-3">
            <label for="amount" class="form-label">Amount (in Bags)</label>
            <input type="number" class="form-control" id="amount" name="amount" min="1" max="500" placeholder="আপনি কত ব্যাগ দিয়েছেন?" required>
        </div>
        <div class="mb-3">
            <label for="location" class="form-label">Donation Location</label>
            <input type="text" class="form-control" id="location" name="location" placeholder="আপনি কোথায় / কোন হসপিটালে রক্ত দিয়েছেন?" required>
        </div>
        <button type="submit" class="btn btn-primary">Submit Donation</button>
    </form>
    <?php
}
?>
