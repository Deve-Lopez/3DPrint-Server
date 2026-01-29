<?php
include_once "cors.php";
include_once "conexion.php";

header('Content-Type: application/json');

// Aceptamos POST para evitar problemas de CORS en Mac
if($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'DELETE'){
    $id = $_GET['id'] ?? null;

    if(!$id){ 
        echo json_encode(["status" => "error", "mensaje" => "ID no proporcionado"]);
        exit();
    }

    try {
        // Borrar directamente el registro de la base de datos
        $stmt = $conexion->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->bind_param("i", $id);

        if($stmt->execute()){
            echo json_encode([
                "status" => "success", 
                "mensaje" => "Producto eliminado de la base de datos"
            ]);
        } else {
            throw new Exception($conexion->error);
        }

    } catch(Exception $e){
        http_response_code(500);
        echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
    }
}
?>