<?php
/**
 * Script: update_perfil.php
 * Finalidad: Permite al usuario actualizar sus datos personales desde su panel de control.
 */

include_once "cors.php";
include_once "conexion.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "mensaje" => "Método no permitido"]);
    exit;
}

/**
 * Captura y limpieza:
 * Se utiliza trim() para eliminar espacios accidentales en los extremos de los strings.
 */
$id            = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$nombre        = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$apellido      = isset($_POST['apellido']) ? trim($_POST['apellido']) : '';
$telefono      = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
$ciudad        = isset($_POST['ciudad']) ? trim($_POST['ciudad']) : '';
$direccion     = isset($_POST['direccion']) ? trim($_POST['direccion']) : '';
$codigo_postal = isset($_POST['codigo_postal']) ? trim($_POST['codigo_postal']) : '';

if ($id === 0) {
    echo json_encode(["status" => "error", "mensaje" => "ID de usuario no válido"]);
    exit;
}

try {
    /**
     * SEGURIDAD POR LIMITACIÓN:
     * El UPDATE omite campos como 'email', 'rol_id' o 'password'. 
     * Esto evita que un usuario cambie su correo (clave de login) o se eleve privilegios por error.
     */
    $sql = "UPDATE usuarios SET 
                nombre = ?, 
                apellido = ?, 
                telefono = ?, 
                ciudad = ?, 
                direccion = ?, 
                codigo_postal = ? 
            WHERE id = ?";

    $stmt = $conexion->prepare($sql);
    
    // Vinculación: 6 parámetros tipo string y 1 entero (ID)
    $stmt->bind_param("ssssssi", 
        $nombre, 
        $apellido, 
        $telefono, 
        $ciudad, 
        $direccion, 
        $codigo_postal, 
        $id
    );

    

    if ($stmt->execute()) {
        /**
         * Validación de ejecución:
         * Se considera éxito incluso si no hay filas afectadas (affected_rows === 0) 
         * si no hay error de base de datos (el usuario guardó sin cambiar nada).
         */
        if ($stmt->affected_rows > 0 || $stmt->errno === 0) {
            echo json_encode([
                "status" => "success", 
                "mensaje" => "Perfil actualizado correctamente"
            ]);
        } else {
            echo json_encode([
                "status" => "error", 
                "mensaje" => "No se realizaron cambios o el usuario no existe"
            ]);
        }
    } else {
        throw new Exception($stmt->error);
    }

    $stmt->close();

} catch (Exception $e) {
    echo json_encode([
        "status" => "error", 
        "mensaje" => "Error en la base de datos: " . $e->getMessage()
    ]);
}

$conexion->close();
?>