<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Twilio\Rest\Client;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $contact = isset($_POST['contact']) ? trim($_POST['contact']) : '';
    $name    = isset($_POST['name']) ? trim($_POST['name']) : 'Customer';

    // Load credentials from environment variables for security
    $sid   = getenv('TWILIO_SID');
    $token = getenv('TWILIO_TOKEN');
    $from  = getenv('TWILIO_FROM'); // Your Twilio phone number in E.164, e.g. +15005550006 (trial test number)

    // Validate credentials before attempting API call
    if (empty($sid) || empty($token) || empty($from)) {
        echo "<script>alert('SMS not configured: Set TWILIO_SID, TWILIO_TOKEN, TWILIO_FROM environment variables.'); window.history.back();</script>";
        exit;
    }

    // Basic phone normalization: keep digits only, then convert to +63 format if local PH number provided
    $digitsOnly = preg_replace('/[^0-9]/', '', $contact);
    if (!$digitsOnly) {
        echo "<script>alert('Invalid recipient number.'); window.history.back();</script>";
        exit;
    }

    // Convert common PH formats to E.164 (+63...)
    if (str_starts_with($digitsOnly, '0')) {
        $to = '+63' . substr($digitsOnly, 1);
    } elseif (str_starts_with($digitsOnly, '63')) {
        $to = '+' . $digitsOnly;
    } elseif (str_starts_with($digitsOnly, '9') && strlen($digitsOnly) === 10) {
        $to = '+63' . $digitsOnly;
    } elseif (str_starts_with($digitsOnly, '1') || str_starts_with($digitsOnly, '2') || str_starts_with($digitsOnly, '3') || str_starts_with($digitsOnly, '4') || str_starts_with($digitsOnly, '5') || str_starts_with($digitsOnly, '6') || str_starts_with($digitsOnly, '7') || str_starts_with($digitsOnly, '8')) {
        // Assume already in country code form without +
        $to = '+' . $digitsOnly;
    } else {
        $to = '+' . $digitsOnly; // Fallback
    }

    $twilio = new Client($sid, $token);

    $message = "Hello $name, this is a reminder from Bulan Veterinary Clinic. Please keep your pets healthy!";

    try {
        $twilio->messages->create(
            $to,
            [
                "from" => $from,
                "body" => $message
            ]
        );
        echo "<script>alert('SMS sent successfully to $name'); window.history.back();</script>";
    } catch (Exception $e) {
        $safeMsg = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        echo "<script>alert('Failed: $safeMsg'); window.history.back();</script>";
    }
}
