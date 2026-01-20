<?php
// استلام رقم العملية من الرابط بعد عودة العميل من البوابة
$transaction_id = $_GET['trans_id']; 
$order_id = $_GET['order_id'];

// الأمان: نقوم بالاستعلام من سيرفر البوابة مباشرة (Server-to-Server)
$verify_url = "https://yemengate.com/api/v1/check-status"; // رابط الاستعلام في البوابة

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $verify_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'api_key' => 'YOUR_MERCHANT_API_KEY',
    'transaction_id' => $transaction_id
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$result = json_decode($response, true);

if ($result['status'] == 'Paid') {
    // هنا فقط نحدث قاعدة بيانات المتجر ونؤكد الطلب
    echo "<h1>✅ تم الدفع بنجاح! شكراً لك.</h1>";
    echo "رقم العملية: " . $transaction_id;
    // تحديث الحالة في DB إلى 'Success'
} else {
    echo "<h1>❌ فشلت عملية الدفع أو أنها معلقة.</h1>";
}
?>