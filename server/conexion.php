<?php
/**
 * Database Connection Provider
 * Motor de conectividad MySQL mediante el driver MySQLi.
 * Centraliza los parámetros de red y las credenciales de la base de datos.
 */

// Configuración de parámetros de red y autenticación para el entorno de desarrollo local
$host = "localhost";
$usuario = "root";      
$contrasena = "";       
$base_datos = "3dprint";

/**
 * Configuración del puerto: Se utiliza el 3307.  */
$puerto = 3307;        

/**
 * Crea una nueva instancia de la clase mysqli para establecer la comunicación con el motor SQL.
 */
$conexion = new mysqli($host, $usuario, $contrasena, $base_datos, $puerto);

/**
 * Valida la disponibilidad del servicio y gestiona excepciones de conectividad inicial.
 * El objeto 'connect_error' contendrá el mensaje de fallo si la conexión no puede establecerse.
 */
if ($conexion->connect_error) {
    /**
     * Interrupción del proceso mediante 'die' en caso de fallo crítico de red o credenciales.
     */
    die("❌ Error de conectividad: " . $conexion->connect_error);
}

/**
 * Sincronización de Charset:  */
$conexion->set_charset("utf8mb4");

?>