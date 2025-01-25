<?php
session_start();
include 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['cv'])) {
    $uploadDir = 'uploads/';
    $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    $file = $_FILES['cv'];
    $fileName = basename($file['name']);
    $fileTmp = $file['tmp_name'];
    $fileType = mime_content_type($fileTmp);
    $userId = $_SESSION['user_id']; // تأكد من أن المستخدم مسجل الدخول

    // تحقق من تسجيل المستخدم
    if (!$userId) {
        die('User not logged in.');
    }

    // التحقق من وجود سجل بالفعل للمستخدم
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM user_info_jop WHERE user_id = :user_id');
    $stmt->execute([':user_id' => $userId]);
    $recordExists = $stmt->fetchColumn();

    if ($recordExists) {
        die('You have already uploaded your CV. You can update it instead.');
    }

    // التحقق من نوع الملف
    if (!in_array($fileType, $allowedTypes)) {
        die('Invalid file type. Only PDF, DOC, and DOCX files are allowed.');
    }

    // التحقق من حجم الملف (بحد أقصى 2 ميغابايت)
    if ($file['size'] > 2 * 1024 * 1024) {
        die('File size exceeds the 2MB limit.');
    }

    // تحديد المسار الجديد للملف
    $filePath = $uploadDir . time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $fileName);

    // نقل الملف إلى المجلد المحدد
    if (move_uploaded_file($fileTmp, $filePath)) {
        try {
            // إدخال البيانات في قاعدة البيانات
            $stmt = $pdo->prepare('INSERT INTO user_info_jop (user_id, file_name, file_path) VALUES (:user_id, :file_name, :file_path)');
            $stmt->execute([
                ':user_id' => $userId,
                ':file_name' => $fileName,
                ':file_path' => $filePath,
            ]);
            header('Location: profile.php');
            exit;
        } catch (PDOException $e) {
            die('Database error: ' . $e->getMessage());
        }
    } else {
        die('Failed to upload the file.');
    }
} else {
    die('Invalid request.');
}
?>
