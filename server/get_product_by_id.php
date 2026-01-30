<?php
/**
 * Script: get_product_detail.php
 * Finalidad: Hidratar la vista de detalle de producto en el frontend mediante su ID.
 */

include_once "conexion.php"; 
include_once "cors.php";    

header('Content-Type: application/json');

/**
 * Validación de entrada:
 * Comprobamos la existencia del ID para evitar consultas nulas.
 */
if (!isset($_GET['id'])) {
    http_response_code(400); 
    echo json_encode([
        "status" => "error", 
        "message" => "Solicitud inválida: Falta el parámetro ID"
    ]);
    exit;
}

/**
 * Sanitización:
 * El casting (int) garantiza que el parámetro sea numérico antes de entrar en la consulta.
 */
$id = (int) $_GET['id'];



// Consulta: Recuperación del dataset completo del producto
$sql = "SELECT 
            id, nombre, descripcion, categoria, subcategoria, 
            sku, precio, stock, imagen_url, disponible, color_hex 
        FROM productos 
        WHERE id = $id";

$result = $conexion->query($sql);

/**
 * Control de resultados:
 * Si el ID no existe en la DB, devolvemos un código 404 para que el frontend lo gestione.
 */
if(!$result || $result->num_rows === 0){
    http_response_code(404); 
    echo json_encode([
        "status" => "error", 
        "message" => "El producto solicitado no existe en el catálogo"
    ]);
    exit();
}

/**
 * Respuesta Exitosa:
 * Enviamos el objeto producto único como JSON asociativo.
 */
echo json_encode($result->fetch_assoc());

$conexion->close();
?>