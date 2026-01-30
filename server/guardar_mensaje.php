<?php
/**
 * Script: guardar_mensaje.php
 * Finalidad: Procesar el formulario de contacto con validaciones de seguridad y formato.
 */

include "cors.php";     
include "conexion.php"; 

header('Content-Type: application/json; charset=utf-8');

/**
 * Parsing de datos:
 * Recuperamos el JSON del cuerpo de la petición (stream) y lo convertimos en array.
 */
$datos = json_decode(file_get_contents("php://input"), true);

/**
 * Validación de campos requeridos:
 * Asegura que la aplicación no procese registros incompletos.
 */
if(empty($datos['nombre']) || empty($datos['email']) || empty($datos['asunto']) || empty($datos['mensaje'])){
    http_response_code(400); 
    echo json_encode(["status" => "error", "mensaje" => "Validación fallida: Campos obligatorios vacíos"]);
    exit();
}

/**
 * Validación de formato:
 * Uso de 'filter_var' para garantizar que el email cumple con los estándares RFC.
 */
if(!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)){
    http_response_code(422); 
    echo json_encode(["status" => "error", "mensaje" => "El formato del email no es válido"]);
    exit();
}



/**
 * Persistencia Segura:
 * Sentencia preparada para registrar el mensaje. Se incluye 'NOW()' para la marca temporal.
 */
$stmt = $conexion->prepare("INSERT INTO mensajes_contacto (nombre, email, asunto, mensaje, telefono, fecha_envio) VALUES (?, ?, ?, ?, ?, NOW())");

/**
 * Vinculación de parámetros:
 * Se define el tipo 's' (string) para los 5 campos capturados.
 */
$telefono = $datos['telefono'] ?? '';

$stmt->bind_param("sssss", 
    $datos['nombre'], 
    $datos['email'], 
    $datos['asunto'], 
    $datos['mensaje'], 
    $telefono
);

/**
 * Respuesta de estado HTTP:
 * 201 (Created) para éxito y 500 (Server Error) para fallos de base de datos.
 */
if ($stmt->execute()) {
    http_response_code(201); 
    echo json_encode(["status" => "success", "mensaje" => "Mensaje registrado correctamente"]);
} else {
    http_response_code(500); 
    echo json_encode(["status" => "error", "mensaje" => "Fallo en la persistencia: " . $stmt->error]);
}

$stmt->close();
$conexion->close();

?>