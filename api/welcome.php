<?php
header("Content-Type: application/json");

$headers = getallheaders();
$auth = $headers["Authorization"] ?? "";

if (!$auth || !str_starts_with($auth, "Bearer ")) {
    http_response_code(403);
    echo json_encode(["error" => "Token no proporcionado"]);
    exit;
}

$token = str_replace("Bearer ", "", $auth);
$decoded = base64_decode($token);

if (!$decoded || !str_contains($decoded, "|")) {
    http_response_code(403);
    echo json_encode(["error" => "Token inválido"]);
    exit;
}

list($username, $time) = explode("|", $decoded);

echo json_encode([
    "username" => $username,
    "hora" => date("H:i:s"),
    "mensaje" => "Bienvenido a la zona segura"
]);
