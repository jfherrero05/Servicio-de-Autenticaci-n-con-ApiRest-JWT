<?php
// Configuración de cabeceras para CORS y JSON
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Responde a las peticiones OPTIONS (preflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 1. Obtener la cabecera de autorización (Authorization: Bearer <token>) [cite: 90]
$auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

if (preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
    $token = $matches[1];
    $payload_json = base64_decode($token);
    $payload = json_decode($payload_json, true);

    // Validación: Chequea si el token se decodifica y contiene datos de usuario
    if ($payload && isset($payload['username'])) {
        // Token válido. Responder con datos del usuario [cite: 66, 67]
        $user_name = $payload['name'] ?? $payload['username'];
        $response = [
            'message' => "Acceso permitido. Los datos se han cargado desde el API.",
            'username' => $user_name,
            'current_time' => date('H:i:s'), // Hora actual [cite: 66]
            'welcome_message' => "¡Bienvenido/a, {$user_name}!" // Mensaje personalizado [cite: 67]
        ];
        http_response_code(200);
        echo json_encode($response);
    } else {
        // Token inválido (aunque presente)
        http_response_code(403); // Forbidden [cite: 72]
        echo json_encode(['message' => 'Token inválido o expirado. Acceso denegado.']);
    }
} else {
    // No hay cabecera Authorization
    http_response_code(403); // Forbidden [cite: 72]
    echo json_encode(['message' => 'Acceso denegado. Se requiere un token de autenticación.']);
}
?>