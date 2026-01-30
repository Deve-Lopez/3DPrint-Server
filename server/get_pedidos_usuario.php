<?php
/**
 * Script: get_pedidos_usuario.php
 * Finalidad: Recuperar el historial de compras de un cliente específico.
 */

include_once "cors.php";
include_once "conexion.php";

header('Content-Type: application/json');

// Captura del ID de usuario desde la URL (Query String)
$usuario_id = isset($_GET['usuario_id']) ? (int)$_GET['usuario_id'] : 0;

if($usuario_id === 0){
    echo json_encode(["status" => "error", "mensaje" => "No se proporcionó un ID válido"]);
    exit();
}

try{
    /**
     * Seguridad: Sentencia preparada para filtrar pedidos por dueño (usuario_id).
     * Ordenamos por fecha descendente para mostrar primero las compras más recientes.
     */
    $stmt = $conexion -> prepare("SELECT id, fecha, total, estado, metodo_pago, direccion_envio
                                FROM pedidos
                                WHERE usuario_id = ?
                                ORDER BY fecha DESC");

    $stmt -> bind_param("i", $usuario_id);
    $stmt -> execute();

    $resultado = $stmt -> get_result();

    /**
     * Transformación de tipos de datos:
     * Aseguramos que el ID sea entero y el Total sea flotante para evitar errores en React.
     */
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
    // Manejo de errores de servidor con código de estado HTTP 500
    http_response_code(500);
    echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
}

?>