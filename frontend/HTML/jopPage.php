<?php
session_start();
require 'conn.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

try {
    // استعلام الوظائف المتاحة
    $jobsStmt = $pdo->query("SELECT * FROM jobs");

    // استعلام بيانات المتقدم
    $applicantStmt = $pdo->prepare("SELECT * FROM user_info_jop WHERE user_id = :user_id");
    $applicantStmt->execute(['user_id' => $_SESSION['user_id']]);
    $applicant = $applicantStmt->fetch(PDO::FETCH_ASSOC);

    if (!$applicant) {
        echo "<p>No applicant data found. Please update your profile.</p>";
        exit;
    }

    // متغير لتتبع ما إذا تم العثور على أي وظيفة متوافقة
    $foundMatchingJob = false;

    // استعراض الوظائف المتاحة
    while ($job = $jobsStmt->fetch(PDO::FETCH_ASSOC)) {
        // دالة التحقق من التوافق بين الوظيفة والمتقدم
        $compatibilityScore = checkJobCompatibility($applicant, $job);
        
        // عرض الوظيفة فقط إذا كان هناك تطابق
        if ($compatibilityScore > 0) {
            $foundMatchingJob = true;
            
            // حفظ النتيجة في قاعدة البيانات
            saveMatchResult($_SESSION['user_id'], $job['job_id'], $compatibilityScore);

            echo "<div class='job-listing'>";
            echo "<h3>" . htmlspecialchars($job['job_title']) . "</h3>";
            echo "<p>" . htmlspecialchars($job['job_description']) . "</p>";
            echo "<p>Compatibility: " . $compatibilityScore . "%</p>";
            echo "</div>";
        }
    }

   
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// دالة التحقق من التوافق بين الوظيفة والمتقدم
function checkJobCompatibility($applicant, $job) {
    // التأكد من أن المتقدم لديه المهارات المطلوبة
    $applicantSkills = isset($applicant['skills']) ? explode(',', $applicant['skills']) : [];
    $requiredSkills = explode(',', $job['skills_required']);

    // التأكد من أن المتقدم لديه الخبرة المطلوبة
    $applicantExperience = isset($applicant['experience']) ? explode(',', $applicant['experience']) : [];
    $requiredExperience = explode(',', $job['experience_required']);

    // التأكد من أن المتقدم لديه التعليم المطلوب
    $applicantEducation = isset($applicant['education']) ? explode(',', $applicant['education']) : [];
    $requiredEducation = explode(',', $job['education_required']);

    // حساب عدد التوافقات
    $skillCount = 0;
    $experienceCount = 0;
    $educationCount = 0;

    foreach ($requiredSkills as $skill) {
        if (in_array(trim($skill), $applicantSkills)) {
            $skillCount++;
        }
    }

    foreach ($requiredExperience as $experience) {
        if (in_array(trim($experience), $applicantExperience)) {
            $experienceCount++;
        }
    }

    foreach ($requiredEducation as $education) {
        if (in_array(trim($education), $applicantEducation)) {
            $educationCount++;
        }
    }

    // تحديد نسبة التوافق
    if ($skillCount == count($requiredSkills) && $experienceCount == count($requiredExperience) && $educationCount == count($requiredEducation)) {
        return 100;
    } elseif ($skillCount >= 2 && $experienceCount >= 2 && $educationCount >= 2) {
        return 70;
    } elseif ($skillCount >= 1 && $experienceCount >= 1 && $educationCount >= 1) {
        return 40;
    } else {
        return 0;
    }
}

// دالة لحفظ النتيجة في جدول job_applications
function saveMatchResult($user_id, $job_id, $matchPercentage) {
    global $pdo;

    // التحقق مما إذا كان هناك سجل بالفعل
    $checkQuery = "SELECT * FROM job_applications WHERE user_id = ? AND job_id = ?";
    $stmt = $pdo->prepare($checkQuery);
    $stmt->execute([$user_id, $job_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // تحديث النسبة إذا كان هناك سجل
        $updateQuery = "UPDATE job_applications SET ApplicantMatch = ? WHERE user_id = ? AND job_id = ?";
        $stmt = $pdo->prepare($updateQuery);
        $stmt->execute([$matchPercentage, $user_id, $job_id]);
    } else {
        // إدخال سجل جديد إذا لم يكن موجودًا
        $insertQuery = "INSERT INTO job_applications (user_id, job_id, ApplicantMatch) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($insertQuery);
        $stmt->execute([$user_id, $job_id, $matchPercentage]);
    }
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Portal Dashboard</title>
    <style>
        /* تصميم الصفحة */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .navbar {
            background-color: #333;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar .logo h1 {
            margin: 0;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .nav-links a:hover {
            background-color: #575757;
        }

        .content {
            padding: 20px;
        }

        .section {
            display: none;
        }

        .section.active {
            display: block;
        }

        .job-box {
            background-color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .job-box h3 {
            margin-top: 0;
        }

        .job-box button {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .job-box button[disabled] {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .job-box p {
            margin: 5px 0;
        }

    </style>
</head>
<body>
    <div class="navbar">
        <div class="logo"><h1>Job Portal</h1></div>
        <div class="nav-links">
            <a href="#" onclick="showSection('available-jobs')">Available Jobs</a>
            <a href="#" onclick="showSection('applied-jobs')">Applied Jobs</a>
            <a href="profel.php">Profile</a>
        </div>
    </div>

    <div class="content">
        <div id="available-jobs" class="section active">
            <h2>Available Jobs</h2>
            <?php
            // إذا لم يتم العثور على وظائف متوافقة
    if (!$foundMatchingJob) {
        echo "<p>No matching jobs found.</p>";
    }
  ?>
            <?php  
while ($job = $jobsStmt->fetch(PDO::FETCH_ASSOC)): ?>
    <div class="job-box">
        <h3><?= htmlspecialchars($job['job_title']); ?></h3>
        <p>Description: <?= htmlspecialchars($job['job_description']); ?></p>
        <p>Experience Required: <?= htmlspecialchars($job['experience_required']); ?> years</p>
        <p>Skills: <?= htmlspecialchars($job['skills_required']); ?></p>
        <p>Education Required: <?= htmlspecialchars($job['education_required']); ?></p>
        <p>Job Date: <?= htmlspecialchars($job['created_at']); ?></p>

        <button onclick="applyJob(<?= $job['job_id']; ?>)">Apply</button>
    </div>
<?php endwhile; ?>

            

        <div id="applied-jobs" class="section">
            <h2>Applied Jobs</h2>
            <?php while ($appliedJob = $appliedJobsStmt->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="job-box">
                    <h3><?= htmlspecialchars($appliedJob['job_title']); ?></h3>
                    <p>Description: <?= htmlspecialchars($appliedJob['job_description']); ?></p>
                    <p>Applied Date: <?= htmlspecialchars($appliedJob['applied_date']); ?></p>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script>
        function showSection(sectionId) {
            const sections = document.querySelectorAll('.section');
            sections.forEach(section => section.classList.remove('active'));
            document.getElementById(sectionId).classList.add('active');
        }

        function applyJob(jobId) {
            if (confirm('Are you sure you want to apply for this job?')) {
                window.location.href = `apply_job.php?job_id=${jobId}`;
            }
        }
    </script>
</body>
</html>
