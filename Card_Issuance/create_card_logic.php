<?php
session_start();
// استدعاء ملف الاتصال فقط (تم الاستغناء عن helper التشفير مؤقتاً)
require_once '../Shared/config.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: ../User_Registration&Login/login_view.php");
    exit();
}

try {
    $user_id = $_SESSION['user_id'];

    // جلب الحسابات الثلاثة للتأكد من وجود حساب الدولار للبطاقة
    $stmt = $pdo->prepare("SELECT u.full_name, a.account_id 
                           FROM users u 
                           JOIN accounts a ON u.user_id = a.user_id 
                           WHERE u.user_id = UNHEX(REPLACE(?, '-', '')) 
                           AND a.currency = 'USD' 
                           LIMIT 1");
    $stmt->execute([$user_id]);
    $user_info = $stmt->fetch();

    if (!$user_info) {
        header("Location: ../User_Registration&Login/dashboard.php?error=need_usd_account");
        exit();
    }

    // توليد بيانات البطاقة الحقيقية
    $bin = "426398"; 
    $card_number = $bin . substr(str_shuffle("01234567890123456789"), 0, 10);
    $masked_pan = substr($card_number, 0, 4) . " **** **** " . substr($card_number, -4);
    $cvv = (string)rand(100, 999);
    $expiry_month = date('m');
    $expiry_year = date('Y') + 3;

    // --- [تم إزالة التشفير للمرحلة التجريبية] ---
    // نستخدم المتغيرات الأصلية مباشرة $card_number و $cvv
    // ------------------------------------------

    // حفظ البطاقة في قاعدة البيانات
    $card_id = bin2hex(random_bytes(16));
    
    // إدراج البطاقة مع دعم محافظ العملات الثلاث (YER, SAR, USD)
    $sql = "INSERT INTO issued_cards (
                card_id, user_id, account_id, card_number, card_holder, 
                masked_pan, expiry_month, expiry_year, cvv, card_type, 
                card_balance, balance_yer, balance_sar, balance_usd
            ) 
            VALUES (
                UNHEX(?), UNHEX(REPLACE(?, '-', '')), ?, ?, ?, 
                ?, ?, ?, ?, 'YEMEN GATE PREMIUM', 
                0.00, 0.00, 0.00, 0.00
            )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $card_id, 
        $user_id, 
        $user_info['account_id'], 
        $card_number, // تم الحفظ كنص عادي
        strtoupper($user_info['full_name']), 
        $masked_pan, 
        $expiry_month, 
        $expiry_year, 
        $cvv // تم الحفظ كنص عادي
    ]);

    // التوجيه لصفحة العرض مع إشارة النجاح
    header("Location: create_card_view.php?status=success");
    exit();

} catch (PDOException $e) {
    die("خطأ فني في إصدار البطاقة: " . $e->getMessage());
}
?>