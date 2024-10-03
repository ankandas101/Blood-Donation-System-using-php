<?php
session_start();
require_once 'config.php';
require_once 'functions.php';
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/custom.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
        }
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        .navbar-brand {
            font-weight: bold;
            color: #dc3545 !important;
        }
        .nav-link {
            color: #495057 !important;
            font-weight: 500;
        }
        .nav-link:hover {
            color: #dc3545 !important;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,.1);
            padding: 30px;
            margin-top: 30px;
            margin-bottom: 30px;
        }
        footer {
            background-color: #343a40;
            color: #ffffff;
        }
        footer a,h5 {
            color: #000000;
            text-decoration: none;
        }
        footer a:hover {
            color: #dc3545;
        }
        .footer_text {
            color: #000000;
        }
        .pic_bg{
            width: 40%;
            height: 50%;
            object-fit: fill;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-heartbeat me-2"></i><?php echo SITE_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i>Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=donor_dashboard"><i class="fas fa-hand-holding-medical me-1"></i>Donate</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=request"><i class="fas fa-tint me-1"></i>Request Blood</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=search_donors"><i class="fas fa-search me-1"></i>Search Donors</a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=profile"><i class="fas fa-user me-1"></i>Profile</a>
                        </li>
                        <?php if ($_SESSION['role'] == ROLE_ADMIN): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?page=admin"><i class="fas fa-user-shield me-1"></i>Admin</a>
                            </li>
                        <?php endif; ?>
                        <?php if ($_SESSION['role'] == ROLE_MODERATOR): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?page=moderator"><i class="fas fa-user-cog me-1"></i>Moderator</a>
                            </li>
                        <?php endif; ?>
                        <?php if ($_SESSION['role'] == ROLE_RECIPIENT): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?page=recipient"><i class="fas fa-user-injured me-1"></i>Recipient</a>
                            </li>
                        <?php endif; ?>
                        <?php if ($_SESSION['role'] == ROLE_DONOR): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?page=donor_dashboard"><i class="fas fa-tachometer-alt me-1"></i>Donor Dashboard</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=register"><i class="fas fa-user-plus me-1"></i>Register</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=login"><i class="fas fa-sign-in-alt me-1"></i>Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <?php
        // Role-based access control
        function check_user_access($required_role) {
            if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
                return false;
            }
            $user_role = $_SESSION['role'];
            switch ($required_role) {
                case ROLE_ADMIN:
                    return $user_role == ROLE_ADMIN;
                case ROLE_MODERATOR:
                    return $user_role == ROLE_ADMIN || $user_role == ROLE_MODERATOR;
                case ROLE_DONOR:
                    return $user_role == ROLE_DONOR;
                case ROLE_RECIPIENT:
                    return $user_role == ROLE_RECIPIENT;
                default:
                    return false;
            }
        }

        // Update switch statement to include role-based access control
        switch ($page) {
            case 'home':
            case 'about':
            case 'faq':
            case 'search_donors':
            case 'contact':
            case 'terms':
                include "pages/{$page}.php";
                break;
            case 'user_management':
            case 'donor_dashboard':
            case 'manage_request':
            case 'moderator':
            case 'admin':
            case 'manage_requests':
            case 'donate':
            case 'request':
            case 'profile':
            case 'donation':
            case 'edit_donation':
            case 'manage_donor':
            case 'statistics':
            case 'reports':
            case 'settings':
            case 'notices':
            case 'messages':
            case 'recipient':
                if (isset($_SESSION['user_id'])) {
                    $user_role = $_SESSION['role'];
                    if (check_user_access($user_role)) {
                        include "pages/{$page}.php";
                    } else {
                        include 'pages/access_denied.php';
                    }
                } else {
                    include 'pages/access_denied.php';
                }
                break;
            case 'register':
            case 'login':
                if (!isset($_SESSION['user_id'])) {
                    include "pages/{$page}.php";
                } else {
                    header("Location: index.php");
                    exit();
                }
                break;
            default:
                include 'pages/home.php';
        }
        ?>
    </div>

    <footer class="text-center text-lg-start mt-4 py-4">
        <div class="container">
            <div class="row">
                <div class="footer_text col-lg-4 col-md-12 mb-4 mb-md-0">
                    <h5 class="text-uppercase"><i class="fas fa-heartbeat me-2"></i><?php echo SITE_NAME; ?></h5>
                    <p>
                        Connecting donors and recipients for a healthier community.
                    </p>
                </div>
                <div class="col-lg-4 col-md-6 mb-4 mb-md-0">
                    <h5 class="text-uppercase">Quick Links</h5>
                    <ul class="list-unstyled mb-0">
                        <li><a href="index.php?page=about"><i class="fas fa-info-circle me-2"></i>About Us</a></li>
                        <li><a href="index.php?page=contact"><i class="fas fa-envelope me-2"></i>Contact</a></li>
                        <li><a href="index.php?page=faq"><i class="fas fa-question-circle me-2"></i>FAQ</a></li>
                        <li><a href="index.php?page=terms"><i class="fas fa-file-alt me-2"></i>Terms and Conditions</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-6 mb-4 mb-md-0">
                    <h5 class="text-uppercase mb-0">Follow Us</h5>
                    <ul class="list-unstyled">
                        <li><a href="https://www.facebook.com/AnkanDas.fb" target="_blank"><i class="fab fa-facebook me-2"></i>Facebook</a></li>
                        <li><a href="#!"><i class="fab fa-twitter me-2"></i>Twitter</a></li>
                        <li><a href="#!"><i class="fab fa-instagram me-2"></i>Instagram</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.2);">
            © <?php echo date("Y"); ?> Copyright: Ankan Das
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/custom.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const divisionSelect = document.getElementById('division');
        const districtSelect = document.getElementById('district');

        if (divisionSelect && districtSelect) {
            divisionSelect.addEventListener('change', function() {
                const selectedDivision = this.value;
                fetch(`get_districts.php?division=${encodeURIComponent(selectedDivision)}`)
                    .then(response => response.json())
                    .then(districts => {
                        districtSelect.innerHTML = '<option value="">Select District</option>';
                        districts.forEach(district => {
                            const option = document.createElement('option');
                            option.value = district;
                            option.textContent = district;
                            districtSelect.appendChild(option);
                        });
                        districtSelect.disabled = false;
                    })
                    .catch(error => console.error('Error:', error));
            });
        }
    });
    </script>
</body>
</html>