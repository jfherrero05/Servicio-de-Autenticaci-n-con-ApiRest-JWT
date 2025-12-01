<?php
// api/welcome.php

// Configuración de cabeceras
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Manejo de petición OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 1. Obtener la cabecera de Autorización
$headers = null;
if (function_exists('apache_request_headers')) {
    $headers = apache_request_headers();
} else {
    $headers = getallheaders();
}

// Intentamos buscar la cabecera en diferentes variables (por compatibilidad de servidores)
$authHeader = $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? null;

// 2. Verificar si existe el token
if (!$authHeader) {
    http_response_code(401); // Unauthorized
    echo json_encode(['message' => 'No se proporcionó token de autorización']);
    exit();
}

// 3. Extraer el token (Quitar la palabra "Bearer ")
if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    $token = $matches[1];
} else {
    // Fallback simple
    $token = str_replace('Bearer ', '', $authHeader);
}

// 4. Decodificar el Token
// IMPORTANTE: Como en login.php usamos base64 simple, aquí decodificamos igual.
// En un sistema real JWT, aquí se verificaría la firma criptográfica.
$json_payload = base64_decode($token);
$payload = json_decode($json_payload, true);

// 5. Validar que el token sea correcto
if (!$payload || !isset($payload['username'])) {
    // Aquí es donde te daba el error antes:
    http_response_code(403); // Forbidden -> Esto dispara la redirección en tu HTML
    echo json_encode(['message' => 'Token inválido o corrupto']);
    exit();
}

// 6. Si todo está bien, devolvemos los datos para bienvenida.html
http_response_code(200);
echo json_encode([
    'message' => 'Token validado correctamente',
    'welcome_message' => '¡Bienvenido al Área Segura!',
    'username' => $payload['name'], // Usamos el nombre real del usuario
    'current_time' => date('d-m-Y H:i:s')
]);
?>