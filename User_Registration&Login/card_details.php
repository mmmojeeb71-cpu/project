<?php
session_start();
require_once '../Shared/config.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login_view.php");
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // 1. جلب بيانات البطاقة الافتراضية للمستخدم
    $stmtCard = $pdo->prepare("SELECT * FROM issued_cards WHERE user_id = UNHEX(REPLACE(?, '-', '')) LIMIT 1");
    $stmtCard->execute([$user_id]);
    $card = $stmtCard->fetch();

    // --- منطق فك التشفير الاحترافي ---
    $encryption_key = "YG_SECRET_KEY_2026"; 
    $cipher = "AES-256-CBC";

    if ($card) {
        // فك تشفير رقم البطاقة (PAN)
        if (!empty($card['card_number']) && strpos($card['card_number'], ':') !== false) {
            list($encrypted_data, $iv) = explode(':', $card['card_number'], 2);
            $decrypted_card = openssl_decrypt($encrypted_data, $cipher, $encryption_key, 0, hex2bin($iv));
            if ($decrypted_card) {
                $card['card_number'] = $decrypted_card;
            }
        }

        // فك تشفير رمز التحقق (CVC)
        if (!empty($card['cvc']) && strpos($card['cvc'], ':') !== false) {
            list($encrypted_cvc, $iv_cvc) = explode(':', $card['cvc'], 2);
            $decrypted_cvc = openssl_decrypt($encrypted_cvc, $cipher, $encryption_key, 0, hex2bin($iv_cvc));
            if ($decrypted_cvc) {
                $card['cvc'] = $decrypted_cvc;
            }
        }
    }
    // -------------------------------

    // 2. جلب أرصدة الحسابات الثلاثة (YEM, SAR, USD) [cite: 2026-01-13]
    $stmtAcc = $pdo->prepare("SELECT currency, balance, account_number FROM accounts WHERE user_id = UNHEX(REPLACE(?, '-', ''))");
    $stmtAcc->execute([$user_id]);
    $accounts = $stmtAcc->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("خطأ في قاعدة البيانات: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل البطاقة الآمنة | Yemen Gate</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #06b6d4;
            --dark-bg: #0b1220;
            --card-glass: rgba(255, 255, 255, 0.05);
        }
        body {
            background-color: var(--dark-bg);
            color: white;
            font-family: 'Tajawal', sans-serif;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .details-container {
            width: 100%;
            max-width: 450px;
            background: var(--card-glass);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .header { text-align: center; margin-bottom: 30px; }
        .header h2 { color: var(--primary); margin: 0; font-size: 22px; }
        
        .info-grid {
            background: rgba(0, 0, 0, 0.2);
            padding: 20px;
            border-radius: 20px;
            margin-bottom: 25px;
        }
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .info-item:last-child { border-bottom: none; }
        .label { color: #94a3b8; font-size: 14px; }
        .value-wrapper { display: flex; align-items: center; gap: 10px; }
        .value { font-family: 'monospace'; color: #fff; font-weight: bold; letter-spacing: 1px; }
        
        .copy-btn {
            background: rgba(6, 182, 212, 0.1);
            border: none;
            color: var(--primary);
            cursor: pointer;
            padding: 5px 8px;
            border-radius: 5px;
            font-size: 12px;
            transition: 0.3s;
        }
        .copy-btn:hover { background: var(--primary); color: #000; }

        .accounts-section { margin-top: 20px; }
        .account-mini-card {
            display: flex;
            justify-content: space-between;
            background: rgba(255,255,255,0.03);
            padding: 10px 15px;
            border-radius: 12px;
            margin-bottom: 10px;
            border-right: 4px solid var(--primary);
        }

        .btn-action {
            display: block;
            width: 100%;
            padding: 15px;
            text-align: center;
            background: linear-gradient(90deg, #0891b2, #06b6d4);
            color: #000;
            text-decoration: none;
            border-radius: 15px;
            font-weight: bold;
            margin-top: 20px;
            transition: 0.3s;
        }
        .btn-action:hover { opacity: 0.9; transform: translateY(-2px); }
        .btn-secondary {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #94a3b8;
            text-decoration: none;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="details-container">
    <div class="header">
        <i class="fa-solid fa-shield-halved" style="font-size: 40px; color: var(--primary); margin-bottom: 15px;"></i>
        <h2>بيانات البطاقة الآمنة</h2>
        <p style="font-size: 12px; color: #64748b;">تم فك التشفير بنجاح [cite: 2026-01-13]</p>
    </div>

    <div class="info-grid">
        <div class="info-item">
            <span class="label">رقم البطاقة (PAN)</span>
            <div class="value-wrapper">
                <span class="value" id="cardNum"><?= htmlspecialchars($card['card_number'] ?? 'غير متوفر') ?></span>
                <button class="copy-btn" onclick="copyToClipboard('cardNum')" title="نسخ الرقم">
                    <i class="fa-regular fa-copy"></i>
                </button>
            </div>
        </div>
        <div class="info-item">
            <span class="label">تاريخ الانتهاء (Expiry)</span>
            <span class="value"><?= htmlspecialchars($card['expiry_date'] ?? '12/28') ?></span>
        </div>
        <div class="info-item">
            <span class="label">رمز التحقق (CVV)</span>
            <div class="value-wrapper">
                <span class="value" id="cvvNum" style="color: #06b6d4;"><?= htmlspecialchars($card['cvc'] ?? '123') ?></span>
                <button class="copy-btn" onclick="copyToClipboard('cvvNum')" title="نسخ رمز التحقق">
                    <i class="fa-regular fa-copy"></i>
                </button>
            </div>
        </div>
        <div class="info-item">
            <span class="label">حالة البطاقة</span>
            <span class="value" style="color: #10b981;">نشط / Active</span>
        </div>
    </div>

    <div class="accounts-section">
        <h4 style="margin-bottom: 15px; font-size: 14px; color: var(--primary);">الحسابات المرتبطة [cite: 2026-01-13]</h4>
        <?php if($accounts): foreach($accounts as $acc): ?>
            <div class="account-mini-card">
                <span style="font-size: 13px;"><?= htmlspecialchars($acc['currency']) ?></span>
                <span style="font-weight: bold;"><?= number_format($acc['balance'], 2) ?></span>
            </div>
        <?php endforeach; else: ?>
            <p style="font-size: 12px; color: #ef4444;">لا توجد حسابات نشطة حالياً.</p>
        <?php endif; ?>
    </div>

    <a href="dashboard.php" class="btn-action">العودة للوحة التحكم</a>
    <a href="#" class="btn-secondary" onclick="window.print()"><i class="fa-solid fa-print"></i> طباعة بيانات البطاقة</a>
</div>

<script>
function copyToClipboard(elementId) {
    const text = document.getElementById(elementId).innerText;
    navigator.clipboard.writeText(text).then(() => {
        const btn = event.currentTarget;
        const originalIcon = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-check"></i>';
        btn.style.color = "#10b981";
        
        setTimeout(() => {
            btn.innerHTML = originalIcon;
            btn.style.color = "";
        }, 2000);
    }).catch(err => {
        console.error('فشل النسخ: ', err);
    });
}
</script>

</body>
</html>