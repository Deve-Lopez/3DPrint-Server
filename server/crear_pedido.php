<?php

// Importar la conexión (usando tu variable $conexion)
include 'conexion.php';
include 'cors.php';

// Capturar los datos enviados desde React
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "mensaje" => "No se recibieron datos válidos"]);
    exit;
}

// Extraer variables del JSON
$usuario_id = $data['usuario_id'];
$total      = $data['total'];
$direccion  = $data['direccion'];
$ciudad     = $data['ciudad'];
$cp         = $data['cp'];
$telefono   = isset($data['telefono']) ? $data['telefono'] : ''; 
$productos  = $data['productos'];

// Iniciar transacción SQL para asegurar integridad de los datos
$conexion->begin_transaction();

try {
    // 1. INSERTAR EN LA TABLA 'pedidos'
    $sql_pedido = "INSERT INTO pedidos 
                   (usuario_id, total, direccion_envio, ciudad_envio, cp_envio, telefono_contacto, metodo_pago, estado, fecha) 
                   VALUES (?, ?, ?, ?, ?, ?, 'tarjeta', 'pendiente', NOW())";
    
    $stmt = $conexion->prepare($sql_pedido);
    if (!$stmt) {
        throw new Exception("Error al preparar pedido: " . $conexion->error);
    }

    $stmt->bind_param("idssss", $usuario_id, $total, $direccion, $ciudad, $cp, $telefono);
    $stmt->execute();
    
    // Recuperar el ID generado para este pedido
    $pedido_id = $conexion->insert_id;

    // 2. INSERTAR EN LA TABLA 'detalle_pedidos'
    $sql_detalle = "INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)";
    $stmt_detalle = $conexion->prepare($sql_detalle);
    
    if (!$stmt_detalle) {
        throw new Exception("Error al preparar detalle: " . $conexion->error);
    }

    // 3. RECORRER PRODUCTOS: Insertar detalle y actualizar stock
    foreach ($productos as $producto) {
        // Insertar cada línea del pedido
        $stmt_detalle->bind_param("iiid", 
            $pedido_id, 
            $producto['id'], 
            $producto['cantidad'], 
            $producto['precio']
        );
        $stmt_detalle->execute();

        // Actualizar stock de cada producto
        $sql_stock = "UPDATE productos SET stock = stock - ? WHERE id = ?";
        $stmt_stock = $conexion->prepare($sql_stock);
        $stmt_stock->bind_param("ii", $producto['cantidad'], $producto['id']);
        $stmt_stock->execute();
    }

    // 4. ACTUALIZAR PERFIL DEL USUARIO
    // Guardamos estos datos en la tabla 'usuarios' para futuros pedidos
    $sql_update_user = "UPDATE usuarios SET 
                        direccion = ?, 
                        ciudad = ?, 
                        codigo_postal = ?, 
                        telefono = ? 
                        WHERE id = ?";
    
    $stmt_user = $conexion->prepare($sql_update_user);
    if ($stmt_user) {
        // "ssssi" -> 4 strings y 1 entero (el id de usuario)
        $stmt_user->bind_param("ssssi", $direccion, $ciudad, $cp, $telefono, $usuario_id);
        $stmt_user->execute();
    }

    // SI TODO HA IDO BIEN, GUARDAMOS CAMBIOS PERMANENTES
    $conexion->commit();

    echo json_encode([
        "status" => "success", 
        "mensaje" => "¡Pedido #$pedido_id creado con éxito!",
        "pedido_id" => $pedido_id
    ]);

} catch (Exception $e) {
    // SI HAY CUALQUIER ERROR, DESHACEMOS TODO
    $conexion->rollback();
    echo json_encode([
        "status" => "error", 
        "mensaje" => "Error procesando el pedido: " . $e->getMessage()
    ]);
}

// Cerrar conexión
$conexion->close();
?>