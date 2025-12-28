<?php
/**
 * API Security Headers & CORS Controller
 * Configura el protocolo de intercambio de recursos entre orígenes.
 * Este bloque es mandatorio para permitir transacciones asíncronas desde el cliente React.
 */

// Permite peticiones desde cualquier origen (Cross-Origin Resource Sharing)
header("Access-Control-Allow-Origin: *");

// Define los verbos HTTP autorizados para las operaciones CRUD del sistema
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// Autoriza las cabeceras específicas necesarias para el envío de payloads JSON
header("Access-Control-Allow-Headers: Content-Type, Authorization");

/**
 * Preflight Request Handling:
 * Los navegadores modernos envían una petición 'OPTIONS' antes de un POST/PUT/DELETE
 * para validar los permisos de seguridad (CORS Handshake).
 */
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Retorna un HTTP Status 200 (OK) y finaliza la ejecución para evitar procesado innecesario
    http_response_code(200);
    exit();
}

/*  */
?>