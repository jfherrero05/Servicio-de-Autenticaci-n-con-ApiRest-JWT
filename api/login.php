<?php
header("Content-Type: application/json");

$usuarios = [
    ["username" => "admin", "password" => "1234"],
    ["username" => "user",  "password" => "abcd"],
    ["username" => "juanfran", "password" => "juanfran"]
];

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

$username = trim($data["username"] ?? "");
$password = trim($data["password"] ?? "");

foreach ($usuarios as $u) {
    if ($u["username"] === $username && $u["password"] === $password) {
        $token = base64_encode($username . "|" . time());
        echo json_encode(["token" => $token, "username" => $username]);
        exit;
    }
}

http_response_code(401);
echo json_encode(["error" => "Credenciales incorrectas"]);
