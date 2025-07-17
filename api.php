<?php
header("Content-Type: application/json");
$pdo = new PDO("pgsql:host=localhost;dbname=todo", "usuario", "contraseña");

$method = $_SERVER['REQUEST_METHOD'];
$path = explode("/", $_GET['path'] ?? '');

if ($path[0] === 'tareas') {
    if ($method === 'GET') {
        if (isset($path[1])) {
            $stmt = $pdo->prepare("SELECT * FROM tareas WHERE id = ?");
            $stmt->execute([$path[1]]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } else {
            $stmt = $pdo->query("SELECT * FROM tareas");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $pdo->prepare("INSERT INTO tareas (titulo, descripcion, estado, usuario_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['titulo'], $data['descripcion'], $data['estado'], $data['usuario_id']]);
        echo json_encode(["mensaje" => "Tarea creada"]);
    } elseif ($method === 'PUT' && isset($path[1])) {
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $pdo->prepare("UPDATE tareas SET titulo=?, descripcion=?, estado=? WHERE id=?");
        $stmt->execute([$data['titulo'], $data['descripcion'], $data['estado'], $path[1]]);
        echo json_encode(["mensaje" => "Tarea actualizada"]);
    } elseif ($method === 'DELETE' && isset($path[1])) {
        $stmt = $pdo->prepare("DELETE FROM tareas WHERE id = ?");
        $stmt->execute([$path[1]]);
        echo json_encode(["mensaje" => "Tarea eliminada"]);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Ruta no encontrada"]);
    }
}
?>