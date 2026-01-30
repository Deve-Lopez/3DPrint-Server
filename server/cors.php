<?php
/**
 * CORS Configuration (Cross-Origin Resource Sharing)
 * Permite que el frontend (React) y el backend (PHP) se comuniquen
 * a pesar de estar en diferentes puertos o dominios.
 */

/* Permite peticiones desde cualquier origen (*) */
header("Access-Control-Allow-Origin: *");

/* Define los verbos HTTP permitidos para las peticiones a la API */
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

/* Especifica qué cabeceras personalizadas pueden enviarse en la petición */
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

/**
 * Directiva 'Allow': 
 * Refuerza la configuración para servidores Apache, asegurando que los métodos 
 * necesarios estén habilitados en la configuración del servidor.
 */
header("Allow: GET, POST, OPTIONS, PUT, DELETE"); 

/**
 * Manejo del Preflight Request:
 * El navegador envía una petición automática de tipo 'OPTIONS' antes de peticiones 
 * como POST o PUT para verificar si el servidor acepta la comunicación.
 */
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Si la petición es OPTIONS, respondemos con un 200 (OK) y finalizamos el script.
    http_response_code(200);
    exit();
}


?>