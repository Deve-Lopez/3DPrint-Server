<?php
/**
 * Script: get_mensajes.php
 * Finalidad: Recuperar todos los registros de contacto para el panel de administración.
 */

include_once "cors.php";
include_once "conexion.php";

header('Content-Type: application/json');

try {
    /**
     * Consulta de selección:
     * Ordenamos por 'fecha_envio' de forma descendente (DESC) para que 
     * los mensajes más recientes aparezcan al principio de la lista.
     */
    $sql = "SELECT * FROM mensajes_contacto ORDER BY fecha_envio DESC";
    $resultado = $conexion->query($sql);

    // Estructura de almacenamiento para la respuesta JSON
    $mensajes = [];
    while ($fila = $resultado->fetch_assoc()) {
        $mensajes[] = $fila;
    }

    // Serialización del array de mensajes para su consumo en el Frontend (React)
    echo json_encode($mensajes);

} catch (Exception $e) {
    // Manejo de errores en caso de fallo en la consulta o conexión
    echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
}

// Cierre de la conexión para optimizar recursos del servidor
$conexion->close();
?>