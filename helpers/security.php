<?php
/**
 * Core Security Helpers
 * LogicERP Modular Framework
 */

// CSRF Handling
function get_csrf_token() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return !empty($token) && hash_equals($_SESSION['csrf_token'], $token);
}

// XSS Protection
function xss_clean($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = xss_clean($value);
        }
        return $data;
    }
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// AES-256 Encryption for IDs and sensitive URLs
define('ENC_KEY', '759f23932e65d83624f1f2eefe9f3152'); // Replace with a secure key in production
define('ENC_METHOD', 'AES-256-CBC');

function encrypt_id($id) {
    if (empty($id)) return null;
    $iv_length = openssl_cipher_iv_length(ENC_METHOD);
    $iv = openssl_random_pseudo_bytes($iv_length);
    $encrypted = openssl_encrypt((string)$id, ENC_METHOD, ENC_KEY, 0, $iv);
    // URL-safe Base64 encode
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($iv . $encrypted));
}

function decrypt_id($val) {
    if (empty($val)) return null;
    // Restore Base64 characters
    $val = str_replace(['-', '_'], ['+', '/'], $val);
    $data = base64_decode($val);
    if (!$data) return null;
    $iv_length = openssl_cipher_iv_length(ENC_METHOD);
    $iv = substr($data, 0, $iv_length);
    $encrypted = substr($data, $iv_length);
    return openssl_decrypt($encrypted, ENC_METHOD, ENC_KEY, 0, $iv);
}

/**
 * Redirect with a message
 */
function redirect($url, $msg = "", $type = "success") {
    if (!empty($msg)) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash_msg'] = $msg;
        $_SESSION['flash_type'] = $type;
    }
    header("Location: $url");
    exit();
}

/**
 * Flash message helper for UI
 */
function display_flash() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['flash_msg'])) {
        $msg = $_SESSION['flash_msg'];
        $type = $_SESSION['flash_type'];
        unset($_SESSION['flash_msg']);
        unset($_SESSION['flash_type']);
        return "<div class='alert alert-$type alert-dismissible fade show' role='alert'>
                    $msg
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>";
    }
    return "";
}
?>
