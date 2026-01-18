<?php

include "cors.php";
include "conexion.php";

try {
    /**
     * Leer el JSON enviado desde React
     */
    $inputJSON = file_get_contents("php://input");
    $data = json_decode($inputJSON, true);

    // Validar que existan los datos
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
     * Consultar usuario en la BD
     * El email debe ser único en la tabla usuarios
     */
    $sql = "SELECT id, email, nombre, apellido, rol_id, direccion, ciudad, codigo_postal, telefono, contrasena_hash FROM usuarios WHERE email = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    // Si no existe el usuario
    if ($resultado->num_rows === 0) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "Usuario o contraseña incorrectos"
        ]);
        exit();
    }

    $usuario = $resultado->fetch_assoc();

    /**
     * Verificar contraseña hasheada
     */
    if (!password_verify($contrasena, $usuario["contrasena_hash"])) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "Usuario o contraseña incorrectos"
        ]);
        exit();
    }

    /**
     * Login correcto -> Devolvemos datos del usuario
     */
    echo json_encode([
        "status" => "success",
        "usuario" => [
            "id"            => $usuario["id"],
            "nombre"        => $usuario["nombre"],
            "apellido"      => $usuario["apellido"], // Añadido
            "email"         => $usuario["email"],
            "rol_id"        => $usuario["rol_id"],   // Añadido
            "direccion"     => $usuario["direccion"], // ¡IMPORTANTE!
            "ciudad"        => $usuario["ciudad"],    // ¡IMPORTANTE!
            "codigo_postal" => $usuario["codigo_postal"], // ¡IMPORTANTE!
            "telefono"      => $usuario["telefono"]   // ¡IMPORTANTE!
        ]
    ]);
    exit();
} catch (Exception $e) {
    /**
     * Manejo de Errores del Servidor
     */
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "mensaje" => "Error interno del servidor",
        "detalle" => $e->getMessage()
    ]);
    exit();
}
