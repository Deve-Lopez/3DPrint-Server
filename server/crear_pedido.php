<?php
/**
 * Gestiona la creación de pedidos, el volcado de detalles, la actualización
 * de stock y la persistencia de datos de envío del usuario.
 */

// Importar la configuración de conexión y cabeceras CORS
include 'conexion.php';
include 'cors.php';

/**
 * RECEPCIÓN DE DATOS:
 * Al enviar datos mediante 'fetch' con JSON desde React, PHP no los recibe en $_POST.
 * Debemos leer el flujo de entrada 'php://input' y decodificarlo.
 */
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "mensaje" => "No se recibieron datos válidos"]);
    exit;
}

// Mapeo de variables desde el payload JSON
$usuario_id = $data['usuario_id'];
$total      = $data['total'];
$direccion  = $data['direccion'];
$ciudad     = $data['ciudad'];
$cp         = $data['cp'];
$telefono   = isset($data['telefono']) ? $data['telefono'] : ''; 
$productos  = $data['productos'];



/**
 * INTEGRIDAD DE DATOS: TRANSACCIONES
 * Iniciamos una transacción. Esto asegura que si falla la inserción de un producto,
 * no se cree el pedido ni se reste el dinero/stock erróneamente.
 */
$conexion->begin_transaction();

try {
    /**
     * 1. INSERCIÓN EN LA TABLA 'pedidos'
     * Registramos la cabecera del pedido con los datos generales.
     */
    $sql_pedido = "INSERT INTO pedidos 
                   (usuario_id, total, direccion_envio, ciudad_envio, cp_envio, telefono_contacto, metodo_pago, estado, fecha) 
                   VALUES (?, ?, ?, ?, ?, ?, 'tarjeta', 'pendiente', NOW())";
    
    $stmt = $conexion->prepare($sql_pedido);
    if (!$stmt) {
        throw new Exception("Error al preparar pedido: " . $conexion->error);
    }

    $stmt->bind_param("idssss", $usuario_id, $total, $direccion, $ciudad, $cp, $telefono);
    $stmt->execute();
    
    // Obtenemos el ID autoincremental que MySQL acaba de asignar a este pedido
    $pedido_id = $conexion->insert_id;

    /**
     * 2. PREPARACIÓN DE DETALLE_PEDIDOS
     * Preparamos la consulta una sola vez fuera del bucle para optimizar el rendimiento.
     */
    $sql_detalle = "INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)";
    $stmt_detalle = $conexion->prepare($sql_detalle);
    
    if (!$stmt_detalle) {
        throw new Exception("Error al preparar detalle: " . $conexion->error);
    }

    /**
     * 3. ITERACIÓN DE PRODUCTOS
     * Por cada item en el carrito, insertamos su línea de detalle y descontamos el stock.
     */
    foreach ($productos as $producto) {
        // Insertar registro en detalle_pedidos (N registros por cada 1 pedido)
        $stmt_detalle->bind_param("iiid", 
            $pedido_id, 
            $producto['id'], 
            $producto['cantidad'], 
            $producto['precio']
        );
        $stmt_detalle->execute();

        // Lógica de inventario: Restamos la cantidad comprada del stock disponible
        $sql_stock = "UPDATE productos SET stock = stock - ? WHERE id = ?";
        $stmt_stock = $conexion->prepare($sql_stock);
        $stmt_stock->bind_param("ii", $producto['cantidad'], $producto['id']);
        $stmt_stock->execute();
    }

    /**
     * 4. PERSISTENCIA DEL PERFIL DE USUARIO
     * Actualizamos la ficha del usuario para que en su próxima compra 
     * sus datos de envío aparezcan por defecto.
     */
    $sql_update_user = "UPDATE usuarios SET 
                        direccion = ?, 
                        ciudad = ?, 
                        codigo_postal = ?, 
                        telefono = ? 
                        WHERE id = ?";
    
    $stmt_user = $conexion->prepare($sql_update_user);
    if ($stmt_user) {
        $stmt_user->bind_param("ssssi", $direccion, $ciudad, $cp, $telefono, $usuario_id);
        $stmt_user->execute();
    }

    /**
     * FINALIZACIÓN EXITOSA:
     * Si el código llega aquí sin errores, confirmamos todas las operaciones en la DB.
     */
    $conexion->commit();

    echo json_encode([
        "status" => "success", 
        "mensaje" => "¡Pedido #$pedido_id creado con éxito!",
        "pedido_id" => $pedido_id
    ]);

} catch (Exception $e) {
    /**
     * GESTIÓN DE ERRORES:
     * Si algo falla en el bloque 'try', deshacemos cualquier cambio realizado 
     * en este proceso para evitar datos huérfanos o stocks inconsistentes.
     */
    $conexion->rollback();
    echo json_encode([
        "status" => "error", 
        "mensaje" => "Error procesando el pedido: " . $e->getMessage()
    ]);
}

// Cierre de la instancia de conexión para liberar recursos del servidor
$conexion->close();
?>