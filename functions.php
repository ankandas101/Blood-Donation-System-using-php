<?php

/**
 * Sanitize user input
 * 
 * @param string $input The input to sanitize
 * @return string The sanitized input
 */
function sanitize_input($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Check if user is logged in
 * 
 * @return bool True if user is logged in, false otherwise
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user has a specific role
 * 
 * @param string $role The role to check for
 * @return bool True if user has the specified role, false otherwise
 */
function has_role($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * Redirect user to a specific page
 * 
 * @param string $page The page to redirect to
 */
function redirect($page) {
    header("Location: " . BASE_URL . "index.php?page=" . urlencode($page));
    exit();
}

/**
 * Check user access to the page
 *
 * @param string $required_role The role required to access the page
 * @return bool True if user has access, false otherwise
 */
function check_page_access($required_role) {
    if (!is_logged_in()) {
        redirect('login');
        return false;
    }

    if (!has_role($required_role)) {
        // Instead of redirecting to 'unauthorized', we'll redirect to 'home'
        redirect('home');
        return false;
    }

    return true;
}

/**
 * Get user details by ID
 * 
 * @param int $user_id The user ID
 * @return array|bool User details array or false if not found
 */
function get_user_by_id($user_id) {
    global $conn;
    $user_id = intval($user_id);
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt === false) {
        error_log("Error preparing statement: " . mysqli_error($conn));
        return false;
    }
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    if (!mysqli_stmt_execute($stmt)) {
        error_log("Error executing statement: " . mysqli_stmt_error($stmt));
        return false;
    }
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $user ? $user : false;
}

/**
 * Check if user is eligible to donate blood
 * 
 * @param int $user_id The user ID
 * @return bool True if eligible, false otherwise

function is_eligible_to_donate($user_id) {
    $user = get_user_by_id($user_id);
    if (!$user) return false;

    $age = date_diff(date_create($user['date_of_birth']), date_create('today'))->y;
    if ($age < MIN_AGE_TO_DONATE || $age > MAX_AGE_TO_DONATE) return false;

    if (empty($user['last_donation_date'])) {
        return true;
    }

    $last_donation = new DateTime($user['last_donation_date']);
    $today = new DateTime();
    $days_since_last_donation = $today->diff($last_donation)->days;
    if ($days_since_last_donation < MIN_DONATION_INTERVAL_DAYS) return false;

    return true;
}
 */
/**
 * Register a new user
 * 
 * @param array $user_data The user data
 * @return bool True if registration successful, false otherwise
 */
function register_user($user_data) {
    global $conn;
    $query = "INSERT INTO users (username, email, password, role, blood_group, location, division, district, phone_number, date_of_birth) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt === false) {
        error_log("Error preparing statement: " . mysqli_error($conn));
        return false;
    }
    $password_hash = password_hash($user_data['password'], PASSWORD_DEFAULT);
    mysqli_stmt_bind_param($stmt, "sssissssss", 
        $user_data['username'], 
        $user_data['email'], 
        $password_hash, 
        $user_data['role'],
        $user_data['blood_group'],
        $user_data['location'],
        $user_data['division'],
        $user_data['district'],
        $user_data['phone_number'],
        $user_data['date_of_birth']
    );
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

/**
 * Authenticate user
 * 
 * @param string $username The username
 * @param string $password The password
 * @return array|bool User data if authentication successful, false otherwise
 */
function authenticate_user($username, $password) {
    global $conn;
    $query = "SELECT id, password, role FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt === false) {
        error_log("Error preparing statement: " . mysqli_error($conn));
        return false;
    }
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        return [
            'id' => $user['id'],
            'role' => $user['role']
        ];
    }
    return false;
}

/**
 * Log out the current user
 */
function logout_user() {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

/**
 * Get all blood donation requests
 * 
 * @return array Array of blood donation requests
 */
function get_blood_requests() {
    global $conn;
    $query = "SELECT * FROM blood_request ORDER BY created_at DESC";
    $result = mysqli_query($conn, $query);
    if ($result === false) {
        error_log("Database query failed: " . mysqli_error($conn));
        return [];
    }
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Create a new blood donation request
 * 
 * @param array $request_data The request data
 * @return bool True if request created successfully, false otherwise
 */
function create_blood_request($request_data) {
    global $conn;
    $query = "INSERT INTO blood_request (requester_id, blood_group, quantity, urgency, contact_number, hospital_name, required_date, division, district) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt === false) {
        error_log("Error preparing statement: " . mysqli_error($conn));
        return false;
    }
    mysqli_stmt_bind_param($stmt, "issssssss", 
        $request_data['requester_id'], 
        $request_data['blood_group'], 
        $request_data['quantity'], 
        $request_data['urgency'],
        $request_data['contact_number'],
        $request_data['hospital_name'],
        $request_data['required_date'],
        $request_data['division'],
        $request_data['district']
    );
    $result = mysqli_stmt_execute($stmt);
    if (!$result) {
        error_log("Error executing statement: " . mysqli_stmt_error($stmt));
    }
    return $result;
}

/**
 * Update user profile
 * 
 * @param int $user_id The user ID
 * @param array $profile_data The profile data to update
 * @return bool True if update successful, false otherwise
 */
function update_user_profile($user_id, $profile_data) {
    global $conn;
    $query = "UPDATE users SET ";
    $params = [];
    $types = "";

    foreach ($profile_data as $key => $value) {
        $query .= "$key = ?, ";
        $params[] = $value;
        $types .= "s";
    }

    $query = rtrim($query, ", ") . " WHERE id = ?";
    $params[] = $user_id;
    $types .= "i";

    $stmt = mysqli_prepare($conn, $query);
    if ($stmt === false) {
        error_log("Error preparing statement: " . mysqli_error($conn));
        return false;
    }
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    return mysqli_stmt_execute($stmt);
}

/**
 * Get total number of registered donors
 * 
 * @return int Total number of registered donors
 */
function get_total_donors() {
    global $conn;
    $query = "SELECT COUNT(*) as total FROM users";
    $result = mysqli_query($conn, $query);
    if ($result === false) {
        error_log("Database query failed: " . mysqli_error($conn));
        return 0;
    }
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

/**
 * Get total number of successful donations
 * 
 * @return int Total number of successful donations
 */
function get_successful_donations() {
    global $conn;
    $query = "SELECT COUNT(*) as total FROM donations ";
    $result = mysqli_query($conn, $query);
    if ($result === false) {
        error_log("Database query failed: " . mysqli_error($conn));
        return 0;
    }
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

/**
 * Get estimated number of lives saved
 * 
 * @return int Estimated number of lives saved
 */
function get_estimated_lives_saved() {
    // Assuming each successful donation saves 3 lives on average
    return get_successful_donations() * 3;
}

/**
 * Get blood requests for a specific division and district
 * 
 * @param string $division The division name
 * @param string $district The district name
 * @return array Array of blood requests for the specified division and district
 */
function get_blood_requests_by_location($division, $district) {
    global $conn;
    $query = "SELECT * FROM blood_request WHERE division = ? AND district = ? ORDER BY created_at DESC";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt === false) {
        error_log("Error preparing statement: " . mysqli_error($conn));
        return [];
    }
    mysqli_stmt_bind_param($stmt, "ss", $division, $district);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Get blood requests for a specific division
 * 
 * @param string $division The division name
 * @return array Array of blood requests for the specified division
 */
function get_blood_requests_by_division($division) {
    global $conn;
    $query = "SELECT * FROM blood_request WHERE division = ? ORDER BY created_at DESC";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt === false) {
        error_log("Error preparing statement: " . mysqli_error($conn));
        return [];
    }
    mysqli_stmt_bind_param($stmt, "s", $division);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Get user's donations
 * 
 * @param int $user_id The user ID
 * @return array Array of user's donations
 */
function get_user_donations($user_id) {
    global $conn;
    $query = "SELECT * FROM donations WHERE donor_id = ? ORDER BY donation_date DESC";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt === false) {
        error_log("Error preparing statement: " . mysqli_error($conn));
        return [];
    }
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Validate email
 * 
 * @param string $email The email to validate
 * @return bool True if email is valid, false otherwise
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Generate a random password
 * 
 * @param int $length The length of the password
 * @return string The generated password
 */
function generate_random_password($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $password;
}

/**
 * Check if a username already exists
 * 
 * @param string $username The username to check
 * @return bool True if username exists, false otherwise
 */
function username_exists($username) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    if ($stmt === false) {
        error_log("Error preparing statement: " . mysqli_error($conn));
        return false;
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

/**
 * Check if an email already exists
 * 
 * @param string $email The email to check
 * @return bool True if email exists, false otherwise
 */
function email_exists($email) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    if ($stmt === false) {
        error_log("Error preparing statement: " . mysqli_error($conn));
        return false;
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

/**
 * Check if a phone number already exists
 * 
 * @param string $phone_number The phone number to check
 * @return bool True if phone number exists, false otherwise
 */
function phone_number_exists($phone_number) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE phone_number = ?");
    if ($stmt === false) {
        error_log("Error preparing statement: " . mysqli_error($conn));
        return false;
    }
    $stmt->bind_param("s", $phone_number);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

/**
 * Log an action
 * 
 * @param int $user_id The user ID
 * @param string $action The action to log
 */
function log_action($user_id, $action) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO action_logs (user_id, action, timestamp) VALUES (?, ?, NOW())");
    if ($stmt === false) {
        error_log("Error preparing statement: " . mysqli_error($conn));
        return;
    }
    $stmt->bind_param("is", $user_id, $action);
    $stmt->execute();
}

/**
 * Get user's blood type
 * 
 * @param int $user_id The user ID
 * @return string|null The user's blood type or null if not found
 */
function get_user_blood_type($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT blood_group FROM users WHERE id = ?");
    if ($stmt === false) {
        error_log("Error preparing statement: " . mysqli_error($conn));
        return null;
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['blood_group'];
    }
    return null;
}

/**
 * Format date
 * 
 * @param string $date The date to format
 * @param string $format The format to use
 * @return string The formatted date
 */
function format_date($date, $format = 'Y-m-d H:i:s') {
    return date($format, strtotime($date));
}

/**
 * Calculate age from birthdate
 * 
 * @param string $birthdate The birthdate
 * @return int The calculated age
 */
function calculate_age($birthdate) {
    $today = new DateTime();
    $diff = $today->diff(new DateTime($birthdate));
    return $diff->y;
}

/**
 * Get compatible blood types
 * 
 * @param string $blood_type The blood type
 * @return array Array of compatible blood types
 */
function get_compatible_blood_types($blood_type) {
    $compatibility = [
        'A+' => ['A+', 'A-', 'O+', 'O-'],
        'A-' => ['A-', 'O-'],
        'B+' => ['B+', 'B-', 'O+', 'O-'],
        'B-' => ['B-', 'O-'],
        'AB+' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'],
        'AB-' => ['A-', 'B-', 'AB-', 'O-'],
        'O+' => ['O+', 'O-'],
        'O-' => ['O-']
    ];
    return isset($compatibility[$blood_type]) ? $compatibility[$blood_type] : [];
}

/**
 * Update user's last donation date
 * 
 * @param int $user_id The user ID
 * @param string $donation_date The donation date
 * @return bool True if update successful, false otherwise
 */
function update_last_donation_date($user_id, $donation_date) {
    global $conn;
    $query = "UPDATE users SET last_donation_date = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt === false) {
        error_log("Error preparing statement: " . mysqli_error($conn));
        return false;
    }
    mysqli_stmt_bind_param($stmt, "si", $donation_date, $user_id);
    return mysqli_stmt_execute($stmt);
}

/**
 * Record a new donation
 * 
 * @param array $donation_data The donation data
 * @return bool True if donation recorded successfully, false otherwise
 */
function record_donation($donation_data) {
    global $conn;
    $query = "INSERT INTO donations (donor_id, donation_date, blood_type, amount, division, district) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt === false) {
        error_log("Error preparing statement: " . mysqli_error($conn));
        return false;
    }
    mysqli_stmt_bind_param($stmt, "issdss", 
        $donation_data['user_id'], 
        $donation_data['donation_date'], 
        $donation_data['blood_type'], 
        $donation_data['amount'],
        $donation_data['division'],
        $donation_data['district']
    );
    $result = mysqli_stmt_execute($stmt);
    
    if ($result) {
        // Update the user's last donation date
        update_last_donation_date($donation_data['user_id'], $donation_data['donation_date']);
    }
    
    return $result;
}

/**
 * Get available blood units from user registrations
 * 
 * @param string $division The division to filter by (optional)
 * @param string $district The district to filter by (optional)
 * @return array Array of available blood units
 */
function get_available_blood_units($division = null, $district = null) {
    global $conn;
    $query = "SELECT blood_group, COUNT(*) as count FROM users WHERE 1=1";
    $params = [];
    $types = "";

    if ($division) {
        $query .= " AND division = ?";
        $params[] = $division;
        $types .= "s";
    }

    if ($district) {
        $query .= " AND district = ?";
        $params[] = $district;
        $types .= "s";
    }

    $query .= " GROUP BY blood_group";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt === false) {
        error_log("Error preparing statement: " . mysqli_error($conn));
        return array(
            'A+' => 0, 'A-' => 0, 'B+' => 0, 'B-' => 0,
            'AB+' => 0, 'AB-' => 0, 'O+' => 0, 'O-' => 0
        );
    }

    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (!$result) {
        // Handle query error
        error_log("Database query failed: " . mysqli_error($conn));
        return array(
            'A+' => 0, 'A-' => 0, 'B+' => 0, 'B-' => 0,
            'AB+' => 0, 'AB-' => 0, 'O+' => 0, 'O-' => 0
        );
    }
    
    $available_blood = array(
        'A+' => 0, 'A-' => 0, 'B+' => 0, 'B-' => 0,
        'AB+' => 0, 'AB-' => 0, 'O+' => 0, 'O-' => 0
    );
    while ($row = mysqli_fetch_assoc($result)) {
        if (isset($available_blood[$row['blood_group']])) {
            $available_blood[$row['blood_group']] = $row['count'];
        }
    }
    return $available_blood;
}

/**
 * Get all divisions
 * 
 * @return array Array of divisions
 */
function get_divisions() {
    global $conn;
    $query = "SELECT DISTINCT division FROM bangladesh_locations ORDER BY division";
    $result = mysqli_query($conn, $query);
    if ($result === false) {
        error_log("Database query failed: " . mysqli_error($conn));
        return [];
    }
    $divisions = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $divisions[] = $row['division'];
    }
    return $divisions;
}

/**
 * Get districts for a specific division
 * 
 * @param string $division The division name
 * @return array Array of districts
 */
function get_districts($division) {
    global $conn;
    $query = "SELECT DISTINCT district FROM bangladesh_locations WHERE division = ? ORDER BY district";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt === false) {
        error_log("Error preparing statement: " . mysqli_error($conn));
        return [];
    }
    mysqli_stmt_bind_param($stmt, "s", $division);
    if (!mysqli_stmt_execute($stmt)) {
        error_log("Error executing statement: " . mysqli_stmt_error($stmt));
        return [];
    }
    $result = mysqli_stmt_get_result($stmt);
    $districts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $districts[] = $row['district'];
    }
    return $districts;
}

/**
 * Add a new district to a division
 * 
 * @param string $division The division name
 * @param string $district The district name
 * @return bool True if district added successfully, false otherwise
 */
function add_district($division, $district) {
    global $conn;
    $query = "INSERT INTO bangladesh_locations (division, district) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt === false) {
        error_log("Error preparing statement: " . mysqli_error($conn));
        return false;
    }
    mysqli_stmt_bind_param($stmt, "ss", $division, $district);
    return mysqli_stmt_execute($stmt);
}

/**
 * Remove a district from a division
 * 
 * @param string $division The division name
 * @param string $district The district name
 * @return bool True if district removed successfully, false otherwise
 */
function remove_district($division, $district) {
    global $conn;
    $query = "DELETE FROM bangladesh_locations WHERE division = ? AND district = ?";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt === false) {
        error_log("Error preparing statement: " . mysqli_error($conn));
        return false;
    }
    mysqli_stmt_bind_param($stmt, "ss", $division, $district);
    return mysqli_stmt_execute($stmt);
}

/**
 * Get user profile
 * 
 * @param int $user_id The user ID
 * @return array|bool User profile data or false if not found
 */
function get_user_profile($user_id) {
    global $conn;
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt === false) {
        error_log("Error preparing statement: " . mysqli_error($conn));
        return false;
    }
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    if (!mysqli_stmt_execute($stmt)) {
        error_log("Error executing statement: " . mysqli_stmt_error($stmt));
        return false;
    }
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

/**
 * Get total number of blood requests
 * 
 * @return int Total number of blood requests
 */
function get_total_requests() {
    global $conn;
    $query = "SELECT COUNT(*) as total FROM blood_request";
    $result = mysqli_query($conn, $query);
    if ($result === false) {
        error_log("Database query failed: " . mysqli_error($conn));
        return 0;
    }
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

/**
 * Search for donors based on criteria
 *
 * @param array $criteria The search criteria
 * @return array Array of matching donors
 */
function searchDonors($criteria) {
    global $conn;
    $query = "SELECT * FROM users WHERE role = 'donor'";
    $params = [];
    $types = "";

    if (!empty($criteria['blood_group'])) {
        $query .= " AND blood_group = ?";
        $params[] = $criteria['blood_group'];
        $types .= "s";
    }

    if (!empty($criteria['division'])) {
        $query .= " AND division = ?";
        $params[] = $criteria['division'];
        $types .= "s";
    }

    if (!empty($criteria['district'])) {
        $query .= " AND district = ?";
        $params[] = $criteria['district'];
        $types .= "s";
    }

    $stmt = mysqli_prepare($conn, $query);
    if ($stmt === false) {
        error_log("Error preparing statement: " . mysqli_error($conn));
        return [];
    }

    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    if (!mysqli_stmt_execute($stmt)) {
        error_log("Error executing statement: " . mysqli_stmt_error($stmt));
        return [];
    }

    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}





/**
 * Check if a user is eligible to donate blood
 *
 * @param int $user_id The ID of the user to check
 * @return bool True if eligible, false otherwise
 */
function is_eligible_to_donate($user_id) {
    global $conn;
    
    // First, check if the user exists and is a donor
    $user_query = "SELECT * FROM users WHERE id = ? AND role = 'donor'";
    $user_stmt = mysqli_prepare($conn, $user_query);
    
    if ($user_stmt === false) {
        error_log("Error preparing user statement: " . mysqli_error($conn));
        return false;
    }
    
    mysqli_stmt_bind_param($user_stmt, "i", $user_id);
    
    if (!mysqli_stmt_execute($user_stmt)) {
        error_log("Error executing user statement: " . mysqli_stmt_error($user_stmt));
        return false;
    }
    
    $user_result = mysqli_stmt_get_result($user_stmt);
    $user = mysqli_fetch_assoc($user_result);
    
    if (!$user) {
        return false; // User not found or not a donor
    }
    
    // Now check the last donation date
    $donation_query = "SELECT MAX(donation_date) as donation_date FROM donations WHERE donor_id = ?";
    $donation_stmt = mysqli_prepare($conn, $donation_query);
    
    if ($donation_stmt === false) {
        error_log("Error preparing donation statement: " . mysqli_error($conn));
        return false;
    }
    
    mysqli_stmt_bind_param($donation_stmt, "i", $user_id);
    
    if (!mysqli_stmt_execute($donation_stmt)) {
        error_log("Error executing donation statement: " . mysqli_stmt_error($donation_stmt));
        return false;
    }
    
    $donation_result = mysqli_stmt_get_result($donation_stmt);
    $last_donation = mysqli_fetch_assoc($donation_result);
    
    if ($last_donation['donation_date'] === null) {
        return true; // No previous donations, so eligible
    }
    
    // Calculate the difference between now and the last donation
    $last_donation_date = new DateTime($last_donation['donation_date']);
    $now = new DateTime();
    $interval = $now->diff($last_donation_date);
    
    // Check if it's been more than 60 days
    return $interval->days > 60;
}




?>
