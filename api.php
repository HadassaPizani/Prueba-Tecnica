<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

header("Content-Type: application/json");
// Cargar configuracion
$config = require 'config.php';

try {
    $pdo = new PDO(
        "pgsql:host={$config['db_host']};dbname={$config['db_name']}",
        $config['db_user'],
        $config['db_pass']
    );
    // Para que lance excepciones en errores SQL
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al conectar con la base de datos: ' . $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$path = explode("/", $_GET['path'] ?? '');

if ($path[0] === 'tareas') {
    if ($method === 'GET') {
        if (isset($path[1])) {
            $stmt = $pdo->prepare("SELECT * FROM tareas WHERE id = ?");
            $stmt->execute([$path[1]]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($resultado) {
                echo json_encode($resultado);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Tarea no encontrada']);
            }
        } else {
            $stmt = $pdo->query("SELECT * FROM tareas");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        error_log("Datos recibidos POST: " . print_r($data, true));

        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos JSON inválidos o vacíos']);
            exit;
        }

        if (
            empty($data['titulo']) ||
            empty($data['descripcion']) ||
            empty($data['estado']) ||
            empty($data['usuario_id'])
        ) {
            http_response_code(400);
            echo json_encode(['error' => 'Faltan datos obligatorios']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO tareas (titulo, descripcion, estado, usuario_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$data['titulo'], $data['descripcion'], $data['estado'], $data['usuario_id']]);
            echo json_encode(["mensaje" => "Tarea creada"]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al insertar tarea: ' . $e->getMessage()]);
        }
    } elseif ($method === 'PUT' && isset($path[1])) {
        $data = json_decode(file_get_contents("php://input"), true);
        error_log("Datos recibidos PUT: " . print_r($data, true));

        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos JSON inválidos o vacíos']);
            exit;
        }

        if (
            empty($data['titulo']) ||
            empty($data['descripcion']) ||
            empty($data['estado'])
        ) {
            http_response_code(400);
            echo json_encode(['error' => 'Faltan datos obligatorios para actualizar']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("UPDATE tareas SET titulo=?, descripcion=?, estado=? WHERE id=?");
            $stmt->execute([$data['titulo'], $data['descripcion'], $data['estado'], $path[1]]);
            echo json_encode(["mensaje" => "Tarea actualizada"]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al actualizar tarea: ' . $e->getMessage()]);
        }
    } elseif ($method === 'DELETE' && isset($path[1])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM tareas WHERE id = ?");
            $stmt->execute([$path[1]]);
            echo json_encode(["mensaje" => "Tarea eliminada"]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar tarea: ' . $e->getMessage()]);
        }
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Ruta no encontrada"]);
    }
} else {
    http_response_code(404);
    echo json_encode(["error" => "Ruta no encontrada"]);
}
