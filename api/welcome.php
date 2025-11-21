<?php
// api/welcome.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// 1. Obtener cabeceras de la petición
$headers = null;
if (isset($_SERVER['Authorization'])) {
    $headers = trim($_SERVER["Authorization"]);
} else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
} elseif (function_exists('apache_request_headers')) {
    $requestHeaders = apache_request_headers();
    if (isset($requestHeaders['Authorization'])) {
        $headers = trim($requestHeaders['Authorization']);
    }
}

// 2. Extraer el token (Bearer <token>) 
$token = null;
if (!empty($headers) && preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
    $token = $matches[1];
}

if (!$token) {
    http_response_code(403); // [cite: 68]
    echo json_encode(["message" => "No se proporcionó token"]);
    exit();
}

// --- VALIDACIÓN MANUAL DEL JWT ---

// 3. Separar el token en partes
$parts = explode('.', $token);
if (count($parts) !== 3) {
    http_response_code(403);
    echo json_encode(["message" => "Token mal formado"]);
    exit();
}

list($header, $payload, $signature) = $parts;

// 4. Recrear la firma para verificar autenticidad
$secret = 'tu_clave_secreta_betis'; // Debe ser la misma clave que en login.php

function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// Recalcular firma con los datos recibidos
$valid_signature = hash_hmac('sha256', $header . "." . $payload, $secret, true);
$base64UrlValidSignature = base64UrlEncode($valid_signature);

// 5. Comparar firmas
if ($base64UrlValidSignature === $signature) {
    // Firma válida: Decodificar payload
    $payloadData = json_decode(base64_decode($payload), true);

    // Verificar expiración
    if (isset($payloadData['exp']) && $payloadData['exp'] < time()) {
        http_response_code(401); // Token expirado
        echo json_encode(["message" => "El token ha expirado"]);
        exit();
    }

    // 6. Retornar datos de bienvenida [cite: 61, 81]
    echo json_encode([
        "status" => "success",
        "usuario" => $payloadData['sub'],
        "hora" => date("H:i:s"),
        "mensaje" => "Bienvenido a la zona segura de la API"
    ]);
} else {
    // Firma inválida
    http_response_code(403);
    echo json_encode(["message" => "Firma del token inválida"]);
}
?>