<?php
/**
 * Script: get_product.php
 * Finalidad: Listado dinámico de productos con soporte para paginación, filtros y roles (Admin/User).
 */

error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

include_once "cors.php";

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    include_once "conexion.php";

    if (!isset($conexion)) {
        throw new Exception("Fallo crítico: No se pudo instanciar la conexión a la DB.");
    }

    /**
     * GESTIÓN DE PARÁMETROS:
     * Recepción de variables para control de navegación (paginación) y filtros de búsqueda.
     */
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 15;
    $ordenSolicitado = $_GET['orden'] ?? 'Relevante';
    $busqueda = isset($_GET['q']) ? $conexion->real_escape_string($_GET['q']) : '';
    $esAdmin = isset($_GET['admin']) && $_GET['admin'] === 'true'; 

    // Lógica de Paginación: Cálculo del OFFSET para el cursor de la base de datos
    $page = max($page, 1);
    $limit = max($limit, 1);
    $offset = ($page - 1) * $limit;

    

    /**
     * FILTRADO DINÁMICO (WHERE):
     * Permite al CMS visualizar productos ocultos (disponible=0) mientras que el catálogo público los filtra.
     */
    $where = $esAdmin ? "WHERE 1=1" : "WHERE disponible = 1";

    // Búsqueda textual mediante operador LIKE
    if (!empty($busqueda)) {
        $where .= " AND (nombre LIKE '%$busqueda%' OR sku LIKE '%$busqueda%')";
    }

    // Filtrado por múltiples categorías utilizando la cláusula SQL 'IN'
    $categorias = isset($_GET['categorias']) && !empty($_GET['categorias']) ? explode(',', $_GET['categorias']) : [];
    if (!empty($categorias)) {
        $categoriasEscapadas = array_map(fn($cat) => "'" . $conexion->real_escape_string($cat) . "'", $categorias);
        $where .= " AND categoria IN (" . implode(",", $categoriasEscapadas) . ")";
    }

    /**
     * CÁLCULO DE METADATOS:
     * Necesario para que el componente de paginación de React conozca el total de páginas.
     */
    $orderBy = "id DESC";
    if ($ordenSolicitado === 'precio_asc') $orderBy = "precio ASC";
    elseif ($ordenSolicitado === 'precio_desc') $orderBy = "precio DESC";

    $totalQuery = "SELECT COUNT(*) AS total FROM productos $where";
    $totalResult = $conexion->query($totalQuery);
    $totalProductos = (int)$totalResult->fetch_assoc()['total'];

    /**
     * CONSULTA FINAL:
     * Recupera los registros aplicando todos los filtros y el rango de paginación.
     */
    $sql = "SELECT id, nombre, descripcion, categoria, subcategoria, 
                   sku, precio, stock, imagen_url, disponible, color_hex
            FROM productos
            $where
            ORDER BY $orderBy
            LIMIT $limit OFFSET $offset";

    $resultado = $conexion->query($sql);

    // Mapeo y tipado de datos para asegurar integridad en el cliente
    $productos = [];
    while ($row = $resultado->fetch_assoc()) {
        $productos[] = [
            'id' => (int)$row['id'],
            'nombre' => $row['nombre'],
            'descripcion' => $row['descripcion'],
            'sku' => $row['sku'],
            'precio' => (float)$row['precio'],
            'stock' => (int)$row['stock'],
            'categoria' => $row['categoria'],
            'subcategoria' => $row['subcategoria'],
            'imagen_url' => $row['imagen_url'],
            'disponible' => (bool)$row['disponible'],
            'color_hex' => $row['color_hex']
        ];
    }

    // Respuesta estructurada con datos de navegación y catálogo
    echo json_encode([
        'page' => $page,
        'limit' => $limit,
        'total' => $totalProductos,
        'productos' => $productos
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'mensaje' => $e->getMessage()]);
} finally {
    if (isset($conexion)) $conexion->close();
}
?>