<?php
// Configuración de cabeceras para CORS y JSON
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Responde a las peticiones OPTIONS (preflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Array de usuarios para simular la base de datos [cite: 94, 95]
$usuarios = [
    ["username" => "admin", "password" => "1234", "name" => "Administrador del Sistema"],
    ["username" => "user", "password" => "abcd", "name" => "Usuario Básico"],
    ["username" => "Juanfran", "password" => "betii", "name" => "Juanfran"]
];

// Obtener los datos JSON (cuerpo de la petición)
$data = json_decode(file_get_contents("php://input"), true);
$input_user = $data['username'] ?? '';
$input_pass = $data['password'] ?? '';

$authenticated_user = null;

// Validar credenciales
foreach ($usuarios as $user) {
    if ($user['username'] === $input_user && $user['password'] === $input_pass) {
        $authenticated_user = $user;
        break;
    }
}

if ($authenticated_user) {
    // Payload del token (información para identificar al usuario)
    $payload = [
        'username' => $authenticated_user['username'],
        'name' => $authenticated_user['name'],
        'iat' => time() // Issued At
    ];
    
    // Generar el Token usando base64_encode [cite: 62]
    $token = base64_encode(json_encode($payload));

    // Respuesta exitosa (200 OK)
    http_response_code(200);
    echo json_encode(['token' => $token, 'message' => 'Login successful']);
} else {
    // Credenciales incorrectas (401 Unauthorized) [cite: 63]
    http_response_code(401);
    echo json_encode(['message' => 'Credenciales incorrectas']);
}
?>