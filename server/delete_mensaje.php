<?php
include_once "cors.php";
include_once "conexion.php";

header('Content-Type: application/json');

// Obtenemos el ID del mensaje, ya sea por POST o por el cuerpo de la petición
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id > 0) {
    try {
        $stmt = $conexion->prepare("DELETE FROM mensajes_contacto WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "mensaje" => "Mensaje eliminado correctamente"]);
        } else {
            echo json_encode(["status" => "error", "mensaje" => "No se pudo eliminar el mensaje"]);
        }
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "mensaje" => "ID no válido"]);
}

$conexion->close();
?>