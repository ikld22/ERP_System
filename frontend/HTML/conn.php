<?php
// معلومات الاتصال بقاعدة البيانات
$host = 'localhost'; // اسم السيرفر
$dbname = 'erps'; // اسم قاعدة البيانات
$username = 'root'; // اسم المستخدم
$password = ''; // كلمة المرور

try {
    // إنشاء اتصال PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // إعدادات لتفعيل وضع الخطأ (لإظهار الأخطاء)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // إعداد نمط الجلب الافتراضي
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // عرض رسالة الخطأ في حال فشل الاتصال
    die('Connection failed: ' . $e->getMessage());
}
?>
