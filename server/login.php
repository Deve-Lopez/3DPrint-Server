<?php
/**
 * Script: login.php
 * Finalidad: Autenticación de usuarios y control de acceso por estado de cuenta.
 */

include "cors.php";
include "conexion.php";

try {
    $inputJSON = file_get_contents("php://input");
    $data = json_decode($inputJSON, true);

    if (!isset($data["email"]) || !isset($data["contrasena"])) {
        http_response_code(400);
        echo json_encode(["status" => "error", "mensaje" => "Faltan datos"]);
        exit();
    }

    $email = trim($data["email"]);
    $contrasena = trim($data["contrasena"]);

    /**
     * Búsqueda de identidad:
     * Recuperamos el perfil completo y el hash de la contraseña mediante el email.
     */
    $sql = "SELECT id, email, nombre, apellido, rol_id, direccion, ciudad, codigo_postal, telefono, contrasena_hash, activo FROM usuarios WHERE email = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        echo json_encode(["status" => "error", "mensaje" => "Usuario o contraseña incorrectos"]);
        exit();
    }

    $usuario = $resultado->fetch_assoc();

    /**
     * Control de Estado (Baneo/Desactivación):
     * Verificamos el flag 'activo' antes de validar la contraseña para ahorrar recursos.
     */
    if ((int)$usuario["activo"] === 0) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "Su cuenta ha sido desactivada. Póngase en contacto con el administrador."
        ]);
        exit();
    }

    

    /**
     * Validación Criptográfica:
     * 'password_verify' compara el texto plano con el hash guardado (BCRYPT).
     * Es inmune a ataques de temporización.
     */
    if (!password_verify($contrasena, $usuario["contrasena_hash"])) {
        echo json_encode(["status" => "error", "mensaje" => "Usuario o contraseña incorrectos"]);
        exit();
    }

    /**
     * Respuesta de éxito:
     * Retornamos el perfil del usuario (sin el hash) para persistirlo en el State de React.
     */
    echo json_encode([
        "status" => "success",
        "usuario" => [
            "id"            => $usuario["id"],
            "nombre"        => $usuario["nombre"],
            "apellido"      => $usuario["apellido"],
            "email"         => $usuario["email"],
            "rol_id"        => $usuario["rol_id"],
            "direccion"     => $usuario["direccion"],
            "ciudad"        => $usuario["ciudad"],
            "codigo_postal" => $usuario["codigo_postal"],
            "telefono"      => $usuario["telefono"]
        ]
    ]);
    exit();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "mensaje" => "Error interno", "detalle" => $e->getMessage()]);
    exit();
}