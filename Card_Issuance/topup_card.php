<?php
session_start();
// استدعاء ملف الاتصال الموحد وملف التشفير [cite: 2026-01-14]
require_once '../Shared/config.php'; 
require_once '../Shared/encryption_helper.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: ../User_Registration&Login/login_view.php");
    exit();
}

// تنظيف وتجهيز الـ User ID للتعامل مع BINARY(16)
$user_id_raw = $_SESSION['user_id'];
$error = "";

try {
    // 1. جلب بيانات البطاقة النشطة للمستخدم (مع جلب الحالة Status)
    $stmt_card = $pdo->prepare("SELECT * FROM issued_cards WHERE user_id = UNHEX(REPLACE(?, '-', '')) LIMIT 1");
    $stmt_card->execute([$user_id_raw]);
    $card = $stmt_card->fetch();

    if (!$card) {
        die("<div style='background:#0b1220; color:white; height:100vh; display:flex; align-items:center; justify-content:center; font-family:Tajawal; direction:rtl;'>❌ لم يتم العثور على بطاقة نشطة. يرجى إصدار بطاقة أولاً.</div>");
    }

    // التحقق إذا كانت البطاقة مجمدة
    if (isset($card['status']) && $card['status'] === 'Frozen') {
        $error = "🚫 لا يمكن إتمام عملية الشحن لأن البطاقة (مجمدة حالياً). يرجى تفعيلها من إعدادات البطاقة أولاً.";
    }

    // 2. جلب الحسابات البنكية المتاحة (يمني، سعودي، دولار)
    $stmt_acc = $pdo->prepare("SELECT HEX(account_id) as acc_id_hex, currency, balance FROM accounts WHERE user_id = UNHEX(REPLACE(?, '-', ''))");
    $stmt_acc->execute([$user_id_raw]);
    $accounts = $stmt_acc->fetchAll();

    // 3. معالجة طلب تحويل الرصيد للبطاقة (التحويل الداخلي)
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['amount'], $_POST['from_account'])) {
        
        if (isset($card['status']) && $card['status'] === 'Frozen') {
            throw new Exception("البطاقة مجمدة، لا يمكن تنفيذ عمليات مالية.");
        }

        $amount_to_add = floatval($_POST['amount']);
        $from_acc_hex = $_POST['from_account'];

        $stmt_check = $pdo->prepare("SELECT balance, currency FROM accounts WHERE account_id = UNHEX(?)");
        $stmt_check->execute([$from_acc_hex]);
        $source_acc = $stmt_check->fetch();

        if ($source_acc) {
            $current_balance = floatval($source_acc['balance']);
            
            if ($current_balance >= $amount_to_add && $amount_to_add > 0) {
                $pdo->beginTransaction();

                $stmt_deduct = $pdo->prepare("UPDATE accounts SET balance = balance - ? WHERE account_id = UNHEX(?)");
                $stmt_deduct->execute([$amount_to_add, $from_acc_hex]);

                $currency_key = trim(strtoupper($source_acc['currency']));
                $target_wallet = "";
                
                if ($currency_key === 'YER') $target_wallet = "balance_yer";
                elseif ($currency_key === 'SAR') $target_wallet = "balance_sar";
                elseif ($currency_key === 'USD') $target_wallet = "balance_usd";

                if (!empty($target_wallet)) {
                    $stmt_add = $pdo->prepare("UPDATE issued_cards SET $target_wallet = $target_wallet + ? WHERE card_id = ?");
                    $stmt_add->execute([$amount_to_add, $card['card_id']]);

                    $stmt_log = $pdo->prepare("INSERT INTO virtual_bank_transactions (account_id, amount, transaction_type, status) VALUES (UNHEX(?), ?, 'CARD_TOPUP', 'completed')");
                    $stmt_log->execute([$from_acc_hex, $amount_to_add]);

                    $pdo->commit();
                    header("Location: create_card_view.php?status=success");
                    exit();
                } else {
                    throw new Exception("عملة الحساب المختار غير مدعومة في نظام البطاقات الحالي.");
                }
            } else {
                $error = "عذراً، الرصيد المتاح هو (" . number_format($current_balance, 2) . ")، وهو غير كافٍ لإتمام عملية الشحن.";
            }
        } else {
            $error = "فشل في التعرف على بيانات الحساب المختار. يرجى إعادة المحاولة.";
        }
    }
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    if (empty($error)) $error = "⚠️ خطأ في النظام: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>شحن البطاقة | Yemen Gate</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Tajawal', sans-serif; background: #0b1220; color: white; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; }
        .card-container { background: #0f172a; padding: 30px; border-radius: 24px; width: 100%; max-width: 500px; border: 1px solid #1e293b; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); }
        .header-title { text-align: center; margin-bottom: 25px; }
        .header-title h2 { margin: 0; font-size: 22px; color: #f8fafc; }
        
        /* تصميم طرق الشحن الحقيقية [cite: 2026-01-13] */
        .real-methods { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 30px; }
        .method-item { background: #1e293b; padding: 15px; border-radius: 15px; text-align: center; border: 1px solid #334155; cursor: pointer; transition: 0.3s; text-decoration: none; color: white; }
        .method-item:hover { border-color: #06b6d4; background: #2d3748; transform: translateY(-3px); }
        .method-item i { font-size: 20px; color: #06b6d4; margin-bottom: 8px; display: block; }
        .method-item span { font-size: 12px; font-weight: bold; }
        .method-item.special { grid-column: span 2; background: rgba(6, 182, 212, 0.1); border-color: #06b6d4; }

        .divider { display: flex; align-items: center; text-align: center; margin: 20px 0; color: #475569; font-size: 12px; }
        .divider::before, .divider::after { content: ''; flex: 1; border-bottom: 1px solid #334155; }
        .divider:not(:empty)::before { margin-left: .5em; }
        .divider:not(:empty)::after { margin-right: .5em; }

        .alert-error { background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #fca5a5; padding: 12px; border-radius: 12px; margin-bottom: 20px; text-align: center; font-size: 13px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 8px; color: #94a3b8; font-size: 13px; }
        select, input { width: 100%; padding: 14px; border-radius: 12px; background: #1e293b; border: 1px solid #334155; color: white; font-size: 15px; box-sizing: border-box; }
        .submit-btn { width: 100%; padding: 16px; background: #06b6d4; border: none; border-radius: 12px; color: #0b1220; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .footer-link { text-align: center; margin-top: 20px; font-size: 13px; }
        .footer-link a { color: #94a3b8; text-decoration: none; }
    </style>
</head>
<body>
    <div class="card-container">
        <div class="header-title">
            <h2><i class="fa-solid fa-wallet"></i> شحن بطاقة يمن جت</h2>
            <p>اختر طريقة الشحن المناسبة لك</p>
        </div>

        <?php if($error): ?>
            <div class="alert-error"><?= $error ?></div>
        <?php endif; ?>

        <div class="real-methods">
            <a href="#" class="method-item">
                <i class="fa-brands fa-cc-visa"></i>
                <span>Visa / Master</span>
            </a>
            <a href="#" class="method-item">
                <i class="fa-brands fa-paypal"></i>
                <span>PayPal</span>
            </a>
            <a href="../Bank_Simulator/virtual_bank_gateway.php" class="method-item special">
                <i class="fa-solid fa-building-columns"></i>
                <span>الشحن عبر البنك الافتراضي (Simulator)</span>
            </a>
        </div>

        <div class="divider">أو تحويل من حساباتك الداخلية</div>

        <form method="POST">
            <div class="form-group">
                <label>من حساب:</label>
                <select name="from_account" required <?= ($card['status'] === 'Frozen') ? 'disabled' : '' ?>>
                    <option value="" disabled selected>-- اختر الحساب --</option>
                    <?php foreach($accounts as $acc): ?>
                        <option value="<?= $acc['acc_id_hex'] ?>">
                            <?= htmlspecialchars($acc['currency']) ?> - الرصيد: <?= number_format($acc['balance'], 2) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>المبلغ:</label>
                <input type="number" name="amount" step="0.01" min="0.10" placeholder="0.00" required <?= ($card['status'] === 'Frozen') ? 'disabled' : '' ?>>
            </div>

            <button type="submit" class="submit-btn" <?= ($card['status'] === 'Frozen') ? 'disabled' : '' ?>>
                <?= ($card['status'] === 'Frozen') ? 'البطاقة مجمدة' : 'إتمام التحويل الداخلي' ?>
            </button>
            
            <div class="footer-link">
                <a href="../User_Registration&Login/dashboard.php">🏠 العودة للرئيسية</a>
            </div>
        </form>
    </div>
</body>
</html>