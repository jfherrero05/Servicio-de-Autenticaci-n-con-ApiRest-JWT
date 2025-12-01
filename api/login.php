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

// Array de usuarios para simular la base de datos
$usuarios = [
    ["username" => "admin", "password" => "1234", "name" => "Administrador del Sistema"],
    ["username" => "user", "password" => "abcd", "name" => "Usuario Básico"],
    ["username" => "Juanfran", "password" => "betii", "name" => "Juanfran"]
];

// --- CORRECCIÓN AQUÍ ---
// 1. Intentamos leer JSON (para APIs/Fetch)
$json_data = json_decode(file_get_contents("php://input"), true);

// 2. Asignamos variables: Si existe en JSON úsalo, si no, busca en el formulario normal ($_POST)
$input_user = $json_data['username'] ?? $_POST['username'] ?? '';
$input_pass = $json_data['password'] ?? $_POST['password'] ?? '';
// -----------------------

$authenticated_user = null;

// Validar credenciales
foreach ($usuarios as $user) {
    // Comprobación estricta (Ojo: Juanfran lleva J mayúscula)
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
    
    // Generar el Token usando base64_encode
    $token = base64_encode(json_encode($payload));

    // Respuesta exitosa (200 OK)
    http_response_code(200);
    echo json_encode(['token' => $token, 'message' => 'Login successful']);
} else {
    // Credenciales incorrectas (401 Unauthorized)
    http_response_code(401);
    // Agregamos debug para que veas qué está llegando (opcional, quítalo en producción)
    echo json_encode([
        'message' => 'Credenciales incorrectas', 
        'debug_user_received' => $input_user
    ]);
}
?>