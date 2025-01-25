<?php
session_start();
require 'conn.php';

// Check if an application is selected for viewing
if (isset($_GET['application_id']) && isset($_GET['user_id'])) {
    $applicationId = $_GET['application_id'];
    $userId = $_GET['user_id'];

    // Get applicant's information and CV
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
            uij.cv,   -- Changed to uij.cv to get CV from user_info_jop
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

    // Approve or Reject the application
    if (isset($_POST['approve'])) {
        // Code to approve the application (e.g., update a status field in database)
        $approveStmt = $pdo->prepare("UPDATE job_applications SET status = 'Approved' WHERE job_id = ? AND user_id = ?");
        $approveStmt->execute([$applicationId, $userId]);
        header("Location: HR.php");
        exit;
    }

    if (isset($_POST['reject'])) {
        // Code to reject the application
        $rejectStmt = $pdo->prepare("UPDATE job_applications SET status = 'Rejected' WHERE job_id = ? AND user_id = ?");
        $rejectStmt->execute([$applicationId, $userId]);
        header("Location: HR.php");
        exit;
    }
} else {
    echo "Invalid parameters.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applicant Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">Applicant Information</div>
            <div class="card-body">
                <ul class="list-group">
                    <li class="list-group-item"><strong>Name:</strong> <?= htmlspecialchars($applicant['name']) ?></li>
                    <li class="list-group-item"><strong>Email:</strong> <?= htmlspecialchars($applicant['email']) ?></li>
                    <li class="list-group-item"><strong>Phone:</strong> <?= htmlspecialchars($applicant['phone']) ?></li>
                    <li class="list-group-item"><strong>Education:</strong> <?= htmlspecialchars($applicant['education']) ?></li>
                    <li class="list-group-item"><strong>Experience:</strong> <?= htmlspecialchars($applicant['experience']) ?></li>
                    <li class="list-group-item"><strong>Skills:</strong> <?= htmlspecialchars($applicant['skills']) ?></li>
                    <li class="list-group-item"><strong>Languages:</strong> <?= htmlspecialchars($applicant['languages']) ?></li>
                    <li class="list-group-item"><strong>Applied Date:</strong> <?= htmlspecialchars($applicant['applied_date']) ?></li>
                    <li class="list-group-item"><strong>Job Applied For:</strong> <?= htmlspecialchars($applicant['job_title']) ?></li>
                </ul>

                <!-- CV Download Link (if available) -->
                <?php if ($applicant['cv']): ?>
                    <a href="download_cv.php?application_id=<?= $applicationId ?>" class="btn btn-info mt-3">Download CV</a>
                <?php else: ?>
                    <p>No CV uploaded.</p>
                <?php endif; ?>

                <!-- Approval or Rejection Buttons -->
                <form method="POST" class="mt-3">
                    <button type="submit" name="approve" class="btn btn-success">Approve</button>
                    <button type="submit" name="reject" class="btn btn-danger">Reject</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
