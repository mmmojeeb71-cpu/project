<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>متجر اليمن جت التجريبي</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .payment-card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 400px; text-align: center; }
        .payment-card h2 { color: #2c3e50; margin-bottom: 20px; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        .form-group { margin-bottom: 15px; text-align: right; }
        input[type="number"], select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .methods-selector { background: #f9f9f9; padding: 15px; border-radius: 8px; border: 1px dashed #3498db; }
        .methods-selector label { display: block; margin-bottom: 10px; cursor: pointer; font-size: 0.9em; }
        button { background: #27ae60; color: white; border: none; padding: 12px 25px; border-radius: 5px; cursor: pointer; width: 100%; font-size: 1.1em; transition: 0.3s; }
        button:hover { background: #219150; }
    </style>
</head>
<body>

<div class="payment-card">
    <h2>Yemen Gate Global</h2>
    <form action="checkout.php" method="POST">
        <input type="hidden" name="api_key" value="YG_LIVE_5570C52F531F4B7400AD2811">
        
        <div class="form-group">
            <label>حدد مبلغ الشراء:</label>
            <input type="number" name="amount" step="0.01" placeholder="0.00" required>
        </div>

        <div class="form-group">
            <label>عملة الحساب:</label>
            <select name="currency">
                <option value="USD">دولار أمريكي (USD)</option>
                <option value="SAR">ريال سعودي (SAR)</option>
                <option value="YER">ريال يمني (YER)</option>
            </select>
        </div>

        <div class="methods-selector">
            <p style="margin-top:0;">اختر وسيلة الدفع:</p>
            <label>
                <input type="radio" name="payment_option" value="card" checked> 💳 بطاقة يمن جت الإلكترونية
            </label>
            <label>
                <input type="radio" name="payment_option" value="bank"> 🏦 تحويل مباشر للحساب (رقم الحساب)
            </label>
        </div>

        <button type="submit" style="margin-top:20px;">إتمام الدفع الآن</button>
    </form>
</div>

</body>
</html>