<?php
/**
 * Script: registro.php
 * Finalidad: Alta de nuevos usuarios con cifrado de credenciales y asignación de rol por defecto.
 */

include "cors.php";
include "conexion.php";

header('Content-Type: application/json; charset=utf-8');

// Captura del flujo de datos JSON enviado por el cliente (fetch)
$datos = json_decode(file_get_contents("php://input"), true);

/**
 * Validación de integridad:
 * Comprobación de existencia de datos críticos antes de iniciar la transacción.
 */
if(empty($datos['nombre']) || empty($datos['apellido']) || empty($datos['email']) || empty($datos['contrasena_hash'])){
    http_response_code(400); 
    echo json_encode(["status" => "error", "mensaje" => "Validación fallida: Los campos obligatorios están vacíos"]);
    exit();
}

// Validación sintáctica del correo electrónico
if(!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)){
    http_response_code(422);
    echo json_encode(["status" => "error", "mensaje" => "El formato del email no es válido"]);
    exit();
}

/**
 * CIFRADO DE CONTRASEÑA:
 * Uso de BCRYPT para generar un hash irreversible. 
 * Esto cumple con las normativas de protección de datos (RGPD).
 */
$password_segura = password_hash($datos['contrasena_hash'], PASSWORD_BCRYPT);



// Configuración inicial: Rol de cliente (2) y estado activo (1)
$rol_id = 2;
$activo = 1;

/**
 * Persistencia mediante Sentencia Preparada:
 * Previene ataques de Inyección SQL al separar la estructura de la consulta de los datos.
 */
$stmt = $conexion->prepare("
    INSERT INTO usuarios 
    (email, contrasena_hash, nombre, apellido, rol_id, fecha_registro, activo)
    VALUES (?, ?, ?, ?, ?, NOW(), ?)
");

/**
 * Tipado de parámetros: 
 * "ssssii" -> 4 Strings (email, hash, nombre, apellido) y 2 Integers (rol, activo).
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
 * Manejo de respuesta HTTP:
 * 201 Created indica que el recurso se ha generado con éxito en el servidor.
 */
if($stmt->execute()){
    http_response_code(201); 
    echo json_encode(["status" => "success", "mensaje" => "Usuario registrado correctamente"]);
} else {
    http_response_code(500); 
    echo json_encode(["status" => "error", "mensaje" => "Fallo en la persistencia: " . $stmt->error]);
}

$stmt->close();
$conexion->close();
?>