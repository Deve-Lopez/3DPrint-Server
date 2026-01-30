<?php
/**
 * Script: get_roles.php
 * Finalidad: Recuperar el catálogo de roles disponibles para la gestión de permisos.
 */

include_once "cors.php";
include_once "conexion.php";

header('Content-Type: application/json');

try {
    /**
     * Consulta simple:
     * Obtenemos el identificador y la etiqueta descriptiva de cada rol.
     */
    $resultado = $conexion->query("SELECT id, nombre FROM roles");
    
    $roles = [];
    while($row = $resultado->fetch_assoc()){
        $roles[] = $row;
    }

    // Respuesta exitosa para la hidratación de selectores o validaciones en el CMS
    echo json_encode(["status" => "success", "data" => $roles]);

} catch (Exception $e) {
    // Captura de errores de conectividad o de motor SQL
    echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
}

?>