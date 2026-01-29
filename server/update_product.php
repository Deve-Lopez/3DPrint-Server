<?php
include_once "cors.php";
include_once "conexion.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Recogemos el ID y los demás campos
        $id           = $_POST['id'] ?? null;
        $nombre       = $_POST['nombre'] ?? '';
        $categoria    = $_POST['categoria'] ?? '';
        $sku          = $_POST['sku'] ?? '';
        $descripcion  = $_POST['descripcion'] ?? '';
        $precio       = (float)($_POST['precio'] ?? 0);
        $stock        = (int)($_POST['stock'] ?? 0);
        $color_hex    = $_POST['color_hex'] ?? '#333333';

        if (!$id) throw new Exception("ID de producto no proporcionado.");

        // 1. Verificamos si se subió una nueva imagen
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
            $directorio_destino = "/Applications/XAMPP/xamppfiles/htdocs/3dprint/images/";
            $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $nombre_imagen = time() . "_" . preg_replace('/[^A-Za-z0-9]/', '', $sku) . "." . $extension;
            
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $directorio_destino . $nombre_imagen)) {
                // Actualizar con imagen nueva
                $sql = "UPDATE productos SET nombre=?, categoria=?, sku=?, descripcion=?, precio=?, stock=?, color_hex=?, imagen_url=? WHERE id=?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("ssssdissi", $nombre, $categoria, $sku, $descripcion, $precio, $stock, $color_hex, $nombre_imagen, $id);
            }
        } else {
            // Actualizar SIN cambiar la imagen
            $sql = "UPDATE productos SET nombre=?, categoria=?, sku=?, descripcion=?, precio=?, stock=?, color_hex=? WHERE id=?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("ssssdisi", $nombre, $categoria, $sku, $descripcion, $precio, $stock, $color_hex, $id);
        }

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "mensaje" => "Producto actualizado correctamente"]);
        } else {
            throw new Exception($conexion->error);
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
    }
}
?>