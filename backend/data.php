<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// الاتصال بقاعدة البيانات باستخدام PDO
require 'conn.php';

// إضافة وظيفة جديدة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_job'])) {
    $jobTitle = $_POST['job_title'];
    $skillsRequired = $_POST['skills_required'];
    $experienceRequired = $_POST['experience_required'];
    $educationRequired = $_POST['education_required'];
    $jobDescription = $_POST['job_description'];
    $date = $_POST['created_at'];

    $stmt = $pdo->prepare("INSERT INTO jobs (job_title, skills_required, experience_required, education_required, job_description, created_at) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$jobTitle, $skillsRequired, $experienceRequired, $educationRequired, $jobDescription, $date]);

    header("Location: HR.php"); 
    exit();
}

// حذف وظيفة
if (isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM jobs WHERE id = ?");
    $stmt->execute([$deleteId]);

    header("Location: HR.php");
    exit();
}

// التحقق من تسجيل الدخول
if (!isset($_SESSION['employee_id'])) {
    echo "No employee ID found in session.";
    exit;
}

// جلب بيانات الموظف
$employeeId = $_SESSION['employee_id'];
$stmt = $pdo->prepare("SELECT * FROM employees WHERE employee_id = ?");
$stmt->execute([$employeeId]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

// جلب الوظائف
$jobsStmt = $pdo->query("SELECT * FROM jobs");
$jobs = $jobsStmt->fetchAll(PDO::FETCH_ASSOC);

// جلب طلبات الوظائف
$applicationsStmt = $pdo->query("
    SELECT 
        ja.job_id,          -- Ensure job_id is selected
        ja.job_id AS application_id,
        rj.name,
        rj.email,
        j.job_title,
        ja.applied_date,
        rj.id AS user_id
    FROM 
        job_applications ja
    INNER JOIN 
        jobs j ON ja.job_id = j.job_id
    INNER JOIN 
        registerjop rj ON ja.user_id = rj.id
");
$applications = $applicationsStmt->fetchAll(PDO::FETCH_ASSOC);

?>