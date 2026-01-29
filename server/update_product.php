<?php
include_once "cors.php";
include_once "conexion.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recogemos datos del FormData
    $id = isset($_POST['id']) ? intval($_POST['id']) : null;
    $nombre = $_POST['nombre'] ?? '';
    $categoria = $_POST['categoria'] ?? '';
    $sku = $_POST['sku'] ?? '';
    $precio = $_POST['precio'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $descripcion = $_POST['descripcion'] ?? '';
    // Convertimos a entero para asegurar que sea 0 o 1
    $disponible = isset($_POST['disponible']) ? intval($_POST['disponible']) : 1;

    if ($id) {
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
        $stmt->bind_param("sssdssii", $nombre, $categoria, $sku, $precio, $stock, $descripcion, $disponible, $id);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "mensaje" => "Producto actualizado con éxito"]);
        } else {
            echo json_encode(["status" => "error", "mensaje" => $conexion->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "mensaje" => "ID no proporcionado"]);
    }
}
$conexion->close();
?>