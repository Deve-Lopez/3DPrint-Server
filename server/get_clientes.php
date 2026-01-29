<?php

include_once "cors.php";

if($_SERVER['REQUEST_METHOD'] === 'OPTIONS'){
    http_response_code(200);
    exit();
}

header('Content-Type: application/json'); // Aseguramos que el cliente sepa que es JSON

try {
    include_once "conexion.php";

    if(!isset($conexion)){
        throw new Exception("Fallo crítico: No se pudo instanciar la conexión a la BD");        
    }

    // Corregido: 'codigo_postal' y ELIMINADO 'contrasena_hash' por seguridad
    $sql = "SELECT id, email, nombre, apellido, direccion, ciudad, codigo_postal, telefono, rol_id 
            FROM usuarios";

    $resultado = $conexion->query($sql);

    // Corregido: Añadido '$' a resultado
    if(!$resultado){
        throw new Exception("Excepción en la ejecución SQL: " . $conexion->error);
    }

    $clientes = [];
    while($row = $resultado->fetch_assoc()){
        $clientes[] = [
            'id' => (int)$row['id'],
            'email' => $row['email'],
            'nombre' => $row['nombre'],
            'apellido' => $row['apellido'],
            'direccion' => $row['direccion'],
            'ciudad' => $row['ciudad'],
            'codigo_postal' => $row['codigo_postal'],
            'telefono' => $row['telefono'],
            'rol_id' => (int)$row['rol_id']
        ];
    }

    // ÉXITO: Enviamos los datos
    echo json_encode([
        'status' => 'success',
        'data' => $clientes
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'mensaje' => $e->getMessage()
    ]);
} finally {
    // Cierre de recursos garantizado por el bloque finally
    if(isset($conexion) && $conexion){
        $conexion->close();
    }
}
?>