<?php

include_once "cors.php";
include_once "conexion.php";

header('Content-Type: application/json');

try{
    $resultado = $conexion->query("SELECT id, nombre FROM roles");
    $roles=[];
    while($row = $resultado -> fetch_assoc()){
        $roles[] = $row;
    }

    echo json_encode(["status" => "success", "data" => $roles]);
} catch (Exception $e){
    echo json_encode(["status" => "error", "mensaje" => $e->getMessage()]);
}

?>