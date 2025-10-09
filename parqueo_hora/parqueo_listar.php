<?php
require '../conexion/conexion.php';
date_default_timezone_set('America/Bogota');

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
                <button class="salida-btn" data-id="<?= $p['parqueo_id'] ?>">Registrar Salida</button>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<script>
document.querySelectorAll('.salida-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        if(confirm('¿Registrar salida de este vehículo?')) {
            const id = this.dataset.id;
            fetch('parqueo_salida.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + id
            })
            .then(res => res.text())
            .then(resp => {
                alert(resp);
                location.reload();
            })
            .catch(err => console.error('Error:', err));
        }
    });
});
</script>
