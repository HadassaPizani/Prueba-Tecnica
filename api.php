<?php
// configuramos para mostrar solo errores graves 
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

header("Content-Type: application/json");

// se carga la configuracion de la base de datos desde un archivo externo
$config = require 'config.php';

// se conecta con la base de datos
try {
    $pdo = new PDO(
        "pgsql:host={$config['db_host']};dbname={$config['db_name']}",
        $config['db_user'],
        $config['db_pass']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'error al conectar con la base de datos: ' . $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
// Obtenemos la ruta enviada por GET para saber que recurso se esta solicitando
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
        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos json invalidos o vacios']);
            exit;
        }

        // verificamos que se hayan enviado todos los campos obligatorios
        if (
            empty($data['titulo']) ||
            empty($data['descripcion']) ||
            empty($data['estado']) ||
            empty($data['usuario_id'])
        ) {
            http_response_code(400);
            echo json_encode(['error' => 'Falta datos por completar']);
            exit;
        }

        // Validar que el usuario exista
        $stmtUser = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE id = ?");
        $stmtUser->execute([$data['usuario_id']]);
        $existeUsuario = $stmtUser->fetchColumn();

        if (!$existeUsuario) {
            http_response_code(400);
            echo json_encode(['error' => "El usuario con id {$data['usuario_id']} no existe"]);
            exit;
        }

        try {
            // insertamos la nueva tarea en la base de datos
            $stmt = $pdo->prepare("INSERT INTO tareas (titulo, descripcion, estado, usuario_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$data['titulo'], $data['descripcion'], $data['estado'], $data['usuario_id']]);
            echo json_encode(["mensaje" => "Tarea creada"]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'error al insertar tarea: ' . $e->getMessage()]);
        }
    } elseif ($method === 'PUT' && isset($path[1])) {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos json invalidos o vacios']);
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
            // actualizamos la tarea con los nuevos datos
            $stmt = $pdo->prepare("UPDATE tareas SET titulo=?, descripcion=?, estado=? WHERE id=?");
            $stmt->execute([$data['titulo'], $data['descripcion'], $data['estado'], $path[1]]);
            echo json_encode(["mensaje" => "Tarea actualizada"]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'error al actualizar tarea: ' . $e->getMessage()]);
        }
    } elseif ($method === 'DELETE' && isset($path[1])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM tareas WHERE id = ?");
            $stmt->execute([$path[1]]);
            echo json_encode(["mensaje" => "Tarea eliminada"]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'error al eliminar tarea: ' . $e->getMessage()]);
        }
        // si no coincide ninguna ruta devuelve un error 404
    } else {
        http_response_code(404);
        echo json_encode(["error" => "ruta no encontrada"]);
    }
    // si la ruta no comienza con tareas devolvemos error 404
} else {
    http_response_code(404);
    echo json_encode(["error" => "ruta no encontrada"]);
}
