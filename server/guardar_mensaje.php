<?php
/**
 * Contact Form Processor
 * Gestiona la recepción de leads y mensajes desde el frontend.
 * Implementa validaciones de integridad y persistencia segura mediante sentencias preparadas.
 */

include "cors.php";     // CORS Controller: Habilita el handshake con el cliente React
include "conexion.php"; // Instance Provider: Proporciona el objeto $conexion (MySQLi)

// Definición de cabeceras para la transmisión de datos en formato JSON
header('Content-Type: application/json; charset=utf-8');

/**
 * Request Body Parsing:
 * Extrae el payload del stream 'php://input' (necesario para lecturas de fetch/JSON).
 * Se decodifica a un array asociativo para su manipulación.
 */
$datos = json_decode(file_get_contents("php://input"), true);

/**
 * Server-side Validation:
 * Verificación de campos obligatorios para asegurar la integridad de la base de datos.
 */
if(empty($datos['nombre']) || empty($datos['email']) || empty($datos['asunto']) || empty($datos['mensaje'])){
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "mensaje" => "Validación fallida: Los campos obligatorios están vacíos"]);
    exit();
}

/**
 * Email Format Verification:
 * Filtros nativos de PHP para validar la estructura sintáctica del correo.
 */
if(!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)){
    http_response_code(422); 
    echo json_encode(["status" => "error", "mensaje" => "El formato del email no es válido"]);
    exit();
}

/**
 * Data Persistence (Prepared Statements):
 * Implementa el protocolo de seguridad para mitigar ataques de Inyección SQL.
 * Se mapean las 5 variables a las columnas: nombre, email, asunto, mensaje, telefono.
 */
$stmt = $conexion->prepare("INSERT INTO mensajes_contacto (nombre, email, asunto, mensaje, telefono, fecha_envio) VALUES (?, ?, ?, ?, ?, NOW())");

/**
 * Parameter Binding:
 * Vincular las variables PHP a los placeholders. 'sssss' indica cinco parámetros de tipo String.
 * Manejo de 'telefono' como opcional mediante el operador null coalescing.
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
 * Execution & Transaction Feedback:
 * Ejecuta la sentencia y retorna el estado de la operación al cliente.
 */
if ($stmt->execute()) {
    http_response_code(201); // Created
    echo json_encode(["status" => "success", "mensaje" => "Mensaje registrado correctamente"]);
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(["status" => "error", "mensaje" => "Fallo en la persistencia: " . $stmt->error]);
}

/**
 * Resource Cleanup:
 * Cierre explícito del statement y la conexión para optimizar la memoria del servidor.
 */
$stmt->close();
$conexion->close();

?>