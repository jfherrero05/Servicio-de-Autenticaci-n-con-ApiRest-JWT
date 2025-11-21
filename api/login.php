<?php
// api/login.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// 1. Lista de usuarios (Simulación de Base de Datos) [cite: 90]
$usuarios = [
    "admin" => "1234",
    "usuario" => "abcd",
    "paco" => "paco"
];

// 2. Leer entrada JSON
$data = json_decode(file_get_contents("php://input"), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

// 3. Validar credenciales
if (isset($usuarios[$username]) && $usuarios[$username] === $password) {
    
    // --- GENERACIÓN MANUAL DEL JWT ---
    
    // A. Definir Header y Payload
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload = json_encode([
        'sub' => $username,      // Usuario
        'iat' => time(),         // Creación
        'exp' => time() + 3600   // Expiración (1 hora)
    ]);

    // B. Funciones para codificar en Base64Url (Requisito estándar JWT)
    function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    // C. Codificar partes
    $base64UrlHeader = base64UrlEncode($header);
    $base64UrlPayload = base64UrlEncode($payload);

    // D. Crear la Firma (Signature) usando HMAC SHA256
    $secret = 'tu_clave_secreta_betis'; // En producción esto se guarda en variables de entorno
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
    $base64UrlSignature = base64UrlEncode($signature);

    // E. Unir todo el token
    $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

    // 4. Respuesta exitosa con Token [cite: 58, 82]
    echo json_encode(["status" => "success", "token" => $jwt]);

} else {
    // 5. Respuesta de error (401 Unauthorized) [cite: 59]
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Credenciales incorrectas"]);
}
?>