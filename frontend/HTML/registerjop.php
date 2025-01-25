<?php
// تضمين ملف الاتصال بقاعدة البيانات
include 'conn.php';

// التحقق من إرسال البيانات عبر POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $phone = trim($_POST['phone']); // تصحيح اسم المتغير

    // التحقق من عدم ترك الحقول فارغة
    if (empty($username) || empty($email) || empty($password) || empty($phone)) {
        die('Please fill all the fields.');
    }

    // التحقق من صحة البريد الإلكتروني
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die('Invalid email format.');
    }

    // التحقق من أن الحقل phone يحتوي فقط على أرقام
    if (!ctype_digit($phone)) {
        die('Phone number must contain only digits.');
    }

    // تشفير كلمة المرور
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    try {
        // إدخال البيانات في قاعدة البيانات
        $stmt = $pdo->prepare('INSERT INTO registerjop (name, email, phone, password) VALUES (:username, :email, :phone, :password)');
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':phone' => $phone,
            ':password' => $hashedPassword
        ]);
        
        // إعادة التوجيه إلى صفحة تسجيل الدخول
        header('Location: Loginjop.html');
        exit; // ضمان توقف التنفيذ بعد إعادة التوجيه
    
    } catch (PDOException $e) {
        // التعامل مع الأخطاء (مثل تكرار اسم المستخدم أو البريد الإلكتروني)
        if ($e->getCode() === '23000') {
            echo 'Username or email already exists.';
        } else {
            die('Error: ' . $e->getMessage());
        }
    }
}

?>
