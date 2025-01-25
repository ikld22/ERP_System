<?php
session_start(); // بدء الجلسة
require 'conn.php'; // تضمين ملف الاتصال بقاعدة البيانات

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['employee_id']); // تنظيف مدخلات اسم المستخدم
    $password = trim($_POST['password']); // تنظيف مدخلات كلمة المرور

    try {
        // البحث عن المستخدم بناءً على employee_id
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE employee_id = :employee_id"); // تغيير $conn إلى $pdo
        $stmt->bindParam(':employee_id', $username, PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // التحقق من كلمة المرور باستخدام password_verify
        if ($user && password_verify($password, $user['password'])) {
            // تخزين بيانات المستخدم في الجلسة
            $_SESSION['id'] = $user['id'];
            $_SESSION['employee_id'] = $user['employee_id'];
            $_SESSION['job_title'] = $user['job_title'];

            // إعادة التوجيه بناءً على المسمى الوظيفي
            switch ($user['job_title']) {
                case 'Department manager':
                    header("Location: D_manager.php");
                    exit;
                case 'General Manager':
                    header("Location: GM.html");
                    exit;
                case 'HR':
                    header("Location: HR.php");
                    exit;
                case 'Employee':
                    header("Location: employee.php");
                    exit;
                default:
                    echo "Unknown job title.";
            }
        } else {
            // إذا كانت بيانات تسجيل الدخول غير صحيحة
            echo "Invalid username or password.";
        }
    } catch (PDOException $e) {
        // التعامل مع خطأ في الاتصال بقاعدة البيانات
        echo "Error: " . $e->getMessage();
    }
}
?>