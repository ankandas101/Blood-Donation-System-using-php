<?php
$recent_requests = get_blood_requests();

$available_blood = get_available_blood_units();

// Function to search for donors
function search_donors($division, $blood_group) {
    global $conn;
    $query = "SELECT username, blood_group, division, phone FROM users WHERE division = ? AND blood_group = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $division, $blood_group);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

$search_results = [];
$search_performed = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search_donors'])) {
    $division = $_POST['division'];
    $blood_group = $_POST['blood_group'];
    $search_results = search_donors($division, $blood_group);
    $search_performed = true;
}

// Sort recent requests by date and urgency
usort($recent_requests, function($a, $b) {
    $date_compare = strtotime($b['created_at']) - strtotime($a['created_at']);
    if ($date_compare == 0) {
        $urgency_order = ['Critical' => 1, 'High' => 2, 'Medium' => 3, 'Low' => 4];
        return $urgency_order[$a['urgency']] - $urgency_order[$b['urgency']];
    }
    return $date_compare;
});

// Limit to latest 10 requests
$recent_requests = array_slice($recent_requests, 0, 10);

// Fetch notices
$notices_query = "SELECT notice_text FROM notices WHERE is_active = 1 ORDER BY created_at DESC LIMIT 5";
$notices_result = mysqli_query($conn, $notices_query);
$notices = mysqli_fetch_all($notices_result, MYSQLI_ASSOC);

?>

<?php if (!empty($notices)): ?>
<!-- Scrolling Notice -->
<div class="alert alert-info" role="alert" style="overflow: hidden;">
  <div style="display: flex; align-items: center;">
    <strong style="white-space: nowrap; background-color: #f8d7da; padding: 5px; border-radius: 5px;">Notice:</strong>
    <div class="scrolling-text" style="white-space: nowrap; overflow: hidden; margin-left: 10px;">       
      <span style="display: inline-block; padding-left: 0%; animation: scroll-left 20s linear infinite;">
        <?php foreach ($notices as $notice): ?>
            <span class="me-4"><?php echo htmlspecialchars($notice['notice_text']); ?></span>
        <?php endforeach; ?>
      </span>
    </div>
  </div>
</div>

<style>
@keyframes scroll-left {
    0% { transform: translateX(0); }
    100% { transform: translateX(-100%); }
}
</style>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <h2>Welcome to <?php echo SITE_NAME; ?></h2>
        <p>We connect blood donors with those in need. Your donation can save lives!</p>

        <img class="pic_bg img-fluid" src="images/blood-donater.jpg" alt="Blood Donation">

        <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="mt-4">
                <a href="index.php?page=register" class="btn btn-primary me-2">Register</a>
                <a href="index.php?page=login" class="btn btn-secondary">Login</a>
            </div>
        <?php else: ?>
            <div class="mt-4">
                <a href="index.php?page=donate" class="btn btn-primary me-2">Donate Blood</a>
                <a href="index.php?page=request" class="btn btn-secondary">Request Blood</a>
            </div>
        <?php endif; ?>
    </div>
    <div class="col-md-4">
        <h3>Blood Availability</h3>
        <ul class="list-group">
            <?php foreach ($available_blood as $blood_type => $units): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <?php echo htmlspecialchars($blood_type); ?>
                    <span class="badge bg-primary rounded-pill"><?php echo intval($units); ?> donors</span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<div class="row mt-5">
    <div class="search_blood col-md-12" style="background-color: #f8d7da; padding: 20px; border-radius: 8px;">
        <h3>Search for Donors</h3>
        <form method="post" action="" class="mb-3">
            <div class="input-group">
                <select class="form-select bg-white text-dark" name="division" required>
                    <option value="">Select Division</option>
                    <option value="Dhaka">Dhaka</option>
                    <option value="Chittagong">Chittagong</option>
                    <option value="Rajshahi">Rajshahi</option>
                    <option value="Khulna">Khulna</option>
                    <option value="Barisal">Barisal</option>
                    <option value="Sylhet">Sylhet</option>
                    <option value="Rangpur">Rangpur</option>
                    <option value="Mymensingh">Mymensingh</option>
                </select>
                <select class="form-select bg-white text-dark" name="blood_group" required>
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
                <button type="submit" name="search_donors" class="btn btn-danger">Search Donors</button>
            </div>
        </form>
    </div>
</div>

<?php if ($search_performed): ?>
    <?php if (!empty($search_results)): ?>
        <div class="row mt-3">
            <div class="col-md-12">
                <h4>Search Results</h4>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Blood Group</th>
                                <th>Location</th>
                                <th>Phone Number</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($search_results as $donor): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($donor['username']); ?></td>
                                    <td><?php echo htmlspecialchars($donor['blood_group']); ?></td>
                                    <td><?php echo htmlspecialchars($donor['division']); ?></td>
                                    <td><?php echo htmlspecialchars($donor['phone']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="alert alert-info">No donors found for the selected criteria.</div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<div class="row mt-5">
    <div class="col-md-12">
        <h3>Recent Blood Requests</h3>
        <?php if (!empty($recent_requests) && is_array($recent_requests)): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Blood Group</th>
                            <th>Division</th>
                            <th>District</th>
                            <th>Units Needed</th>
                            <th>Urgency</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_requests as $request): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($request['blood_group']); ?></td>
                                <td><?php echo htmlspecialchars($request['division']); ?></td>
                                <td><?php echo htmlspecialchars($request['district']); ?></td>
                                <td><?php echo htmlspecialchars($request['quantity']); ?></td>
                                <td><?php echo htmlspecialchars($request['urgency']); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($request['created_at'])); ?></td>
                                <td>
                                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#requestModal<?php echo $request['id']; ?>">
                                        View
                                    </button>
                                </td>
                            </tr>
                            <!-- Modal for each request -->
                            <div class="modal fade" id="requestModal<?php echo $request['id']; ?>" tabindex="-1" aria-labelledby="requestModalLabel<?php echo $request['id']; ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="requestModalLabel<?php echo $request['id']; ?>">Request Details</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <?php if (isset($_SESSION['user_id'])): ?>
                                                <p><strong>Requested by:</strong> <?php
                                                    if (isset($request['requester_id'])) {
                                                        $user = get_user_by_id($request['requester_id']);
                                                        if ($user && isset($user['username'])) {
                                                            echo htmlspecialchars($user['username']);
                                                        } else {
                                                            echo 'Unknown';
                                                        }
                                                    } else {
                                                        echo 'Unknown';
                                                    }
                                                ?></p>
                                                <p><strong>Contact Number:</strong> <?php echo isset($request['contact_number']) ? htmlspecialchars($request['contact_number']) : 'Not specified'; ?></p>
                                                <p><strong>Division:</strong> <?php echo isset($request['division']) ? htmlspecialchars($request['division']) : 'Not specified'; ?></p>
                                                <p><strong>District:</strong> <?php echo isset($request['district']) ? htmlspecialchars($request['district']) : 'Not specified'; ?></p>
                                                <p><strong>Hospital:</strong> <?php echo isset($request['hospital_name']) ? htmlspecialchars($request['hospital_name']) : 'Not specified'; ?></p>
                                                <p><strong>Blood Group:</strong> <?php echo isset($request['blood_group']) ? htmlspecialchars($request['blood_group']) : 'Not specified'; ?></p>
                                                <p><strong>Units Needed:</strong> <?php echo isset($request['quantity']) ? htmlspecialchars($request['quantity']) : 'Not specified'; ?></p>
                                                <p><strong>Urgency:</strong> <?php echo isset($request['urgency']) ? htmlspecialchars($request['urgency']) : 'Not specified'; ?></p>
                                            <?php else: ?>
                                                <p>Please <a href="index.php?page=login">login</a> to view request details.</p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No recent blood requests available.</p>
        <?php endif; ?>
    </div>
</div>

<div class="row mt-5">
    <div class="col-md-6">
        <h3>কেন রক্তদান করবেন?</h3>
        <ul>
            <li>জরুরি পরিস্থিতিতে জীবন বাঁচান</li>
            <li>বড় অস্ত্রোপচারের রোগীদের সাহায্য করুন</li>
            <li>রক্তের ব্যাধিগ্রস্ত ব্যক্তিদের সহায়তা করুন</li>
            <li>আপনার সম্প্রদায়ের স্বাস্থ্যে অবদান রাখুন</li>
        </ul>
    </div>

    <div class="col-md-6">
        <h3>কীভাবে রক্তদান করবেন</h3>
        <ol>
            <li>আপনার রক্তের গ্রুপ ও বিভাগ দিয়ে সার্স করুন</li>
            <li>আপনার অ্যাকাউন্টে নিবন্ধন করুন বা লগ ইন করুন</li>
            <li>একটি অ্যাপয়েন্টমেন্ট নির্ধারণ করুন</li>
            <li>রক্তদানের আগের নির্দেশাবলী অনুসরণ করুন</li>
            <li>রক্তদান করুন এবং জীবন বাঁচান!</li>
        </ol>
    </div>
</div>
