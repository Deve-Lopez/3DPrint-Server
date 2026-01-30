<?php
include_once "cors.php";
include_once "conexion.php";
header('Content-Type: application/json');

$pedido_id = isset($_GET['pedido_id']) ? (int)$_GET['pedido_id'] : 0;

if ($pedido_id === 0) {
    echo json_encode(['status' => 'error', 'mensaje' => 'Pedido no encontrado']);
    exit;
}

try {
    // Usamos Sentencia Preparada para seguridad
    // Seleccionamos cantidad, precio y nombre del producto uniendo las tablas
    $stmt = $conexion->prepare("
        SELECT dp.cantidad, dp.precio_unitario, p.nombre, p.imagen_url 
        FROM detalle_pedidos dp
        JOIN productos p ON dp.producto_id = p.id
        WHERE dp.pedido_id = ?
    ");

    $stmt->bind_param("i", $pedido_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    $productos = [];
    while ($row = $resultado->fetch_assoc()) {
        $productos[] = [
            'nombre' => $row['nombre'],
            'cantidad' => (int)$row['cantidad'],
            'precio' => (float)$row['precio_unitario'],
            'imagen' => $row['imagen_url'],
            'subtotal' => (float)($row['cantidad'] * $row['precio_unitario'])
        ];
    }

    echo json_encode(['status' => 'success', 'data' => $productos]);
    $stmt->close();

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'mensaje' => $e->getMessage()]);
}
?>