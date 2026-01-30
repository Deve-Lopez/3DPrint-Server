<?php
/**
 * Script: update_product.php
 * Finalidad: Actualizar la información técnica y comercial de un producto existente.
 */

include_once "cors.php";
include_once "conexion.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /**
     * Captura de Datos:
     * Al procesar un FormData, extraemos las variables y aseguramos el tipado correcto
     * (int para IDs y stock, double para precios).
     */
    $id = isset($_POST['id']) ? intval($_POST['id']) : null;
    $nombre = $_POST['nombre'] ?? '';
    $categoria = $_POST['categoria'] ?? '';
    $sku = $_POST['sku'] ?? '';
    $precio = $_POST['precio'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $descripcion = $_POST['descripcion'] ?? '';
    $disponible = isset($_POST['disponible']) ? intval($_POST['disponible']) : 1;

    if ($id) {
        /**
         * Persistencia con Sentencia Preparada:
         * Actualizamos el registro garantizando que los datos no corrompan la estructura SQL.
         */
        $sql = "UPDATE productos SET 
                nombre = ?, 
                categoria = ?, 
                sku = ?, 
                precio = ?, 
                stock = ?, 
                descripcion = ?, 
                disponible = ? 
                WHERE id = ?";

        $stmt = $conexion->prepare($sql);
        
        /**
         * Definición de tipos:
         * "sssdssii" -> string, string, string, double (precio), string, string, integer, integer.
         */
        $stmt->bind_param("sssdssii", $nombre, $categoria, $sku, $precio, $stock, $descripcion, $disponible, $id);

        

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "mensaje" => "Producto actualizado con éxito"]);
        } else {
            // Manejo de errores a nivel de motor de base de datos
            echo json_encode(["status" => "error", "mensaje" => $conexion->error]);
        }
        $stmt->close();
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "mensaje" => "ID no proporcionado"]);
    }
}
$conexion->close();
?>