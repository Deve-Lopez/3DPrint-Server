<?php

include "cors.php";
include "conexion.php";

try {
    $inputJSON = file_get_contents("php://input");
    $data = json_decode($inputJSON, true);

    if (!isset($data["email"]) || !isset($data["contrasena"])) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "mensaje" => "Faltan datos"
        ]);
        exit();
    }

    $email = trim($data["email"]);
    $contrasena = trim($data["contrasena"]);

    /**
     * 1. Añadimos 'activo' a la consulta
     */
    $sql = "SELECT id, email, nombre, apellido, rol_id, direccion, ciudad, codigo_postal, telefono, contrasena_hash, activo FROM usuarios WHERE email = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "Usuario o contraseña incorrectos"
        ]);
        exit();
    }

    $usuario = $resultado->fetch_assoc();

    /**
     * 2. COMPROBACIÓN DE CUENTA ACTIVA
     * Si el usuario existe pero activo es 0, bloqueamos el acceso inmediatamente
     */
    if ((int)$usuario["activo"] === 0) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "Su cuenta ha sido desactivada. Póngase en contacto con el administrador."
        ]);
        exit();
    }

    /**
     * 3. Verificar contraseña hasheada
     */
    if (!password_verify($contrasena, $usuario["contrasena_hash"])) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "Usuario o contraseña incorrectos"
        ]);
        exit();
    }

    /**
     * Login correcto
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
    echo json_encode([
        "status" => "error",
        "mensaje" => "Error interno del servidor",
        "detalle" => $e->getMessage()
    ]);
    exit();
}