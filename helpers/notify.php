<?php
/**
 * Professional Notification Hub
 * LogicERP Modular Framework
 */

/**
 * Send Email Notification
 */
function send_email($to, $subject, $message) {
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: LogicERP <no-reply@logicerp.com>\r\n";

    $template = "
    <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #eef2f6; border-radius: 12px;'>
      <h2 style='color: #4f46e5;'>LogicERP Alert</h2>
      <p style='color: #4b5563;'>$message</p>
      <hr style='border: 0; border-top: 1px solid #eef2f6; margin: 20px 0;'>
      <small style='color: #94a3b8;'>This is an automated message. Please do not reply.</small>
    </div>";

    // Uncomment for production: mail($to, $subject, $template, $headers);
    error_log("Email to $to: $subject - $message");
    return true;
}

/**
 * Send SMS Notification (Placeholder for Twilio/MSG91)
 */
function send_sms($number, $text) {
    // Logic for SMS API integration
    error_log("SMS to $number: $text");
    return true;
}

/**
 * Send WhatsApp Notification (Placeholder for Meta/Interakt)
 */
function send_whatsapp($number, $text) {
    // Logic for WhatsApp API integration
    error_log("WhatsApp to $number: $text");
    return true;
}

/**
 * Trigger Global Alert
 */
function trigger_notification($type, $target, $msg) {
    $subject = "System Notification | LogicERP"; 
    if ($type === 'email') return send_email($target, $subject, $msg);
    if ($type === 'sms') return send_sms($target, $msg);
    if ($type === 'whatsapp') return send_whatsapp($target, $msg);
}
?>
