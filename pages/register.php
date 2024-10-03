<?php
// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $division = $_POST['division'];
    $district = $_POST['district'];
    $location = $_POST['location'];
    $phone = $_POST['phone'];
    $blood_group = $_POST['blood_group'];
    //$role = $_POST['role'];
    $role = 'donor';

    // Validate inputs
    $errors = [];
    if (empty($username)) {
        $errors[] = "Username is required";
    }
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    if (empty($division)) {
        $errors[] = "Division is required";
    }
    if (empty($district)) {
        $errors[] = "District is required";
    }
    if (empty($location)) {
        $errors[] = "Specific location is required";
    }
    if (empty($phone)) {
        $errors[] = "Phone number is required";
    } elseif (strlen($phone) !== 11 || !ctype_digit($phone)) {
        $errors[] = "Phone number must be 11 digits";
    } elseif (phone_number_exists($phone)) {
        $errors[] = "Your phone number already exists in our system";
    }
    if (empty($blood_group)) {
        $errors[] = "Blood group is required";
    }

    // If no errors, proceed with registration
    if (empty($errors)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert user into the database
        $query = "INSERT INTO users (username, email, password, division, district, location, phone, blood_group, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssssssss", $username, $email, $hashed_password, $division, $district, $location, $phone, $blood_group, $role);

        if (mysqli_stmt_execute($stmt)) {
            // Registration successful
            $_SESSION['success_message'] = "Registration successful. You can now log in.";
            header("Location: index.php?page=login");
            exit();
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
        mysqli_stmt_close($stmt);
    }
}

// Fetch divisions using the get_divisions function
$divisions = get_divisions();

// Fetch districts from the database
$districts_query = "SELECT DISTINCT district FROM bangladesh_locations ORDER BY district";
$districts_result = mysqli_query($conn, $districts_query);

?>

<h2>Register</h2>

<?php
if (!empty($errors)) {
    echo '<div class="alert alert-danger">';
    foreach ($errors as $error) {
        echo '<p>' . htmlspecialchars($error) . '</p>';
    }
    echo '</div>';
}
?>

<form method="post" action="">
    <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" class="form-control" id="username" name="username" required>
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">Email address (optional)</label>
        <input type="email" class="form-control" id="email" name="email">
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
    </div>
    <div class="mb-3">
        <label for="confirm_password" class="form-label">Confirm Password</label>
        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
    </div>
    <div class="mb-3">
        <label for="division" class="form-label">Division for Blood Donation</label>
        <select class="form-select" id="division" name="division" required>
            <option value="">Select a division</option>
            <?php
            foreach ($divisions as $division) {
                echo '<option value="' . htmlspecialchars($division) . '">' . htmlspecialchars($division) . '</option>';
            }
            ?>
        </select>
    </div>
    <div class="mb-3">
        <label for="district" class="form-label">District</label>
        <select class="form-select" id="district" name="district" required>
            <option value="">Select a district</option>
            <?php
            while ($district = mysqli_fetch_assoc($districts_result)) {
                echo '<option value="' . htmlspecialchars($district['district']) . '">' . htmlspecialchars($district['district']) . '</option>';
            }
            ?>
        </select>
    </div>
    <div class="mb-3">
        <label for="location" class="form-label">Specific Location</label>
        <input type="text" class="form-control" id="location" name="location" placeholder="Enter your specific location" required>
    </div>
    <div class="mb-3">
        <label for="phone" class="form-label">Phone Number</label>
        <input type="tel" class="form-control" id="phone" name="phone" required pattern="[0-9]{11}" placeholder="Enter your phone number" title="Phone number must be 11 digits">
    </div>
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
        <label for="role" class="form-label" >Role</label>
        <select class="form-select" id="role" name="role" required disabled>
            <option value="donor" selected>Donor</option>
            <option value="recipient">Recipient</option>
            <option value="moderator">Moderator</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Register</button>
</form>

<p class="mt-3">Already have an account? <a href="index.php?page=login">Login here</a></p>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const divisionSelect = document.getElementById('division');
    const districtSelect = document.getElementById('district');

    divisionSelect.addEventListener('change', function() {
        const selectedDivision = this.value;
        if (selectedDivision) {
            fetch(`get_districts.php?division=${selectedDivision}`)
                .then(response => response.json())
                .then(districts => {
                    districtSelect.innerHTML = '<option value="">Select a district</option>';
                    districts.forEach(district => {
                        const option = document.createElement('option');
                        option.value = district;
                        option.textContent = district;
                        districtSelect.appendChild(option);
                    });
                    districtSelect.disabled = false;
                })
                .catch(error => console.error('Error:', error));
        } else {
            districtSelect.innerHTML = '<option value="">Select a district</option>';
            districtSelect.disabled = true;
        }
    });
});
</script>
