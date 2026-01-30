<?php
/**
 * Script: add_product.php
 * Finalidad: Procesar el alta de nuevos productos desde el panel de administración.
 * Gestiona la validación de datos, la carga de archivos (imágenes) y la persistencia en MySQL.
 */

// Definición de cabecera para que el cliente (React) interprete la respuesta como JSON
header('Content-Type: application/json');

// Inclusión de dependencias: CORS para permitir peticiones externas y la conexión a la DB
include_once "cors.php";
include_once "conexion.php";

/**
 * CONFIGURACIÓN DE ERRORES:
 * En producción (o entrega de proyecto), desactivamos la visualización de errores 
 * nativos de PHP para no exponer información sensible de la arquitectura.
 */
error_reporting(0);
ini_set('display_errors', 0);

// Verificación del método de petición: Solo aceptamos POST para creación de recursos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validación de la integridad de la conexión antes de operar
        if ($conexion->connect_error) {
            throw new Exception("Error de conexión: " . $conexion->connect_error);
        }

        /**
         * RECOGIDA DE DATOS:
         * Utilizamos el operador Null Coalesce (??) para asignar valores por defecto 
         * y el casting (float/int) para asegurar tipos de datos correctos en la base de datos.
         */
        $nombre       = $_POST['nombre'] ?? '';
        $categoria    = $_POST['categoria'] ?? '';
        $subcategoria = $_POST['subcategoria'] ?? null;
        $sku          = $_POST['sku'] ?? '';
        $descripcion  = $_POST['descripcion'] ?? null;
        $precio       = isset($_POST['precio']) ? (float)$_POST['precio'] : 0.00;
        $stock        = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
        $color_hex    = $_POST['color_hex'] ?? '#333333';
        
        // Flag de disponibilidad (Boolean en DB representado como TinyInt)
        $disponible   = isset($_POST['disponible']) ? (int)$_POST['disponible'] : 1;

        /**
         * GESTIÓN DE CARGA DE IMÁGENES:
         * Implementa la lógica para mover archivos desde el directorio temporal del sistema
         * a la carpeta de assets del servidor web.
         */
        $nombre_imagen = "default.jpg";
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
            
            // Ruta absoluta del servidor (Entorno local XAMPP)
            $directorio_destino = "/Applications/XAMPP/xamppfiles/htdocs/3dprint/images/";
            
            if (!is_dir($directorio_destino)) {
                throw new Exception("La carpeta de destino no existe en el disco.");
            }

            // Normalización del nombre del archivo: timestamp + SKU limpio para evitar colisiones
            $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $nombre_imagen = time() . "_" . preg_replace('/[^A-Za-z0-9]/', '', $sku) . "." . $extension;
            $ruta_final = $directorio_destino . $nombre_imagen;

            // Mover el archivo de la carpeta temporal a la ruta final
            if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_final)) {
                throw new Exception("Error al mover el archivo.");
            }
        }

        /**
         * SEGURIDAD: SENTENCIAS PREPARADAS
         * Evita Inyección SQL separando la estructura de la consulta de los datos dinámicos.
         */
        $sql = "INSERT INTO productos (nombre, categoria, subcategoria, sku, descripcion, precio, stock, imagen_url, color_hex, disponible) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conexion->prepare($sql);
        if (!$stmt) throw new Exception("Error SQL: " . $conexion->error);

        /**
         * BIND PARAM: Vinculación de tipos de datos.
         * s = string, d = double (float), i = integer.
         */
        $stmt->bind_param("sssssdissi", 
            $nombre, 
            $categoria, 
            $subcategoria, 
            $sku, 
            $descripcion, 
            $precio, 
            $stock, 
            $nombre_imagen, 
            $color_hex,
            $disponible
        );

        if ($stmt->execute()) {
            // Respuesta exitosa al cliente React
            echo json_encode(["status" => "success", "mensaje" => "Producto guardado correctamente"]);
        } else {
            throw new Exception("Error al ejecutar: " . $stmt->error);
        }

    } catch (Exception $e) {
        // En caso de error, devolvemos código 500 y el mensaje detallado
        http_response_code(500);
        echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
    }
}
?>