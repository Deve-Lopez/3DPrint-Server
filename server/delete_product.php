<?php
/**
 * Script de eliminación de productos
 */

include_once "cors.php";
include_once "conexion.php";

header('Content-Type: application/json');

// Soporte multiplataforma para métodos POST y DELETE (evita bloqueos de CORS)
if($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'DELETE'){
    $id = $_GET['id'] ?? null;

    if(!$id){ 
        echo json_encode(["status" => "error", "mensaje" => "ID no proporcionado"]);
        exit();
    }

    try {
        // Preparación de consulta DELETE física sobre la tabla productos
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
        // Error 500 en caso de fallo de servidor o restricción de integridad referencial
        http_response_code(500);
        echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
    }
}
?>