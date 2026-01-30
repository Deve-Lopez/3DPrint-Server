<?php
/**
 * Script: update_order_status.php
 * Finalidad: Actualizar el estado logístico de una orden de compra.
 */

include_once "cors.php";
header('Content-Type: application/json');

include_once "conexion.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /**
     * Captura de datos:
     * Al recibir un FormData desde React, accedemos mediante la superglobal $_POST.
     */
    $id = isset($_POST['id']) ? intval($_POST['id']) : null;
    $estado = isset($_POST['estado']) ? $_POST['estado'] : null;

    if ($id && $estado) {
        /**
         * Persistencia:
         * Sentencia preparada para actualizar el campo 'estado' de la tabla pedidos.
         */
        $sql = "UPDATE pedidos SET estado = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        
        if ($stmt) {
            // "si" indica String para el estado e Integer para el ID
            $stmt->bind_param("si", $estado, $id);
            
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "mensaje" => "Estado actualizado"]);
            } else {
                http_response_code(500);
                echo json_encode(["status" => "error", "mensaje" => $stmt->error]);
            }
            $stmt->close();
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "mensaje" => "Error al preparar consulta"]);
        }
    } else {
        // Validación: Asegura que ambos parámetros obligatorios estén presentes
        http_response_code(400);
        echo json_encode(["status" => "error", "mensaje" => "ID o Estado no recibidos"]);
    }
}

// Cierre de la conexión al finalizar el ciclo de vida del script
if(isset($conexion)) $conexion->close();
?>