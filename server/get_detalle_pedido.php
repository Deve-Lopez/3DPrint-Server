<?php
/**
 * Script: get_detalle_pedido.php
 * Finalidad: Recuperar los artículos asociados a un pedido mediante una consulta relacional.
 */

include_once "cors.php";
include_once "conexion.php";
header('Content-Type: application/json');

// Captura del ID del pedido y validación de tipo para asegurar que sea un entero válido
$pedido_id = isset($_GET['pedido_id']) ? (int)$_GET['pedido_id'] : 0;

if ($pedido_id === 0) {
    echo json_encode(['status' => 'error', 'mensaje' => 'Pedido no encontrado']);
    exit;
}

try {
    /**
     * CONSULTA RELACIONAL (INNER JOIN):
     * Unimos 'detalle_pedidos' con 'productos' para obtener el nombre e imagen 
     * en una sola petición al servidor, optimizando el rendimiento.
     */
    $stmt = $conexion->prepare("
        SELECT dp.cantidad, dp.precio_unitario, p.nombre, p.imagen_url 
        FROM detalle_pedidos dp
        JOIN productos p ON dp.producto_id = p.id
        WHERE dp.pedido_id = ?
    ");

    $stmt->bind_param("i", $pedido_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    /**
     * Procesamiento de resultados:
     * Calculamos el 'subtotal' por línea de producto para facilitar el renderizado en React.
     */
    $productos = [];
    while ($row = $resultado->fetch_assoc()) {
        $productos[] = [
            'nombre'   => $row['nombre'],
            'cantidad' => (int)$row['cantidad'],
            'precio'   => (float)$row['precio_unitario'],
            'imagen'   => $row['imagen_url'],
            'subtotal' => (float)($row['cantidad'] * $row['precio_unitario'])
        ];
    }

    echo json_encode(['status' => 'success', 'data' => $productos]);
    $stmt->close();

} catch (Exception $e) {
    // Captura de excepciones del motor MySQL
    echo json_encode(['status' => 'error', 'mensaje' => $e->getMessage()]);
}
?>