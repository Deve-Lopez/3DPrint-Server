<?php
include_once "cors.php";
include_once "conexion.php";

header('Content-Type: application/json');

// Verificamos que la petición sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "mensaje" => "Método no permitido"]);
    exit;
}

// Obtenemos los datos del FormData (enviados desde React)
// Usamos el operador ternario para evitar errores de "undefined index"
$id            = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$nombre        = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$apellido      = isset($_POST['apellido']) ? trim($_POST['apellido']) : '';
$telefono      = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
$ciudad        = isset($_POST['ciudad']) ? trim($_POST['ciudad']) : '';
$direccion     = isset($_POST['direccion']) ? trim($_POST['direccion']) : '';
$codigo_postal = isset($_POST['codigo_postal']) ? trim($_POST['codigo_postal']) : '';

// Validación básica: El ID es obligatorio
if ($id === 0) {
    echo json_encode(["status" => "error", "mensaje" => "ID de usuario no válido"]);
    exit;
}

try {
    // IMPORTANTE: Solo actualizamos campos de perfil. 
    // No permitimos actualizar 'email', 'password' ni 'rol_id' aquí por seguridad.
    $sql = "UPDATE usuarios SET 
                nombre = ?, 
                apellido = ?, 
                telefono = ?, 
                ciudad = ?, 
                direccion = ?, 
                codigo_postal = ? 
            WHERE id = ?";

    $stmt = $conexion->prepare($sql);
    
    // "ssssssi" indica: 6 strings y 1 entero al final
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