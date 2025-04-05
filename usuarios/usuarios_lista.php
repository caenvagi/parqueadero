<?php
require_once "../conexion/conexion.php";

// CONSULTA USUARIOS EN TABLA USUARIOS    
    try {
        // Verifica que $pdo esté definido correctamente

        // Preparar la consulta
        $stmt = $pdo->prepare("SELECT * FROM usuarios");

        // Ejecutar la consulta
        $stmt->execute();

        // Obtener resultados
        $usuarios = $stmt->fetchAll();

        // Validar si hay datos
        if (!empty($usuarios)) {
            // foreach ($usuarios as $usuario) {
            //     echo "ID: " . htmlspecialchars($usuario['id']) . " - Nombre: " . htmlspecialchars($usuario['nombre']) . "<br>";
            // }
        } else {
            echo "No se encontraron cargos.";
        }
    } catch (PDOException $e) {
        error_log("Error en la consulta: " . $e->getMessage());
        echo "Error al consultar los cargos.";
    }


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php require '../logs/head.php'; ?>
</head>
<body>
    <style>
    table {
        border-collapse: collapse;
        width: 100%;
    }
    th, td {
        border: 1px solid black;
        padding: 8px;
        text-align: center;
    }
    </style>
    <table>
        <tr>
            <th>ID</th>
            <th>NOMBRE</th>
            <th>CARGO</th>
            <th>TELEFONO</th>            
            <th>USUARIO</th>
            <th>CLAVE</th>
            <th>TIPO</th>
            <th>AVATAR</th>
            <th>ACTIVO</th>
            <th>CONTABILIDAD</th><th></th>
        </tr>
        
            <?php foreach ($usuarios as $usuario): ?>
        <tr>
            <td><?= htmlspecialchars($usuario['id']) ?></td>
            <td><?= htmlspecialchars($usuario['nombre']) ?></td>
            <td><?= htmlspecialchars($usuario['tipo_cargo']) ?></td>
            <td><?= htmlspecialchars($usuario['telefono']) ?></td>
            <td><?= htmlspecialchars($usuario['usuario']) ?></td>
            <td><?= htmlspecialchars($usuario['clave']) ?></td>
            <td><?= htmlspecialchars($usuario['tipo_usuario']) ?></td>
            <td><?= htmlspecialchars($usuario['avatar']) ?></td>
            <td><?= htmlspecialchars($usuario['activo']) ?></td>
            <td><?= htmlspecialchars($usuario['contabilidad']) ?></td>
        </tr>   
            <?php endforeach; ?>
        
    </table>
    
    
    
</body>
</html>