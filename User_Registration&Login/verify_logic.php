<?php
session_start();
require_once '../Shared/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_otp = $_POST['otp_code'];
    
    // في النظام الحقيقي، نقوم بمقارنة الكود المرسل للإيميل. هنا سنستخدم 123456 للتجربة
    if ($user_otp === '123456') {
        $_SESSION['user_id'] = $_SESSION['temp_user_id']; // تفعيل الجلسة الحقيقية
        unset($_SESSION['temp_user_id']);
        header("Location: dashboard.php");
    } else {
        die("❌ رمز التحقق غير صحيح!");
    }
}