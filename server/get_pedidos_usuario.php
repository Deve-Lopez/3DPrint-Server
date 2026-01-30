<?php

include_once "cors.php";
include_once "conexion.php";

header('Content-Type: application/json');

// Capturamos el ID de usuario desde la URL
$usuario_id = isset($_GET['usuario_id']) ? (int)$_GET['usuario_id'] : 0;

if($usuario_id === 0){
    echo json_encode(["status" => "error", "mensaje" => "No se proporcionó un ID válido"]);
    exit();
}

try{
    // Consulta: Filtramos por el ID del dueño del pedido

    $stmt = $conexion -> prepare("SELECT id, fecha, total, estado, metodo_pago, direccion_envio
                                FROM pedidos
                                WHERE usuario_id = ?
                                ORDER BY fecha DESC");

    // Vinculamos el parámetro 
    $stmt -> bind_param("i", $usuario_id);

    // Ejecutamos
    $stmt -> execute();

    // Obtenemos el resultado
    $resultado = $stmt -> get_result();

    $pedidos = [];
    while($row = $resultado -> fetch_assoc()){
        $pedidos[] = [
            'id' => (int)$row['id'],
            'fecha' => $row['fecha'],
            'total' => (float)$row['total'],
            'estado' => $row['estado'],
            'metodo_pago' => $row['metodo_pago'],
            'direccion' => $row['direccion_envio']
        ];
    }

    echo json_encode(["status" => "success", "data" => $pedidos]);

    $stmt->close();

} catch(Exception $e){
    http_response_code(500);
    echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
}

?>