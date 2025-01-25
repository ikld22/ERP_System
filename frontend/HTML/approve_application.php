<?php
session_start();
require 'conn.php';

$applicationId = isset($_GET['application_id']) ? $_GET['application_id'] : null;
$userId = isset($_GET['user_id']) ? $_GET['user_id'] : null;

if ($applicationId === null || $userId === null) {
    echo "Missing application or user ID!";
    exit;
}

// Get applicant's information
$stmt = $pdo->prepare("
    SELECT 
        rj.name, 
        rj.email, 
        rj.phone, 
        uij.education, 
        uij.experience, 
        uij.skills, 
        uij.languages, 
        ja.applied_date, 
        j.job_title
    FROM job_applications ja
    INNER JOIN registerjop rj ON ja.user_id = rj.id
    INNER JOIN user_info_jop uij ON ja.user_id = uij.user_id
    INNER JOIN jobs j ON ja.job_id = j.job_id
    WHERE ja.job_id = ? AND ja.user_id = ?
");
$stmt->execute([$applicationId, $userId]);
$applicant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$applicant) {
    echo "Application not found!";
    exit;
}

// Check if the email already exists in the employees table
$checkEmailStmt = $pdo->prepare("SELECT COUNT(*) FROM employees WHERE employee_email = ?");
$checkEmailStmt->execute([$applicant['email']]);
$emailCount = $checkEmailStmt->fetchColumn();

if ($emailCount > 0) {
    // If the email exists, show an error message
    echo "This email is already registered as an employee.";
    exit;
}

// Approve or Reject the application
if (isset($_POST['approve'])) {
    // Insert applicant's information into employees table
    $employee_id = 'EMP' . time(); // Generate a unique employee ID (e.g., EMP + current timestamp)
    $password = password_hash('default_password', PASSWORD_BCRYPT); // Generate a hashed password

    // Prepare the insert query
    $insertStmt = $pdo->prepare("
        INSERT INTO employees 
        (name, job_title, employee_id, password, employee_email, employee_number, department, dob, start_date) 
        VALUES 
        (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    // Insert values into the employees table
    $insertStmt->execute([
        $applicant['name'],
        $applicant['job_title'],
        $employee_id,
        $password,
        $applicant['email'],
        $applicant['phone'],  // Assuming phone is used as employee_number
        'Default Department',  // Assuming you have a default department or you can get this from job info
        date('Y-m-d'),  // Set the start date as today's date
        date('Y-m-d')   // Same for the start date or get it from the applicant's info if available
    ]);

    // Optionally, you can update the job application status to 'Approved'
    $approveStmt = $pdo->prepare("UPDATE job_applications SET status = 'Approved' WHERE job_id = ? AND user_id = ?");
    $approveStmt->execute([$applicationId, $userId]);

    // Delete the applicant's record from the job applications and user info tables
    $deleteStmt = $pdo->prepare("DELETE FROM job_applications WHERE application_id = ?");
    $deleteStmt->execute([$applicationId]);

    $deleteUserInfoStmt = $pdo->prepare("DELETE FROM user_info_jop WHERE user_id = ?");
    $deleteUserInfoStmt->execute([$userId]);

    // Display success message
    echo "Employee has been successfully added, and the application has been approved!";

    // Redirect to HR page after 3 seconds
    header("Refresh: 3; URL=HR.php");
    exit;
}

if (isset($_POST['reject'])) {
    // Code to reject the application
    $rejectStmt = $pdo->prepare("UPDATE job_applications SET status = 'Rejected' WHERE job_id = ? AND user_id = ?");
    $rejectStmt->execute([$applicationId, $userId]);

    // Optionally, delete the application record
    $deleteStmt = $pdo->prepare("DELETE FROM job_applications WHERE application_id = ?");
    $deleteStmt->execute([$applicationId]);

    header("Location: HR.php");
    exit;
}
?>
