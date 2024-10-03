<?php
// Include any necessary header files or database connections here
require_once 'config.php';
require_once 'functions.php';

// Function to get lives saved (assuming each donation saves one life)
function get_lives_saved() {
    return get_successful_donations();
}
?>

<div class="container mt-5">
    <h1 class="mb-4">About Blood Donation Management System</h1>
    
    <div class="row">
        <div class="col-md-8">
            <h2>Our Mission</h2>
            <p>The Blood Donation Management System is dedicated to connecting blood donors with those in need, streamlining the process of blood donation, and ultimately saving lives. Our platform aims to create a robust network of donors and recipients, ensuring that blood is available when and where it's needed most.</p>
            
            <h2>What We Do</h2>
            <ul>
                <li>Facilitate easy registration for blood donors</li>
                <li>Allow users to search for compatible donors in their area</li>
                <li>Manage blood donation requests from hospitals and individuals</li>
                <li>Provide information about blood donation and its importance</li>
                <li>Organize blood donation drives and events</li>
            </ul>

            <h2>Our Impact</h2>
            <p>Since our inception, we have helped thousands of patients receive life-saving blood transfusions. Our system has significantly reduced the time it takes to find compatible donors, especially in emergency situations.</p>
        </div>

        <div class="col-md-4">
            <h2>Quick Facts</h2>
            <ul>
                <li>Founded in: 2024</li>
                <li>Registered Donors: <?php echo get_total_donors(); ?></li>
                <li>Donation Completed: <?php echo get_successful_donations(); ?></li>
            </ul>
            
            <h2>Contact Us</h2>
            <p>
                Email: info@blooddonation.org<br>
                Phone: +8801700000000<br>
                Address: Sonadanga, Khulna, Bangladesh
            </p>
            <a href="index.php?page=contact" class="btn btn-primary">Sent Message</a>
        </div>
    </div>
    
    <div class="row mt-5">
        <div class="col-md-12">
            <h2>Join Our Cause</h2>
            <p>Whether you're a potential donor or someone in need of blood, we invite you to join our community. Together, we can make a difference and save lives.</p>
            <a href="index.php?page=register" class="btn btn-primary">Register Now</a>
        </div>
    </div>
</div>