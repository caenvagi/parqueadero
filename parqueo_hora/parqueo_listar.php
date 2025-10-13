<?php
session_start();
    require_once "../conexion/conexion.php";

    date_default_timezone_set('America/Bogota');

    if (!isset($_SESSION['id'])) {
        header("Location: index.php");
    }
    $id = $_SESSION['id'];
    $tipo_usuario = $_SESSION['tipo_usuario'];
    
    if ($tipo_usuario == 1) {
        $where = "";
    } else if ($tipo_usuario == 2) {
        $where = "WHERE id=$id";
    }

$sql = "
SELECT 
    p.parqueo_id,
    c.nombre AS cliente_nombre,
    cat.cat_nombre AS categoria_vehiculo,
    p.placa_cli,
    p.fecha_ini,
    t.tar_valor AS tarifa_hora,
    t.tar_bloque,
    t.tar_categoria,
    p.caseta,
    p.estado,
    P.tarifa,
    cs.casetas_nom as caseta
FROM parqueo p
INNER JOIN cliente c ON p.placa_cli = c.placa
INNER JOIN tarifas t ON c.categoria = t.tar_categoria
INNER JOIN categorias cat ON c.categoria = cat.cat_id
INNER JOIN tar_tiempo tt ON t.tar_categoria = tt.tar_id_nombre
INNER JOIN casetas cs ON p.caseta = cs.caseta_id
WHERE p.estado = 'SI'and tar_nombre = 1
ORDER BY p.fecha_ini ASC
";
$stmt = $pdo->query($sql);
$parqueos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<table border="1" cellpadding="5" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Placa</th>
            <th>Categoría</th>
            <th>Fecha Inicio</th>
            <th>Tiempo Transcurrido</th>
            <th>Tarifa /hora</th>
            <th>Tarifa Bloque (12h)</th>
            <th>Total</th>
            <th>Caseta</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($parqueos as $p): 
        $inicio = new DateTime($p['fecha_ini']);
        $ahora = new DateTime();
        $intervalo = $inicio->diff($ahora);

        $minutosTotales = ($intervalo->days * 24 * 60) + ($intervalo->h * 60) + $intervalo->i;

        $tarifaHora = (float)$p['tarifa_hora'];
        $tarifaBloque = (float)$p['tar_bloque'];
        $total = 0;

        // 🕒 Lógica de cobro con periodo de gracia de 15 minutos
        if ($minutosTotales <= 15) {
            $total = 0;
        } else {
            $minutosTotales -= 15;
            $bloques = floor($minutosTotales / 720); // Cada bloque = 12h = 720min
            $restoMinutos = $minutosTotales % 720;
            $horasResto = ceil($restoMinutos / 60);
            $total = ($bloques * $tarifaBloque);

            if ($restoMinutos > 0) {
                $costoResto = $horasResto * $tarifaHora;
                $total += ($costoResto >= $tarifaBloque) ? $tarifaBloque : $costoResto;
            }
        }

        $tiempoTexto = sprintf("%dd %02dh %02dm", $intervalo->d, $intervalo->h, $intervalo->i);
    ?>
        <tr>
            <td><?= $p['parqueo_id'] ?></td>
            <td><?= htmlspecialchars($p['cliente_nombre']) ?></td>
            <td><?= htmlspecialchars($p['placa_cli']) ?></td>
            <td><?= htmlspecialchars($p['categoria_vehiculo']) ?></td>
            <td><?= $p['fecha_ini'] ?></td>
            <td><?= $tiempoTexto ?></td>
            <td>$<?= number_format($tarifaHora, 0, ',', '.') ?></td>
            <td>$<?= number_format($tarifaBloque, 0, ',', '.') ?></td>
            <td><b>$<?= number_format($total, 0, ',', '.') ?></b></td>
            <td><?= $p['caseta'] ?></td>
            <td>
                <button class="btn-salida" data-id="<?= $p['parqueo_id'] ?>">Registrar Salida</button>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {

    // Al hacer click en "Registrar salida"
    $(document).on('click', '.btn-salida', function() {
        const id = $(this).data('id');
        const boton = $(this);

        if (!confirm('¿Confirmar salida de este vehículo?')) return;

        boton.prop('disabled', true).text('Procesando...');

        $.ajax({
            url: 'registrar_salida.php',
            method: 'POST',
            data: { parqueo_id: id },
            dataType: 'json',
            success: function(res) {
                if (res.ok) {
                    alert(
                        '✅ Salida registrada correctamente.\n' +
                        '⏱ Tiempo total: ' + res.tiempo + '\n' +
                        '💰 Valor total: $' + res.total.toLocaleString()
                    );

                     window.open('../modulos/imprimir_ticket_php/recibo.php', '_blank', 'width=400,height=600');
                    $('#formParqueo')[0].reset();

                    // Eliminar la fila del listado sin recargar
                    boton.closest('tr').fadeOut(600, function() {
                        $(this).remove();
                    });

                } else {
                    alert('⚠️ Error: ' + res.error);
                    boton.prop('disabled', false).text('Registrar salida');
                }
            },
            error: function(xhr, status, error) {
                alert('❌ Error al conectar con el servidor: ' + error);
                boton.prop('disabled', false).text('Registrar salida');
            }
        });
    });

});
</script>
