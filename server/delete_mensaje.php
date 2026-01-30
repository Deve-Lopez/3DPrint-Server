<?php
/**
 * Script: delete_mensaje.php
 * Finalidad: Eliminar registros de la tabla mensajes_contacto desde el panel de gestión.
 */

include_once "cors.php";
include_once "conexion.php";

header('Content-Type: application/json');

// Captura del ID del mensaje y validación inicial de tipo entero
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id > 0) {
    try {
        // Uso de sentencia preparada para seguridad contra Inyección SQL
        $stmt = $conexion->prepare("DELETE FROM mensajes_contacto WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "mensaje" => "Mensaje eliminado correctamente"]);
        } else {
            echo json_encode(["status" => "error", "mensaje" => "No se pudo eliminar el mensaje"]);
        }
        $stmt->close();
    } catch (Exception $e) {
        // Manejo de excepciones y reporte de error en formato JSON
        echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "mensaje" => "ID no válido"]);
}

// Cierre de la conexión al motor de base de datos
$conexion->close();
?>