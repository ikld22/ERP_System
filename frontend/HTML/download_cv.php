<?php
session_start();
require 'conn.php';

// Check if the application_id is set in the URL
if (isset($_GET['application_id'])) {
    $applicationId = $_GET['application_id'];

    // Fetch the CV file path for the given application_id from the database
    $stmt = $pdo->prepare("
        SELECT uij.cv
        FROM job_applications ja
        INNER JOIN user_info_jop uij ON ja.user_id = uij.user_id
        WHERE ja.job_id = ? AND ja.user_id = ?
    ");
    
    // You should pass both the job_id and user_id here; make sure they are properly set
    $stmt->execute([$jobId, $userId]);
    $cvData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cvData && $cvData['cv']) {
        // Define the path where CVs are stored (ensure the correct directory and filename)
        $cvFilePath = 'path/to/cvs/' . $cvData['cv'];  // Change this to the correct file path

        // Check if the file exists
        if (file_exists($cvFilePath)) {
            // Set headers to trigger a download
            header('Content-Type: application/pdf');  // Adjust if the CV is not a PDF
            header('Content-Disposition: attachment; filename="' . basename($cvFilePath) . '"');
            header('Content-Length: ' . filesize($cvFilePath));

            // Output the file content
            readfile($cvFilePath);
            exit;
        } else {
            echo "The CV file was not found.";
        }
    } else {
        echo "No CV uploaded for this application.";
    }
} else {
    echo "Invalid application ID.";
}
?>
