<?php
/**
 * Product Detail Endpoint
 * Recupera la información extendida de una entidad específica mediante su identificador único.
 * Proporciona el dataset completo necesario para la vista 'DetailsProduct' del frontend.
 */

include_once "conexion.php"; // Instance Provider
include_once "cors.php";    // CORS & Header Controller

/**
 * Request Validation:
 * Verifica la existencia del parámetro obligatorio 'id' en el query string.
 */
if (!isset($_GET['id'])) {
    http_response_code(400); // Bad Request
    echo json_encode([
        "status" => "error", 
        "message" => "Solicitud inválida: Falta el parámetro ID"
    ]);
    exit;
}

/**
 * Security & Sanitization:
 * Aplicamos Type Casting (int) para asegurar que el parámetro sea un entero,
 * mitigando riesgos básicos de Inyección SQL en el segmento dinámico.
 */
$id = (int) $_GET['id'];

// Query Execution: Selección de campos específicos para la hidratación del componente
$sql = "SELECT 
            id, nombre, descripcion, categoria, subcategoria, 
            sku, precio, stock, imagen_url, disponible, color_hex 
        FROM productos 
        WHERE id = $id";

$result = $conexion->query($sql);

/**
 * Database Response Analysis:
 * Gestiona el flujo según la existencia del registro en el motor SQL.
 */
if(!$result || $result->num_rows === 0){
    http_response_code(404); // Not Found
    echo json_encode([
        "status" => "error", 
        "message" => "El producto solicitado no existe en el catálogo"
    ]);
    exit();
}

/**
 * Success Response:
 * Serialización del registro (Assoc Array) a formato JSON para el consumo del cliente.
 */
echo json_encode($result->fetch_assoc());

// Resource Cleanup: Cierre de la conexión al finalizar el ciclo de vida del script
$conexion->close();
?>