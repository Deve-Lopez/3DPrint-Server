<?php
/**
 * Script: get_clientes.php
 * Finalidad: Listado dinámico de usuarios con soporte para búsqueda en tiempo real.
 */

include_once "cors.php";
include_once "conexion.php";
header('Content-Type: application/json');

try {
    // 1. Captura del parámetro de búsqueda 'q' para filtrado dinámico
    $q = isset($_GET['q']) ? $_GET['q'] : '';

    // 2. Proyección de columnas necesarias para la gestión de usuarios
    $sql = "SELECT id, email, nombre, apellido, direccion, ciudad, codigo_postal, telefono, rol_id, activo FROM usuarios";

    // 3. Lógica de búsqueda: Construcción de filtros condicionales
    if ($q !== '') {
        // Sanitización del input mediante real_escape_string para prevenir SQL Injection
        $search = $conexion->real_escape_string($q);
        $sql .= " WHERE nombre LIKE '%$search%' 
                  OR apellido LIKE '%$search%' 
                  OR email LIKE '%$search%' 
                  OR ciudad LIKE '%$search%' 
                  OR telefono LIKE '%$search%'";
    }

    // Ordenación cronológica inversa (más recientes primero)
    $sql .= " ORDER BY id DESC";
    $resultado = $conexion->query($sql);

    if(!$resultado) throw new Exception($conexion->error);

    /**
     * Procesamiento del set de resultados:
     * Mapeamos las filas de la DB a un array asociativo con tipado corregido (int).
     */
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

    // Respuesta exitosa serializada en JSON
    echo json_encode(['status' => 'success', 'data' => $clientes]);

} catch (Exception $e) {
    // Gestión de excepciones y errores de servidor
    http_response_code(500);
    echo json_encode(['status' => 'error', 'mensaje' => $e->getMessage()]);
}
?>