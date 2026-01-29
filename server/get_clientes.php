<?php
include_once "cors.php";
include_once "conexion.php";
header('Content-Type: application/json');

try {
    // 1. Capturamos el parámetro de búsqueda 'q'
    $q = isset($_GET['q']) ? $_GET['q'] : '';

    // 2. Base de la consulta
    $sql = "SELECT id, email, nombre, apellido, direccion, ciudad, codigo_postal, telefono, rol_id, activo FROM usuarios";

    // 3. Si hay búsqueda, añadimos el filtro WHERE
    if ($q !== '') {
        // Escapamos el valor para evitar inyecciones SQL (usando la conexión de conexion.php)
        $search = $conexion->real_escape_string($q);
        $sql .= " WHERE nombre LIKE '%$search%' 
                  OR apellido LIKE '%$search%' 
                  OR email LIKE '%$search%' 
                  OR ciudad LIKE '%$search%' 
                  OR telefono LIKE '%$search%'";
    }

    $sql .= " ORDER BY id DESC";
    $resultado = $conexion->query($sql);

    if(!$resultado) throw new Exception($conexion->error);

    $clientes = [];
    while($row = $resultado->fetch_assoc()){
        $clientes[] = [
            'id'            => (int)$row['id'],
            'email'         => $row['email'],
            'nombre'        => $row['nombre'],
            'apellido'      => $row['apellido'],
            'direccion'     => $row['direccion'],
            'ciudad'        => $row['ciudad'],
            'codigo_postal' => $row['codigo_postal'],
            'telefono'      => $row['telefono'],
            'rol_id'        => (int)$row['rol_id'],
            'activo'        => (int)$row['activo']
        ];
    }

    echo json_encode(['status' => 'success', 'data' => $clientes]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'mensaje' => $e->getMessage()]);
}
?>