<?php
header('Content-Type: application/json');

include_once "cors.php";
include_once "conexion.php";

error_reporting(0);
ini_set('display_errors', 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($conexion->connect_error) {
            throw new Exception("Error de conexión: " . $conexion->connect_error);
        }

        // Recoger datos
        $nombre       = $_POST['nombre'] ?? '';
        $categoria    = $_POST['categoria'] ?? '';
        $subcategoria = $_POST['subcategoria'] ?? null;
        $sku          = $_POST['sku'] ?? '';
        $descripcion  = $_POST['descripcion'] ?? null;
        $precio       = isset($_POST['precio']) ? (float)$_POST['precio'] : 0.00;
        $stock        = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
        $color_hex    = $_POST['color_hex'] ?? '#333333';

        // Gestión de Imagen
        $nombre_imagen = "default.jpg";
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
            
            // Ruta absoluta específica para XAMPP en Mac
            $directorio_destino = "/Applications/XAMPP/xamppfiles/htdocs/3dprint/images/";
            
            // Validar si la carpeta existe y es escribible
            if (!is_dir($directorio_destino)) {
                throw new Exception("La carpeta de destino no existe en el disco.");
            }

            $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $nombre_imagen = time() . "_" . preg_replace('/[^A-Za-z0-9]/', '', $sku) . "." . $extension;
            $ruta_final = $directorio_destino . $nombre_imagen;

            if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_final)) {
                throw new Exception("Error al mover el archivo. Revisa el tamaño del archivo.");
            }
        }

        // Insertar en BD
        $sql = "INSERT INTO productos (nombre, categoria, subcategoria, sku, descripcion, precio, stock, imagen_url, color_hex) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conexion->prepare($sql);
        if (!$stmt) throw new Exception("Error SQL: " . $conexion->error);

        // 9 parámetros (asumiendo que eliminamos 'disponible' si no lo tienes o ajustamos)
        $stmt->bind_param("sssssdiss", $nombre, $categoria, $subcategoria, $sku, $descripcion, $precio, $stock, $nombre_imagen, $color_hex);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "mensaje" => "Producto guardado correctamente"]);
        } else {
            throw new Exception("Error al ejecutar: " . $stmt->error);
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
    }
}
?>