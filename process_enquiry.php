<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for JSON response
header('Content-Type: application/json');

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "herbal_products";

// Email configuration - UPDATE THESE WITH REAL CREDENTIALS
$admin_email = "hr@7siq.com";
$website_name = "Herbal Products Inc";
$from_email = "devaraj@7siq.com";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    $response = [
        'success' => false,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ];
    echo json_encode($response);
    exit;
}

// Get POST data
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
$company = isset($_POST['company']) ? trim($_POST['company']) : '';
$product = isset($_POST['product']) ? trim($_POST['product']) : '';
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
$message = isset($_POST['message']) ? trim($_POST['message']) : '';
$submission_date = date('Y-m-d H:i:s');

// Validate required fields
if (empty($name) || empty($email) || empty($mobile) || empty($product) || $quantity <= 0) {
    $response = [
        'success' => false,
        'message' => 'Please fill in all required fields'
    ];
    echo json_encode($response);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response = [
        'success' => false,
        'message' => 'Please enter a valid email address'
    ];
    echo json_encode($response);
    exit;
}

// Map product values to display names
$product_names = [
    'ultra-thin-herbal-pads' => 'Ultra Thin Herbal Pads',
    'xl-herbal-cotton-pads' => 'XL Herbal Cotton Pads',
    'xxl-herbal-cotton-pads' => 'XXL Herbal Cotton Pads',
    'cotton-herbal-pads' => 'Cotton Herbal Pads',
    'panty-liner-pads' => 'Panty Liner Pads',
    'biodegradable-herbal-pads' => 'Biodegradable Herbal Pads'
];

$product_display = isset($product_names[$product]) ? $product_names[$product] : $product;

// Insert into database
$sql = "INSERT INTO enquiries (name, email, mobile, company, product, quantity, message, submission_date) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    $response = [
        'success' => false,
        'message' => 'Database prepare failed: ' . $conn->error
    ];
    echo json_encode($response);
    exit;
}

$stmt->bind_param("sssssiss", $name, $email, $mobile, $company, $product, $quantity, $message, $submission_date);

if ($stmt->execute()) {
    $enquiry_id = $stmt->insert_id;
    $email_errors = [];
    
    // Email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: $website_name <$from_email>" . "\r\n";
    
    // Send email to admin
    $admin_subject = "New Product Enquiry Received - $website_name";
    $admin_body = "
    <html>
    <head>
        <title>New Product Enquiry</title>
    </head>
    <body>
        <h2>New Product Enquiry Received</h2>
        <p><strong>Enquiry ID:</strong> $enquiry_id</p>
        <p><strong>Name:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Mobile:</strong> $mobile</p>
        <p><strong>Company:</strong> " . ($company ? $company : 'Not provided') . "</p>
        <p><strong>Product:</strong> $product_display</p>
        <p><strong>Quantity:</strong> $quantity</p>
        <p><strong>Message:</strong> " . ($message ? nl2br($message) : 'No message provided') . "</p>
        <p><strong>Submission Date:</strong> $submission_date</p>
    </body>
    </html>
    ";
    
    // Send admin email
    if (!mail($admin_email, $admin_subject, $admin_body, $headers)) {
        $email_errors[] = "Failed to send email to admin";
        // Log the error for debugging
        error_log("Failed to send email to admin: $admin_email");
    }
    
    // Send confirmation email to user
    $user_subject = "Thank you for your product enquiry - $website_name";
    $user_body = "
    <html>
    <head>
        <title>Product Enquiry Confirmation</title>
    </head>
    <body>
        <h2>Thank you for your enquiry!</h2>
        <p>Dear $name,</p>
        <p>We have received your enquiry for our herbal products and will get back to you shortly with pricing information.</p>
        
        <h3>Your Enquiry Details:</h3>
        <p><strong>Product:</strong> $product_display</p>
        <p><strong>Quantity:</strong> $quantity</p>
        <p><strong>Enquiry Date:</strong> $submission_date</p>
        
        <p>Our team will review your requirements and contact you within 24 hours.</p>
        <p>Best regards,<br><strong>$website_name Team</strong></p>
    </body>
    </html>
    ";
    
    // Send user confirmation email
    if (!mail($email, $user_subject, $user_body, $headers)) {
        $email_errors[] = "Failed to send confirmation email to user";
        error_log("Failed to send email to user: $email");
    }
    
    // Prepare response
    if (empty($email_errors)) {
        $response = [
            'success' => true,
            'message' => 'Enquiry submitted successfully! We will contact you shortly.'
        ];
    } else {
        $response = [
            'success' => true,
            'message' => 'Enquiry submitted successfully! However, there were issues sending confirmation emails.'
        ];
    }
} else {
    $response = [
        'success' => false,
        'message' => 'Error submitting enquiry: ' . $stmt->error
    ];
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>