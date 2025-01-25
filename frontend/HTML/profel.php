<?php
session_start(); // بدء الجلسة

require 'conn.php'; // ملف الاتصال بقاعدة البيانات

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // إعادة التوجيه إلى صفحة تسجيل الدخول إذا لم يكن المستخدم مسجلاً دخوله
    exit;
}

// جلب بيانات المستخدم الأساسية
$stmt = $pdo->prepare('SELECT * FROM registerjop WHERE id = :id');
$stmt->execute(['id' => $_SESSION['user_id']]);
$user = $stmt->fetch();

// جلب بيانات السيرة الذاتية للمستخدم
$stmt = $pdo->prepare('SELECT * FROM user_info_jop WHERE user_id = :user_id');
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$userCv = $stmt->fetch(PDO::FETCH_ASSOC);

// جلب بيانات إضافية من جدول user_info_jop
function getUserInfo($pdo, $userId, $column) {
    $stmt = $pdo->prepare("SELECT $column FROM user_info_jop WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetchColumn() ?? ''; // إرجاع المحتوى إذا كان موجودًا أو فارغًا إذا لم يكن
}

$experience = getUserInfo($pdo, $_SESSION['user_id'], 'experience');
$skills = getUserInfo($pdo, $_SESSION['user_id'], 'skills');
$languages = getUserInfo($pdo, $_SESSION['user_id'], 'languages');
$education = getUserInfo($pdo, $_SESSION['user_id'], 'education');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .alert {
            padding: 10px;
            background-color: #d9edf7;
            color: #31708f;
            border: 1px solid #bce8f1;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .navbar {
            margin-bottom: 20px;
        }
        .box {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .box h3 {
            color: #007BFF;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Job Portal</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="jopPage.php">Available Jobs</a></li>
                    <li class="nav-item"><a class="nav-link" href="jopPage.php">Applied Jobs</a></li>
                    <li class="nav-item"><a class="nav-link active" href="profel.php">Profile</a></li>
                </ul>
            </div>
        </div>
    </nav>

    
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info text-center">
                <?= htmlspecialchars($_SESSION['message']); ?>
                <?php unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <!-- عرض بيانات المستخدم -->
        <div class="box mb-4">
            <h3>Your Profile</h3>
            <p><strong>Welcome, <?= htmlspecialchars($user['name']) ?></strong></p>
            <p>Email: <?= htmlspecialchars($user['email']) ?></p>
            <p>phone: <?= htmlspecialchars($user['phone']) ?></p>

        </div>

      <!-- رفع السيرة الذاتية -->
<div class="box mb-4">
    <h3>Upload CV</h3>
    <?php if ($userCv && isset($userCv['cv']) && !empty($userCv['cv'])): ?>
        <p><?= htmlspecialchars($userCv['file_name']) ?></p>
        <a href="download_cv.php?cv_id=<?= htmlspecialchars($userCv['id']) ?>" class="btn btn-success">Download CV</a>
    <?php else: ?>
        <p>No CV uploaded yet.</p>
    <?php endif; ?>
    <form action="save_cv.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="cv" id="cv" class="form-control mb-3" accept=".pdf,.doc,.docx">
        <button type="submit" name="upload_cv" class="btn btn-primary">Upload CV</button>
    </form>
</div>


        <!-- عرض السيرة الذاتية إذا كانت موجودة -->
      
     
        <!-- الخبرات -->
        <div class="box mb-4">
            <h3>Experiences</h3>
            <form action="save_info.php" method="POST">
                <textarea name="experience" rows="5" class="form-control mb-3" placeholder="Add your experiences..."><?= htmlspecialchars($experience) ?></textarea>
                <button type="submit" name="save_experience" class="btn btn-primary">Save</button>
            </form>
        </div>

        <!-- اللغات -->
        <div class="box mb-4">
            <h3>Languages</h3>
            <form action="save_info.php" method="POST">
                <textarea name="languages" rows="5" class="form-control mb-3" placeholder="Add the languages you speak..."><?= htmlspecialchars($languages) ?></textarea>
                <button type="submit" name="save_languages" class="btn btn-primary">Save</button>
            </form>
        </div>

        <!-- المهارات -->
        <div class="box mb-4">
            <h3>Skills</h3>
            <form action="save_info.php" method="POST">
                <textarea name="skills" rows="5" class="form-control mb-3" placeholder="Add your skills..."><?= htmlspecialchars($skills) ?></textarea>
                <button type="submit" name="save_skills" class="btn btn-primary">Save</button>
            </form>
        </div>

        <!-- التعليم -->
        <div class="box mb-4">
            <h3>Education</h3>
            <form action="save_info.php" method="POST">
                <textarea name="education" rows="5" class="form-control mb-3" placeholder="Add your educational background..."><?= htmlspecialchars($education) ?></textarea>
                <button type="submit" name="save_education" class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
