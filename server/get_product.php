<?php
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

    // --- PARÁMETROS DE ENTRADA ---
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 15;
    $ordenSolicitado = $_GET['orden'] ?? 'Relevante';
    $busqueda = isset($_GET['q']) ? $conexion->real_escape_string($_GET['q']) : '';
    $esAdmin = isset($_GET['admin']) && $_GET['admin'] === 'true'; // Detectar si es el CMS

    $page = max($page, 1);
    $limit = max($limit, 1);
    $offset = ($page - 1) * $limit;

    // --- CONSTRUCCIÓN DE LA QUERY (WHERE) ---
    // Si es admin, ve todo (1=1). Si es cliente, solo lo disponible.
    $where = $esAdmin ? "WHERE 1=1" : "WHERE disponible = 1";

    // Filtro por búsqueda (Nombre o SKU)
    if (!empty($busqueda)) {
        $where .= " AND (nombre LIKE '%$busqueda%' OR sku LIKE '%$busqueda%')";
    }

    // Filtro por categorías
    $categorias = isset($_GET['categorias']) && !empty($_GET['categorias']) ? explode(',', $_GET['categorias']) : [];
    if (!empty($categorias)) {
        $categoriasEscapadas = array_map(fn($cat) => "'" . $conexion->real_escape_string($cat) . "'", $categorias);
        $where .= " AND categoria IN (" . implode(",", $categoriasEscapadas) . ")";
    }

    // --- ORDENACIÓN ---
    $orderBy = "id DESC";
    if ($ordenSolicitado === 'precio_asc') $orderBy = "precio ASC";
    elseif ($ordenSolicitado === 'precio_desc') $orderBy = "precio DESC";

    // --- METADATOS: TOTAL ---
    $totalQuery = "SELECT COUNT(*) AS total FROM productos $where";
    $totalResult = $conexion->query($totalQuery);
    $totalProductos = (int)$totalResult->fetch_assoc()['total'];

    // --- CONSULTA PRINCIPAL ---
    $sql = "SELECT id, nombre, descripcion, categoria, subcategoria, 
                   sku, precio, stock, imagen_url, disponible, color_hex
            FROM productos
            $where
            ORDER BY $orderBy
            LIMIT $limit OFFSET $offset";

    $resultado = $conexion->query($sql);

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