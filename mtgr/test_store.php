<?php
// متجر إلكتروني تجريبي - اليمن جت
$error_message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        if (empty($_POST['amount']) || empty($_POST['currency']) || empty($_POST['payment_option'])) {
            throw new Exception("⚠️ يرجى إدخال جميع الحقول المطلوبة بشكل صحيح.");
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>متجري التجريبي - تجربة الدفع الآمن</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; color: #1e293b; }
        .store-container { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 400px; text-align: center; border-top: 5px solid #1a73e8; }
        .product-img { width: 100px; height: 100px; background: #e2e8f0; border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; font-size: 40px; }
        .payment-form { background: #f1f5f9; padding: 20px; border-radius: 10px; margin-top: 20px; text-align: right; }
        input, select { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #cbd5e1; border-radius: 8px; box-sizing: border-box; font-size: 14px; }
        .methods-selector label { display: block; background: white; padding: 10px; margin: 5px 0; border-radius: 8px; border: 1px solid #e2e8f0; cursor: pointer; font-size: 14px; transition: 0.2s; }
        .methods-selector label:hover { background: #f8fafc; border-color: #1a73e8; }
        .btn-checkout { background: #1a73e8; color: white; border: none; padding: 15px; width: 100%; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 16px; transition: 0.3s; margin-top: 10px; }
        .btn-checkout:hover { background: #1557b0; }
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 15px; font-size: 14px; font-weight: bold; }
        .alert-danger { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
    </style>
</head>
<body>

<div class="store-container">
    <div class="product-img">📱</div>
    <h2>هاتف ذكي بلس</h2>
    <p style="color: #64748b; font-size: 14px;">منتج تجريبي لاختبار بوابة دفع اليمن جت العالمية.</p>

    <div class="payment-form">
        <?php if($error_message): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
        <?php endif; ?>

        <form action="http://localhost/YEMEN_GATE/Payment_Gateway/checkout.php" method="POST">
            <input type="hidden" name="api_key" value="YG_LIVE_9F611C242EA5C53B83B90AA9">
            
            <label>حدد السعر والعملة للطلب:</label>
            <div style="display: flex; gap: 5px;">
                <input type="number" name="amount" value="10.00" step="0.01" required style="flex: 2;">
                <select name="currency" style="flex: 1;">
                    <option value="USD">USD</option>
                    <option value="SAR">SAR</option>
                    <option value="YER">YER</option>
                </select>
            </div>

            <div class="methods-selector" style="margin-top:15px;">
                <p style="font-size: 13px; margin-bottom: 5px; font-weight: bold;">اختر وسيلة الدفع:</p>
                <label>
                    <input type="radio" name="payment_option" value="card" checked> 
                    💳 بطاقة يمن جت الإلكترونية
                </label>
                <label>
                    <input type="radio" name="payment_option" value="bank"> 
                    🏦 تحويل مباشر للحساب
                </label>
            </div>

            <button type="submit" class="btn-checkout">
                <i class="fa-solid fa-lock"></i> دفع آمن عبر اليمن جت
            </button>
        </form>
    </div>
    
    <p style="font-size: 11px; color: #94a3b8; margin-top: 15px;">تتم معالجة جميع المدفوعات عبر نظام اليمن جت المشفر.</p>
</div>

</body>
</html>