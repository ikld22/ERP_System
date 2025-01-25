<?php
session_start();
require 'conn.php'; // الاتصال بقاعدة البيانات

// التحقق من أن الرسالة قد تم إرسالها
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $message = $_POST['message'];
    $employee_id = $_SESSION['employee_id']; // الحصول على معرف الموظف من الجلسة
    $to = $_POST['to']; // تحديد ما إذا كانت الرسالة إلى HR أو Manager

    try {
        // إعداد الاستعلام لإدخال الرسالة في قاعدة البيانات
        $stmt = $pdo->prepare("INSERT INTO communication (employee_id, `to`, Message) VALUES (?, ?, ?)");
        $stmt->execute([$employee_id, $to, $message]);

        // عرض رسالة تأكيد
        $_SESSION['message_status'] = "Your message has been sent to $to successfully!";
    } catch (PDOException $e) {
        $_SESSION['message_status'] = "Error: " . $e->getMessage();
        error_log("Database error: " . $e->getMessage());
    }

    // إعادة التوجيه إلى الصفحة بعد إرسال الرسالة
    header("Location: employee.php");
    exit;
}
?>
