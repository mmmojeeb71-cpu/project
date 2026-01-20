<?php
// 1. استلام بيانات العودة من بوابة Yemen Gate
$status = $_GET['status'] ?? '';
$transaction_id = $_GET['transaction_id'] ?? '';
$order_id = $_GET['order_id'] ?? '';
$amount = $_GET['amount'] ?? '';

// 2. التحقق الأمني (هذا هو جوهر سؤالك: هل فعلاً دفع؟)
// نقوم بالاتصال بـ API البوابة للتأكد من حالة هذه العملية يدوياً
$api_url = "http://localhost/YEMEN_GATE/api/verify_payment.php";

$verify_data = [
    'api_key' => 'YG_LIVE_5570C52F531F4B7400AD2811',
    'transaction_id' => $transaction_id
];

// استخدام cURL لطلب التأكيد من البوابة
$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $verify_data);
$response = curl_exec($ch);
$result = json_decode($response, true);

// 3. اتخاذ القرار بناءً على رد البوابة الرسمي وليس رابط المتصفح
if ($result && $result['status'] === 'SUCCESS') {
    echo "<h2>✅ تم التحقق! المبلغ ($amount) وصل إلى حسابك بنجاح.</h2>";
    echo "رقم العملية المرجعي: " . $transaction_id;
    // هنا تضع كود إرسال المنتج للعميل أو تفعيل اشتراكه
} else {
    echo "<h2>❌ تنبيه: فشل التحقق من عملية الدفع.</h2>";
    echo "السبب: " . ($result['message'] ?? 'بيانات غير مطابقة');
}
?>