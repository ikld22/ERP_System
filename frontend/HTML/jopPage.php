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

    // استعلام الوظائف التي تم التقديم عليها
    $appliedJobsStmt = $pdo->prepare("SELECT jobs.job_id, jobs.job_title, jobs.job_description, job_applications.applied_date 
                                      FROM job_applications 
                                      JOIN jobs ON job_applications.job_id = jobs.job_id 
                                      WHERE job_applications.user_id = :user_id");
    $appliedJobsStmt->execute(['user_id' => $_SESSION['user_id']]);
    
    // استعلام بيانات المتقدم
    $applicantStmt = $pdo->prepare("SELECT * FROM user_info_jop WHERE user_id = :user_id");
    $applicantStmt->execute(['user_id' => $_SESSION['user_id']]);
    $applicant = $applicantStmt->fetch(PDO::FETCH_ASSOC);

    if (!$applicant) {
        echo "<p>No applicant data found. Please update your profile.</p>";
        exit;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// دالة التحقق من التوافق بين الوظيفة والمتقدم
function checkJobCompatibility($features, $job) {
    $encodedFeatures = escapeshellarg(json_encode($features));

    // تأكد من استخدام المسار الكامل لـ Python
    $pythonPath = "C:\Users\ccvn5\AppData\Local\Programs\Python\Python312\python.exe"; // Adjust this path to your Python installation
    $scriptPath = "C:\\wamp64\\www\\ERP_System\\frontend\\HTML\\predict_employee.py"; // Adjust this path to your script
    $command = "$pythonPath $scriptPath $encodedFeatures";

    // تنفيذ السكربت
    $output = shell_exec($command);

    if ($output === null) {
        error_log("Python script failed to execute for features: $encodedFeatures");
        return "Prediction failed. There was an error processing your data.";
    }

    // Log the output for debugging
    error_log("Python script output: $output");

    // استخراج النتيجة
    $compatibility = floatval($output);

    // فحص المهارات المطلوبة مقارنة بمهارات المتقدم
    $applicantSkills = explode(',', $features[1]); // assuming skills are stored as a comma-separated string
    $requiredSkills = explode(',', $job['skills_required']); // assuming skills are stored as a comma-separated string
    
    // إذا كان المتقدم يملك مهارة واحدة على الأقل من المهارات المطلوبة
    foreach ($requiredSkills as $skill) {
        if (in_array(trim($skill), $applicantSkills)) {
            return "50% Match"; // يمكن التقديم إذا كانت المهارات 50%
        }
    }

    if ($compatibility >= 0.5) {
        return "Good Match"; // يمكن التقديم إذا كانت التوافقية 50% أو أكثر
    } elseif ($compatibility >= 0.2) {
        return "50% Match";
    } else {
        return "Not a Good Match";
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
            while ($job = $jobsStmt->fetch(PDO::FETCH_ASSOC)): 
                $applicantFeatures = [
                    $applicant['experience'],
                    $applicant['skills'],
                    $applicant['education'],
                ];
                $compatibility = checkJobCompatibility($applicantFeatures, $job);
            ?> 
            <div class="job-box">
                <h3><?= htmlspecialchars($job['job_title']); ?></h3>
                <p>Description: <?= htmlspecialchars($job['job_description']); ?></p>
                <p>Experience Required: <?= htmlspecialchars($job['experience_required']); ?> years</p>
                <p>Skills: <?= htmlspecialchars($job['skills_required']); ?></p>
                <p>Education Required: <?= htmlspecialchars($job['education_required']); ?></p>
                <p>Job Date: <?= htmlspecialchars($job['created_at']); ?></p>
                <p>Compatibility: <?= htmlspecialchars($compatibility); ?></p>
                <?php if ($compatibility === "Good Match" || $compatibility === "50% Match"): ?>
                    <button onclick="applyJob(<?= $job['job_id']; ?>)">Apply</button>
                <?php else: ?>
                    <button disabled>Not Compatible</button>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        </div>

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
