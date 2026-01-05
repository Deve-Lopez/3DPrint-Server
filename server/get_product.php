<?php
/**
 * Global Product Catalog Endpoint
 * Orquestador de consultas complejas: Gestiona paginación, filtrado por categorías,
 * ordenación dinámica y normalización de tipos de datos para el frontend.
 */

// Desactivación de errores nativos para evitar corrupción del JSON de salida
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

include_once "cors.php"; // Protocolo de seguridad CORS

/**
 * Preflight Request Handling:
 * Responde al handshake inicial de los navegadores para autorizar la petición.
 */
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    include_once "conexion.php"; // Resource Provider

    if (!isset($conexion)) {
        throw new Exception("Fallo crítico: No se pudo instanciar la conexión a la DB.");
    }

    // ============================================================
    // PARÁMETROS DE ENTRADA
    // Recuperación de parámetros de URL para paginación y orden
    // ============================================================
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 15;
    $ordenSolicitado = $_GET['orden'] ?? 'Relevante';

    // Validación de rangos mínimos
    $page = max($page, 1);
    $limit = max($limit, 1);
    $offset = ($page - 1) * $limit;

    // ============================================================
    // MOTOR DE FILTRADO (DYNAMIC QUERY BUILDER)
    // Descompone los parámetros de URL para construir la cláusula SQL.
    // ============================================================
    $categorias = isset($_GET['categorias']) && !empty($_GET['categorias'])
        ? explode(',', $_GET['categorias'])
        : [];

    $where = "WHERE disponible = 1"; // Base de la consulta: Solo productos activos

    if (!empty($categorias)) {
        /**
         * SQL Injection Mitigation:
         * Escapamos cada valor individualmente antes de la implosión en la query.
         */
        $categorias = array_map(fn($cat) => "'" . $conexion->real_escape_string($cat) . "'", $categorias);
        $where .= " AND categoria IN (" . implode(",", $categorias) . ")";
    }

    // ============================================================
    // LÓGICA DE ORDENACIÓN (DINÁMICA)
    // Mapea el parámetro 'orden' a instrucciones ORDER BY válidas
    // ============================================================
    $orderBy = "id DESC"; // Estado por defecto: Más nuevos primero

    if ($ordenSolicitado === 'precio_asc') {
        $orderBy = "precio ASC";
    } elseif ($ordenSolicitado === 'precio_desc') {
        $orderBy = "precio DESC";
    }

    // ============================================================
    // METADATOS: TOTAL DE REGISTROS
    // Necesario para que el frontend calcule el número de páginas disponibles.
    // ============================================================
    $totalQuery = "SELECT COUNT(*) AS total FROM productos $where";
    $totalResult = $conexion->query($totalQuery);

    if (!$totalResult) {
        throw new Exception("Error al calcular el conteo de inventario.");
    }

    $totalRows = $totalResult->fetch_assoc();
    $totalProductos = (int)$totalRows['total'];

    // ============================================================
    // CONSULTA PRINCIPAL Y HIDRATACIÓN DE DATOS
    // Extracción de la entidad con límites de segmentación y orden dinámico.
    // ============================================================
    $sql = "SELECT id, nombre, descripcion, categoria, subcategoria, 
                   sku, precio, stock, imagen_url, disponible, color_hex
            FROM productos
            $where
            ORDER BY $orderBy
            LIMIT $limit OFFSET $offset";

    $resultado = $conexion->query($sql);

    if (!$resultado) {
        throw new Exception("Excepción en la ejecución SQL: " . $conexion->error);
    }

    // Mapeo y Type Casting manual para asegurar consistencia en el JSON de salida
    $productos = [];
    while ($row = $resultado->fetch_assoc()) {
        $productos[] = [
            'id' => (int)$row['id'],
            'nombre' => $row['nombre'] ?? '',
            'descripcion' => $row['descripcion'] ?? '',
            'categoria' => $row['categoria'] ?? '',
            'subcategoria' => $row['subcategoria'] ?? '',
            'sku' => $row['sku'] ?? '',
            'precio' => (float)$row['precio'],
            'stock' => (int)$row['stock'],
            'imagen_url' => $row['imagen_url'] ?? '',
            'disponible' => (bool)$row['disponible'],
            'color_hex' => $row['color_hex'] ?? ''
        ];
    }

    // Serialización final de la respuesta paginada
    http_response_code(200);
    echo json_encode([
        'page' => $page,
        'limit' => $limit,
        'total' => $totalProductos,
        'productos' => $productos
    ]);

} catch (Exception $e) {
    // Exception Management: Captura fallos críticos y retorna feedback semántico
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'mensaje' => $e->getMessage()
    ]);
}

// Cierre preventivo de recursos del motor SQL
if (isset($conexion)) {
    $conexion->close();
}
?>