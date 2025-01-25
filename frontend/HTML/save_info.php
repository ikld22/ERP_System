<?php
require 'conn.php'; // الاتصال بقاعدة البيانات
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];

    // بيانات السيرة الذاتية
    if (isset($_FILES['cv'])) {
        // معالجة السيرة الذاتية كما في الكود السابق
        // هنا سيتم التحقق من السيرة الذاتية فقط كما في الكود السابق
    }

    // بيانات النصوص (الخبرات، المهارات، اللغات، التعليم)
    $fields = ['experience', 'skills', 'languages', 'education'];

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $fieldValue = trim($_POST[$field]);

            if (!empty($fieldValue)) {
                // تحقق إذا كانت البيانات موجودة بالفعل للمستخدم
                $stmt = $pdo->prepare("SELECT id FROM user_info_jop WHERE user_id = :user_id AND $field IS NOT NULL");
                $stmt->execute(['user_id' => $user_id]);
                $existingData = $stmt->fetch();

                if ($existingData) {
                    // إذا كانت البيانات موجودة، نقوم بتحديثها
                    $stmt = $pdo->prepare("UPDATE user_info_jop SET $field = :value WHERE user_id = :user_id");
                    $stmt->execute([
                        'value' => $fieldValue,
                        'user_id' => $user_id
                    ]);
                    echo ucfirst($field) . " updated successfully!";
                } else {
                    // إذا كانت البيانات غير موجودة، نقوم بإدخالها
                    $stmt = $pdo->prepare("INSERT INTO user_info_jop (user_id, $field) VALUES (:user_id, :value)");
                    $stmt->execute([
                        'user_id' => $user_id,
                        'value' => $fieldValue
                    ]);
                    echo ucfirst($field) . " added successfully!";
                }
            }
        }
    }

    // إعادة التوجيه إلى صفحة الملف الشخصي
    header('Location: profel.php'); // تغيير 'profile.php' إلى الاسم الصحيح للملف
    exit;
}
?>
