<?php

class JwtHandler {
    private $conn;

    public function __construct() {
        require_once dirname(__FILE__) . '/DbConnect.php';
        require_once dirname(__FILE__) . '/SmsService.php';
        require_once dirname(__FILE__) . '/PasswordHash.php';
        // opening db connection
        date_default_timezone_set('UTC');
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    public function base64UrlEncode($data) {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    public function base64UrlDecode($data) {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }

    public function createJwt($payload, $secret) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode(json_encode($payload));
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public function validateJwt($jwt, $secret) {
        // Decode and verify the JWT
        list($header, $payload, $signature) = explode('.', $jwt);
        $validSignature = $this->base64UrlEncode(hash_hmac('sha256', $header . "." . $payload, $secret, true));
        if (!hash_equals($validSignature, $signature)) {
            return false;
        }

        $decodedPayload = json_decode($this->base64UrlDecode($payload), true);
        
        // Check if the token is present in the jwt_tokens table and is not expired
        $sql = "SELECT * FROM jwt_tokens WHERE token = ? AND expires_at > NOW()";
        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param("s", $jwt);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                return $decodedPayload;
            }
        }

        return false;
    }
}
?>
