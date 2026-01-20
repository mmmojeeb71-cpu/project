<?php
// تأكد من أن الطلب جاء عبر POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. استلام البيانات من المتجر
    $api_key  = $_POST['api_key'];
    $amount   = $_POST['amount'];
    $currency = $_POST['currency'];
    $method   = $_POST['payment_option'];
    
    // 2. إنشاء رقم طلب فريد للمتجر
    $merchant_order_id = "ORDER_" . time();

    // 3. تجهيز البيانات للإرسال لبوابة Yemen Gate
    // هنا نرسل البيانات إلى رابط البوابة الحقيقي (الذي برمجناه سابقاً)
    $gateway_url = "http://localhost/YEMEN_GATE/Payment_Gateway/process.php";

    // مصفوفة البيانات
    $data = [
        'api_key'    => $api_key,
        'order_id'   => $merchant_order_id,
        'amount'     => $amount,
        'currency'   => $currency,
        'method'     => $method,
        'return_url' => "http://localhost/my_store/success.php" // أين يعود العميل بعد النجاح
    ];

    // 4. استخدام cURL للتحقق من صحة المفتاح وبدء العملية
    // ملاحظة: للتجربة البسيطة، سنقوم بعمل "توجيه" (Redirect) مباشر للبوابة مع البيانات
    
    $query_string = http_build_query($data);
    header("Location: " . $gateway_url . "?" . $query_string);
    exit();
}
?>