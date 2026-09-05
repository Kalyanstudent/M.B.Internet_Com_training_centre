<?php
/**
 * SkillPoint Institute Settings Model
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';

class Setting {
    public static function getAll(): array {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
        $rows = $stmt->fetchAll();

        $settings = [
            'instituteName'   => 'SkillPoint Computer Training Centre',
            'tagline'         => 'Professional Computer Training & Digital Tax Services',
            'phone'           => '+91 98765 43210',
            'whatsapp'        => '+91 98765 43210',
            'email'           => 'info@skillpointcentre.com',
            'address'         => '2nd Floor, Sai Commercial Complex, Station Road, Opp. Municipal Garden, Maharashtra - 400001',
            'gstin'           => '27AAACS1234F1Z5',
            'registrationNo'  => 'MH/EDU/2015/8842',
            'workingHours'    => 'Monday – Saturday: 08:00 AM – 08:00 PM (Sunday Closed)',
            'razorpayKeyId'   => RAZORPAY_KEY_ID
        ];

        foreach ($rows as $r) {
            $key = match ($r['setting_key']) {
                'institute_name'    => 'instituteName',
                'tagline'           => 'tagline',
                'phone'             => 'phone',
                'whatsapp'          => 'whatsapp',
                'email'             => 'email',
                'address'           => 'address',
                'gstin'             => 'gstin',
                'registration_no'   => 'registrationNo',
                'working_hours'     => 'workingHours',
                'razorpay_key_id'   => 'razorpayKeyId',
                default             => $r['setting_key']
            };
            $settings[$key] = $r['setting_value'];
        }

        return $settings;
    }

    public static function saveAll(array $data): bool {
        $db = Database::getConnection();

        $map = [
            'instituteName'   => 'institute_name',
            'tagline'         => 'tagline',
            'phone'           => 'phone',
            'whatsapp'        => 'whatsapp',
            'email'           => 'email',
            'address'         => 'address',
            'gstin'           => 'gstin',
            'registrationNo'  => 'registration_no',
            'workingHours'    => 'working_hours',
            'razorpayKeyId'   => 'razorpay_key_id'
        ];

        $stmt = $db->prepare("
            INSERT INTO settings (setting_key, setting_value, updated_at) 
            VALUES (:k, :v, NOW())
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()
        ");

        foreach ($data as $camelKey => $val) {
            $dbKey = $map[$camelKey] ?? $camelKey;
            $stmt->execute([':k' => $dbKey, ':v' => (string)$val]);
        }

        return true;
    }
}
