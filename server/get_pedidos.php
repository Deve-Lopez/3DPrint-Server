<?php
include_once "cors.php";

/**
 * Gestión del Preflight (OPTIONS):
 * Necesario para peticiones complejas en arquitecturas desacopladas.
 */
if($_SERVER['REQUEST_METHOD'] === 'OPTIONS'){
    http_response_code(200);
    exit();
}

header('Content-Type: application/json');

try {
    include_once "conexion.php";

    if(!isset($conexion)){
        throw new Exception("Fallo crítico: No se pudo instanciar la conexión a la BD");
    }

    /**
     * CONSULTA MULTI-TABLA (INNER JOIN):
     * Consolidamos datos de 'pedidos', 'detalle_pedidos', 'productos' y 'usuarios'.
     * Usamos alias (AS) para evitar colisiones de nombres de columnas.
     */
    $sql = "SELECT 
                p.id AS pedido_id,
                p.fecha,
                p.total AS total_pedido,
                p.estado,
                p.direccion_envio,
                CONCAT(u.nombre, ' ', u.apellido) AS cliente_full,
                u.ciudad,
                u.codigo_postal,
                u.telefono,
                dp.cantidad,
                dp.precio_unitario,
                pr.nombre AS producto_nombre,
                pr.sku AS producto_sku,
                pr.imagen_url
            FROM pedidos p
            INNER JOIN detalle_pedidos dp ON p.id = dp.pedido_id
            INNER JOIN productos pr ON dp.producto_id = pr.id
            INNER JOIN usuarios u ON p.usuario_id = u.id
            ORDER BY p.fecha DESC";

    $resultado = $conexion->query($sql);

    if(!$resultado){
        throw new Exception("Error en la consulta: " . $conexion->error);
    }

    /**
     * AGRUPACIÓN DE DATOS:
     * El resultado SQL devuelve una fila por cada producto.
     * Agrupamos por 'pedido_id' para crear una estructura anidada objeto -> productos.
     */
    $pedidos = [];

    while($row = $resultado->fetch_assoc()){
        $idPedido = (int)$row['pedido_id'];

        if (!isset($pedidos[$idPedido])) {
            $pedidos[$idPedido] = [
                'id'             => $idPedido,
                'fecha'          => $row['fecha'],
                'total_pedido'   => (float)$row['total_pedido'],
                'estado'         => $row['estado'],
                'direccion'      => $row['direccion_envio'],
                'destinatario'   => $row['cliente_full'],
                'ciudad'         => $row['ciudad'],
                'cp'             => $row['codigo_postal'],
                'telefono'       => $row['telefono'],
                'productos'      => [] 
            ];
        }

        // Inyectamos el producto en la colección del pedido correspondiente
        $pedidos[$idPedido]['productos'][] = [
            'nombre'          => $row['producto_nombre'],
            'sku'             => $row['producto_sku'],
            'cantidad'        => (int)$row['cantidad'],
            'precio_unitario' => (float)$row['precio_unitario']
        ];
    }

    /**
     * Respuesta Final:
     * Usamos 'array_values' para convertir el array asociativo en un array indexado 
     * estándar compatible con el método .map() de JavaScript.
     */
    echo json_encode([
        'status' => 'success',
        'data' => array_values($pedidos)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'mensaje' => $e->getMessage()]);
} finally {
    if(isset($conexion) && $conexion) $conexion->close();
}