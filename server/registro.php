<?php
include "cors.php";
include "conexion.php";

// Definicón de cabeceras para la transmisión de datos en formato JSON
header('Content-Type: application/json; charset=utf-8');

/**
 * Request Body Parsing:
 * Extrae el payload del stream 'php://input'
 * Se decodifica a un array asociativo para su manipulación
 */

$datos = json_decode(file_get_contents("php://input"), true);

/**
 * Server-side Validation:
 * Verificación de campos obligatorios para asegurar la integridad de la base de datos
 */

if(empty($datos['nombre']) || empty($datos['apellido']) || empty($datos['email']) || empty($datos['contrasena_hash'])){
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "mensaje" => "Validación fallida: Los campos obligatorios están vacios"]);
    exit();
}

/**
 * Email Format Verification:
 * Filtros nativos de PHP para validar la estructura sintáctica del correo
 */
if(!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)){
    http_response_code(422);
    echo json_encode(["status" => "error", "mensaje" => "El formato del email no es válido"]);
    exit()  ;
}

/**
 * SEGURIDAD: Hash de contraseña
 * No guardes contraseñas en texto plano.
 */
$password_segura = password_hash($datos['contrasena_hash'], PASSWORD_BCRYPT);

$rol_id = 2;
$activo = 1;

/**
 * Data Persistence (Prepared Statements):
 * Implementa el protocolo de seguridad para mitigar ataques de Inyección SQL.
 * Se mapean las 4 variables a las columnas: nombre, apellido, email, contrasena_hash 
 */
$stmt = $conexion->prepare("
    INSERT INTO usuarios 
    (email, contrasena_hash, nombre, apellido, rol_id, fecha_registro, activo)
    VALUES (?, ?, ?, ?, ?, NOW(), ?)
");
/**
 * Parameter Binding:
 * Vincular las variables PHP a los placeholders. 'ssss' indica cuatro parámetros de tipo String.
 */

$stmt->bind_param(
    "ssssii",
    $datos['email'],
    $password_segura,
    $datos['nombre'],
    $datos['apellido'],
    $rol_id,
    $activo
);


/**
 * Execution & Transaction Feedback:
 * Ejecuta la sentencia y retorna el estado de la operación al cliente
 */
if($stmt->execute()){
    http_response_code(201); // Created
    echo json_encode(["status" => "success", "mensaje" => "Usuario registrado correctamente"]);
}else{
    http_response_code(500); // Internal Server Error
    echo json_encode(["status" => "error", "mensaje" => "Fallo en la persistencia: " . $stmt->error]);
}

/**
 * Resource Cleanup:
 * Cierre explicito del statement y la conexión para optiminzar la memoria del servidor.
 */
$stmt->close();
$conexion->close();

?>