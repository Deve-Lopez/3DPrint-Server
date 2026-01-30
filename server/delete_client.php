<?php
/**
 * Script de eliminación de usuarios (Admin)
 */

include_once "cors.php";
include_once "conexion.php";

header('Content-Type:application/json');

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // Captura de ID por parámetro de URL y casting a entero
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;

    if(!$id){
        echo json_encode(["status" => "error", "mensaje" => "ID de usuario no proporcionado"]);
        exit();
    }

    try{
        // Sentencia preparada para evitar inyección SQL
        $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);

        if($stmt->execute()){
            // Verificación de si la fila existía realmente antes del borrado
            if($stmt->affected_rows > 0){
                echo json_encode(["status" => "success", "mensaje" => "Usuario eliminado correctamente"]);
            } else {
                echo json_encode(["status" => "error", "mensaje" => "No se encontró el usuario o ya ha sido eliminado"]);
            }
        } else {
            throw new Exception($conexion->error);
        }
        
        $stmt->close(); 

    } catch(Exception $e){
        // Respuesta en caso de error (ej: restricción de clave foránea)
        http_response_code(500);
        echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
    }

    $conexion->close();

} else {
    echo json_encode(["status" => "error", "mensaje" => "Método no permitido"]);
}
?>