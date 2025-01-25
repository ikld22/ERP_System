<?php
// بدء الجلسة
session_start();

// تضمين ملف الاتصال بقاعدة البيانات
require 'conn.php';

// التحقق من إرسال البيانات عبر POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['email']);
    $password = trim($_POST['password']);

    // التحقق من أن الحقول ليست فارغة
    if (empty($username) || empty($password)) {
        echo 'Please fill in all fields.';
        exit;
    }

    try {
        // البحث عن المستخدم في قاعدة البيانات
        $stmt = $pdo->prepare('SELECT * FROM registerjop WHERE email = :email');
        $stmt->execute(['email' => $username]);
        $user = $stmt->fetch();

        if (!$user) {
            echo 'No account found with this email.';
        } elseif (!password_verify($password, $user['password'])) {
            echo 'Invalid password.';
        } else {
            // تسجيل الدخول ناجح
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['email'];
            echo 'Login successful! Welcome, ' . htmlspecialchars($user['email']);
            header('Location: profel.php');
            exit;
        }
    } catch (PDOException $e) {
        echo 'Database error: ' . $e->getMessage();
    }
}
?>
