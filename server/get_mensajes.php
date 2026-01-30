<?php
include_once "cors.php";
include_once "conexion.php";

header('Content-Type: application/json');

try {
    // Consultamos todos los mensajes, los más nuevos primero
    $sql = "SELECT * FROM mensajes_contacto ORDER BY fecha_envio DESC";
    $resultado = $conexion->query($sql);

    $mensajes = [];
    while ($fila = $resultado->fetch_assoc()) {
        $mensajes[] = $fila;
    }

    echo json_encode($mensajes);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
}

$conexion->close();
?>