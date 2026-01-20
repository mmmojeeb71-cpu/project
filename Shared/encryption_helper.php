<?php
/**
 * نظام التشفير المتقدم (AES-256-CBC)
 * يستخدم لتشفير أرقام البطاقات والبيانات الحساسة قبل تخزينها في قاعدة البيانات
 */

class EncryptionHelper {
    // مفتاح التشفير (يجب تغييره في الإنتاج وحفظه في مكان آمن جداً)
    private static $key = 'yemen_gate_secure_key_2026_top_secret'; 
    private static $method = 'aes-256-cbc';

    /**
     * تشفير البيانات
     */
    public static function encrypt($data) {
        $iv_length = openssl_cipher_iv_length(self::$method);
        $iv = openssl_random_pseudo_bytes($iv_length);
        $encrypted = openssl_encrypt($data, self::$method, self::$key, 0, $iv);
        // ندمج الـ IV مع البيانات المشفرة لاستخدامه عند فك التشفير
        return base64_encode($encrypted . '::' . $iv);
    }

    /**
     * فك تشفير البيانات
     */
    public static function decrypt($data) {
        $data = base64_decode($data);
        if (strpos($data, '::') !== false) {
            list($encrypted_data, $iv) = explode('::', $data, 2);
            return openssl_decrypt($encrypted_data, self::$method, self::$key, 0, $iv);
        }
        return false;
    }
}
?>