<?php
// Include necessary files and start session
require_once 'config.php';
require_once 'functions.php';

// Initialize variables
$filter_division = isset($_GET['filter_division']) ? sanitize_input($_GET['filter_division']) : '';
$filter_district = isset($_GET['filter_district']) ? sanitize_input($_GET['filter_district']) : '';
$filter_blood_group = isset($_GET['filter_blood_group']) ? sanitize_input($_GET['filter_blood_group']) : '';
$search_name = isset($_GET['search_name']) ? sanitize_input($_GET['search_name']) : '';

// Prepare search criteria
$criteria = [
    'division' => $filter_division,
    'district' => $filter_district,
    'blood_group' => $filter_blood_group,
    'name' => $search_name
];

// Get donors based on filters
$all_donors = searchDonors($criteria);

// Filter eligible donors
$donors = array_filter($all_donors, function($donor) {
    return is_eligible_to_donate($donor['id']);
});

// If no donors are found, get all donors
if (empty($donors)) {
    $donors = $all_donors;
}

// Get unique divisions, districts, and blood groups for dropdowns
$divisions = array_unique(array_column($all_donors, 'division'));
$districts = array_unique(array_column($all_donors, 'district'));
$blood_groups = array_unique(array_column($all_donors, 'blood_group'));

// Check if user is logged in
$is_logged_in = is_logged_in();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Donors</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Search Donors</h2>
        
        <!-- Donor Filter and Search Form -->
        <form method="get" action="index.php" class="mb-4">
            <input type="hidden" name="page" value="search_donors">
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
                <div class="col-md-2">
                    <label for="filter_blood_group" class="form-label">Blood Group</label>
                    <select class="form-select" id="filter_blood_group" name="filter_blood_group">
                        <option value="">All Groups</option>
                        <?php foreach ($blood_groups as $blood_group): ?>
                            <option value="<?php echo htmlspecialchars($blood_group); ?>" <?php echo $filter_blood_group === $blood_group ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($blood_group); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="search_name" class="form-label">Search by Name</label>
                    <input type="text" class="form-control" id="search_name" name="search_name" value="<?php echo htmlspecialchars($search_name); ?>" placeholder="Enter name">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </div>
        </form>

        <!-- Donors Table -->
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Blood Group</th>
                    <th>Division</th>
                    <th>District</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($donors)): ?>
                    <tr>
                        <td colspan="5" class="text-center">No donors found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($donors as $donor): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($donor['username']); ?></td>
                            <td><?php echo htmlspecialchars($donor['blood_group']); ?></td>
                            <td><?php echo htmlspecialchars($donor['division']); ?></td>
                            <td><?php echo htmlspecialchars($donor['district']); ?></td>
                            <td>
                                <?php if ($is_logged_in): ?>
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#donorModal<?php echo $donor['id']; ?>">
                                        View
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-info" onclick="alert('Please login to view information')">View</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <!-- Modal for each donor -->
                        <?php if ($is_logged_in): ?>
                            <div class="modal fade" id="donorModal<?php echo $donor['id']; ?>" tabindex="-1" aria-labelledby="donorModalLabel<?php echo $donor['id']; ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="donorModalLabel<?php echo $donor['id']; ?>">Donor Details</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Name:</strong> <?php echo htmlspecialchars($donor['username']); ?></p>
                                            <p><strong>Blood Group:</strong> <?php echo htmlspecialchars($donor['blood_group']); ?></p>
                                            <p><strong>Division:</strong> <?php echo htmlspecialchars($donor['division']); ?></p>
                                            <p><strong>District:</strong> <?php echo htmlspecialchars($donor['district']); ?></p>
                                            <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($donor['phone']); ?></p>
                                            <p><strong>Address:</strong> <?php echo htmlspecialchars($donor['location']); ?></p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
