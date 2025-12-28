<?php
/**
 * Database Connection Provider
 * Motor de conectividad MySQL mediante el driver MySQLi.
 * Centraliza los parámetros de red y las credenciales de la base de datos.
 */

// Configuración de parámetros de red y autenticación
$host = "localhost";
$usuario = "root";      // Usuario administrativo (XAMPP default)
$contrasena = "";       // Credencial de acceso
$base_datos = "3dprint";
$puerto = 3307;         // Standard Port Configuration (MySQL Instance)

/**
 * Instance Initialization:
 * Crea una nueva instancia de la clase mysqli para la comunicación con el motor SQL.
 */
$conexion = new mysqli($host, $usuario, $contrasena, $base_datos, $puerto);

/**
 * Connection Integrity Check:
 * Valida la disponibilidad del servicio y gestiona excepciones de conectividad inicial.
 */
if ($conexion->connect_error) {
    // Interrupción del proceso en caso de fallo crítico de red o credenciales
    die("❌ Error de conectividad: " . $conexion->connect_error);
}

// Sincronización de Charset: Asegura la integridad de caracteres especiales y símbolos (EUR)
$conexion->set_charset("utf8mb4");

?>