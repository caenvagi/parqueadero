<?php
session_start();
require_once "../conexion/conexion.php";



?>
<!DOCTYPE html>
<html lang="es">

<head>
    <?php require '../logs/head.php'; ?>
</head>

<body>
    <?php require '../logs/nav-bar.php'; ?>
    <div id="layoutSidenav_content">
        <main>
            <div class="card m-2">
                <div class="card-header">Turnos Por Pagar</div>
                <div class="card-body m-2">
                <?php
                if (isset($_POST['turnos']) && is_array($_POST['turnos'])) {
                    $turnos = array_map('intval', $_POST['turnos']);
                    $placeholders = implode(',', array_fill(0, count($turnos), '?'));

                    // Obtener detalles de turnos seleccionados
                    $stmt = $pdo->prepare("SELECT t.id_turno, t.usuario_id, u.nombre, t.fecha_inicio, t.valor
                           FROM usuarios_turnos t
                           JOIN usuarios u ON t.usuario_id = u.id
                           WHERE t.id_turno IN ($placeholders)
                           ORDER BY t.usuario_id, t.fecha_inicio");
                    $stmt->execute($turnos);
                    $turnos_seleccionados = $stmt->fetchAll();

                    // Agrupar por usuario
                    $agrupado = [];
                    foreach ($turnos_seleccionados as $t) {
                        $id = $t['usuario_id'];
                        if (!isset($agrupado[$id])) {
                            $agrupado[$id] = [
                                'nombre' => $t['nombre'],
                                'turnos' => [],
                                'total' => 0
                            ];
                        }
                        $agrupado[$id]['turnos'][] = $t;
                        $agrupado[$id]['total'] += $t['valor'];
                    }

                    echo "<h4>Previsualización del Recibo</h4>";
                    foreach ($agrupado as $usuario_id => $info) {
                        echo "<h3>Empleado: {$info['nombre']} (ID: $usuario_id)</h3>";
                        echo "<ul>";
                        foreach ($info['turnos'] as $t) {
                            echo "<li>Turno N° {$t['id_turno']} - Fecha: {$t['fecha_inicio']} - Valor: $ {$t['valor']}</li>";
                        }
                        echo "</ul>";
                        echo "<strong>Total a pagar: $ {$info['total']}</strong><br><br>";
                    }

                    // Guardar IDs seleccionados para la siguiente acción
                    echo '<form method="POST" action="generar_recibo_pdf.php" target="_blank" onsubmit="redirigirDespues();">';
                    foreach ($turnos as $id) {
                        echo '<input type="hidden" name="turnos[]" value="' . htmlspecialchars($id) . '">';
                    }
                    echo '<div class="text-center mb-2">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-receipt"></i> Confirmar y Generar Recibo
            </button>
          </div>';
                    echo '</form>';
                } else {
                    echo "⚠️ No se seleccionaron turnos.";
                }
                ?>
                </div>
            </div>
        </main>
    </div>
    <script>
        function redirigirDespues() {
            setTimeout(function() {
                window.location.href = "usuarios_turnos_nomina.php";
            }, 1000); // redirige después de 1 segundo
        }
    </script>