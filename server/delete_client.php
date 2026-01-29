<?php
include_once "cors.php";
include_once "conexion.php";

header('Content-Type:application/json');

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;

    if(!$id){
        echo json_encode(["status" => "error", "mensaje" => "ID de usuario no proporcionado"]);
        exit();
    }

    try{
        $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);

        if($stmt->execute()){
            if($stmt->affected_rows > 0){
                echo json_encode(["status" => "success", "mensaje" => "Usuario eliminado correctamente"]);
            } else {
                echo json_encode(["status" => "error", "mensaje" => "No se encontró el usuario o ya ha sido eliminado"]);
            }
        } else {
            throw new Exception($conexion->error);
        }
        
        $stmt->close(); // Cerramos aquí si todo fue bien

    } catch(Exception $e){
        http_response_code(500);
        // Enviamos el mensaje real para poder debugear
        echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
    }

    // Cerramos la conexión al final del script
    $conexion->close();

} else {
    echo json_encode(["status" => "error", "mensaje" => "Método no permitido"]);
}
?>