<?php
include_once "cors.php";
header('Content-Type: application/json');

include_once "conexion.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Usamos $_POST directamente ya que React enviará FormData
    $id = isset($_POST['id']) ? intval($_POST['id']) : null;
    $estado = isset($_POST['estado']) ? $_POST['estado'] : null;

    if ($id && $estado) {
        // Importante: Usamos $conexion que es la variable de tu conexion.php
        $sql = "UPDATE pedidos SET estado = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        
        if ($stmt) {
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
        http_response_code(400);
        echo json_encode(["status" => "error", "mensaje" => "ID o Estado no recibidos"]);
    }
}
if(isset($conexion)) $conexion->close();
?>