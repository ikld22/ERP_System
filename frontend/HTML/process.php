<?php
// بدء الجلسة (إذا لزم الأمر)
session_start();
require 'conn.php';

try {
    // إعداد اتصال PDO
    $pdo = new PDO('mysql:host=localhost;dbname=erps', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // التحقق من إرسال البيانات باستخدام POST
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // استقبال البيانات من النموذج
        $name = trim($_POST['name']);
        $job_title = trim($_POST['job_title']);
        $employee_email = trim($_POST['employee_email']);
        $employee_number = trim($_POST['employee_number']);
        $department = trim($_POST['department']);
        $dob = $_POST['dob'];
        $start_date = $_POST['start_date'];

        // التحقق من صحة البيانات
        if (empty($name) || empty($job_title) || empty($employee_email) || empty($department)) {
            die("All required fields must be filled.");
        }

        if (!filter_var($employee_email, FILTER_VALIDATE_EMAIL)) {
            die("Invalid email format.");
        }

        // إنشاء رقم موظف تلقائيًا
        $employee_id = strtoupper(substr($department, 0, 2)) . rand(1000, 9999); // مثال: IT1234

        // إنشاء كلمة مرور عشوائية
        $employee_password = bin2hex(random_bytes(4)); // كلمة مرور عشوائية مثل: a1b2c3d4

        // تشفير كلمة المرور
        $hashed_password = password_hash($employee_password, PASSWORD_BCRYPT);

        // إعداد استعلام الإدخال
        $sql = "INSERT INTO employees (name, job_title, employee_id, password, employee_email, employee_number, department, dob, start_date) 
                VALUES (:name, :job_title, :employee_id, :password, :employee_email, :employee_number, :department, :dob, :start_date)";

        // تحضير الاستعلام
        $stmt = $pdo->prepare($sql);

        // ربط القيم بالمتغيرات
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':job_title', $job_title);
        $stmt->bindParam(':employee_id', $employee_id);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':employee_email', $employee_email);
        $stmt->bindParam(':employee_number', $employee_number);
        $stmt->bindParam(':department', $department);
        $stmt->bindParam(':dob', $dob);
        $stmt->bindParam(':start_date', $start_date);

        // تنفيذ الاستعلام
        if ($stmt->execute()) {
            echo "<h3>Employee information saved successfully!</h3>";
            echo "<p><strong>Employee ID:</strong> $employee_id</p>";
            echo "<p><strong>Password:</strong> $employee_password</p>";
        } else {
            echo "Failed to save employee information.";
        }
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage()); // تسجيل الخطأ في ملف
    echo "Error: " . $e->getMessage();
}
?>

