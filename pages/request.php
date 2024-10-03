<?php
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo '<div class="alert alert-warning">Please log in to request blood.</div>';
    echo '<p>If you don\'t have an account, please <a href="index.php?page=register">register</a> first.</p>';
} else {
    // Check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Process the blood request form
        $requester_id = $_SESSION['user_id'];
        $blood_group = sanitize_input($_POST['blood_group']);
        $units_needed = sanitize_input($_POST['units_needed']);
        $urgency = sanitize_input($_POST['urgency']);
        $contact_number = sanitize_input($_POST['contact_number']);
        $hospital_name = sanitize_input($_POST['hospital_name']);
        $required_date = sanitize_input($_POST['required_date']);
        $division = sanitize_input($_POST['division']);
        $district = isset($_POST['district']) ? sanitize_input($_POST['district']) : '';

        // Validate inputs
        $errors = [];
        if (empty($blood_group)) {
            $errors[] = "Blood group is required.";
        }
        if (empty($units_needed) || !is_numeric($units_needed) || $units_needed < 1) {
            $errors[] = "Please enter a valid number of units needed.";
        }
        if (empty($urgency)) {
            $errors[] = "Urgency is required.";
        }
        if (empty($contact_number)) {
            $errors[] = "Contact number is required.";
        }
        if (empty($hospital_name)) {
            $errors[] = "Hospital name is required.";
        }
        if (empty($required_date)) {
            $errors[] = "Required date is required.";
        } else {
            $current_date = date('Y-m-d');
            if ($required_date < $current_date) {
                $errors[] = "Required date cannot be in the past.";
            }
        }
        if (empty($division)) {
            $errors[] = "Division is required.";
        }
        if (empty($district)) {
            $errors[] = "District is required.";
        }

        if (empty($errors)) {
            // Create blood request
            $request_data = [
                'requester_id' => $requester_id,
                'blood_group' => $blood_group,
                'units_needed' => $units_needed,
                'urgency' => $urgency,
                'contact_number' => $contact_number,
                'hospital_name' => $hospital_name,
                'required_date' => $required_date,
                'division' => $division,
                'district' => $district,
                'quantity' => $units_needed // Add this line to set the quantity
            ];
            
            $result = create_blood_request($request_data);
            if ($result === true) {
                echo '<div class="alert alert-success">Your blood request has been submitted successfully.</div>';
            } else {
                echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($result) . '</div>';
            }
        } else {
            echo '<div class="alert alert-danger"><ul>';
            foreach ($errors as $error) {
                echo '<li>' . htmlspecialchars($error) . '</li>';
            }
            echo '</ul></div>';
        }
    }
    $divisions = get_divisions();
    // Fetch districts from the database

// Fetch divisions using the get_divisions function
$divisions = get_divisions();

// Fetch districts from the database
$districts_query = "SELECT DISTINCT district FROM bangladesh_locations ORDER BY district";
$districts_result = mysqli_query($conn, $districts_query);

    // Display the blood request form
    ?>
    <h2>Request Blood</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?page=request"); ?>">
        <div class="mb-3">
            <label for="blood_group" class="form-label">Blood Group Needed</label>
            <select class="form-select" id="blood_group" name="blood_group" required>
                <option value="">রক্তের গ্রুপ নির্বাচন করুন</option>
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
            <label for="units_needed" class="form-label">Units Needed</label>
            <input type="number" class="form-control" id="units_needed" name="units_needed" min="1" placeholder="কত ব্যাগ লাগবে?" required>
        </div>
        <div class="mb-3">
            <label for="urgency" class="form-label">Urgency</label>
            <select class="form-select" id="urgency" name="urgency" required>
                <option value="">Select Urgency</option>
                <option value="Low">Low</option>
                <option value="Medium">Medium</option>
                <option value="High">High</option>
                <option value="Critical">Critical</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="contact_number" class="form-label">Contact Number</label>
            <input type="tel" class="form-control" id="contact_number" name="contact_number" placeholder="আপনার ফোন নাম্বার দিন?" required>
        </div>
        <div class="mb-3">
            <label for="division" class="form-label">Division</label>
            <select class="form-select" id="division" name="division" required> 
                <option value="">বিভাগ নির্বাচন করুন</option>
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
                <option value="">District Selection</option>
                <?php
            while ($district = mysqli_fetch_assoc($districts_result)) {
                echo '<option value="' . htmlspecialchars($district['district']) . '">' . htmlspecialchars($district['district']) . '</option>';
            }
            ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="hospital_name" class="form-label">Hospital Name</label>
            <input type="text" class="form-control" id="hospital_name" name="hospital_name" placeholder="আপনার কোন হসপিটালে রক্ত লাগবে?" required>
        </div>
        <div class="mb-3">
            <label for="required_date" class="form-label">Required Date</label>
            <input type="date" class="form-control" id="required_date" name="required_date" placeholder="আপনার কোন তারিখে লাগবে?"  min="<?php echo date('Y-m-d'); ?>" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Submit Request</button>
    </form>


    <?php
}
?>

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