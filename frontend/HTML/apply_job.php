<?php
session_start();

require 'conn.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['job_id'])) {
    die("Job ID is required.");
}

$job_id = $_GET['job_id'];
$user_id = $_SESSION['user_id'];

try {
    // تحقق إذا كان المتقدم قد تقدم بالفعل للوظيفة
    $checkStmt = $pdo->prepare("SELECT * FROM job_applications WHERE user_id = :user_id AND job_id = :job_id");
    $checkStmt->execute(['user_id' => $user_id, 'job_id' => $job_id]);
    $existingApplication = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existingApplication) {
        echo "<p>You have already applied for this job.</p>";
        exit;
    }

    // إضافة طلب التقديم للوظيفة
    $applyStmt = $pdo->prepare("INSERT INTO job_applications (user_id, job_id, applied_date) VALUES (:user_id, :job_id, NOW())");
    $applyStmt->execute(['user_id' => $user_id, 'job_id' => $job_id]);
    header('Location: jopPage.php');
    echo "<p>You have successfully applied for the job!</p>";


} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

?>
