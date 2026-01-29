<?php
include_once "cors.php";
include_once "conexion.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id            = isset($_POST['id']) ? (int)$_POST['id'] : null;
    $nombre        = $_POST['nombre'] ?? '';
    $apellido      = $_POST['apellido'] ?? '';
    $email         = $_POST['email'] ?? '';
    $direccion     = $_POST['direccion'] ?? '';
    $ciudad        = $_POST['ciudad'] ?? '';
    $codigo_postal = $_POST['codigo_postal'] ?? '';
    $telefono      = $_POST['telefono'] ?? '';
    $rol_id        = isset($_POST['rol_id']) ? (int)$_POST['rol_id'] : 2;
    $activo        = isset($_POST['activo']) ? (int)$_POST['activo'] : 0;

    try {
        if (!$id) {
            throw new Exception("ID de usuario no proporcionado.");
        }

        /**
         * SEGURIDAD: Evitar desactivar al Admin Principal (ID 1)
         * Ajusta este ID si tu administrador principal tiene otro.
         */
        if ($id === 1 && $activo === 0) {
            throw new Exception("Seguridad: El administrador principal no puede ser desactivado.");
        }

        $sql = "UPDATE usuarios SET 
                nombre = ?, apellido = ?, email = ?, direccion = ?, 
                ciudad = ?, codigo_postal = ?, telefono = ?, 
                rol_id = ?, activo = ? 
                WHERE id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssssssiii", 
            $nombre, $apellido, $email, $direccion, 
            $ciudad, $codigo_postal, $telefono, $rol_id, 
            $activo, $id
        );

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "mensaje" => "Perfil actualizado correctamente"]);
        } else {
            throw new Exception($stmt->error);
        }

        $stmt->close();
    } catch (Exception $e) {
        http_response_code(403); // Forbidden para errores de seguridad
        echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
    }
    
    $conexion->close();
}
?>