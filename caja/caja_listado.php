<?php
session_start();
    require_once "../conexion/conexion.php";

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

// Valores por defecto para filtros
if (isset($_GET['fecha_inicio']) && isset($_GET['fecha_fin'])) {
    $fecha_inicio = $_GET['fecha_inicio'];
    $fecha_fin = $_GET['fecha_fin'];
} else {
    // Mostrar todo sin filtrar si no hay fechas
    $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01'); // o una fecha inicial antigua
    $fecha_fin = date('Y-m-d');
}
$caja = $_GET['caja'] ?? '';

// Parámetros para la consulta
$params = [
    ':fecha_inicio' => $fecha_inicio . ' 00:00:00',
    ':fecha_fin' => $fecha_fin . ' 23:59:59',
];

// Consulta base
$sql = "SELECT caja.*, usuarios.nombre AS nombre_usuario
        FROM caja
        LEFT JOIN usuarios ON caja.user_login = usuarios.id
        WHERE fecha_movimiento BETWEEN :fecha_inicio AND :fecha_fin";

// Filtro por caja si se selecciona
if (!empty($caja)) {
    $sql .= " AND caja.caja = :caja";
    $params[':caja'] = $caja;
}

$sql .= " ORDER BY id_movimiento DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$movimientos = $stmt->fetchAll();

// Obtener valores únicos de caja para el select
$cajas_unicas = $pdo->query("SELECT DISTINCT caja FROM caja ORDER BY caja")->fetchAll();

// Totales
$total_ingresos = 0;
$total_egresos = 0;
foreach ($movimientos as $m) {
    $total_ingresos += $m['valor_ingreso'];
    $total_egresos += $m['valor_egreso'];
}
$saldo = $total_ingresos - $total_egresos;

date_default_timezone_set('America/Bogota');
$hora_actual = date('H:i:s');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php require '../logs/head.php'; ?>
    <!-- DataTable-->
    <?php require '../logs/datatables.php'; ?>
</head>
<?php require '../logs/nav-bar.php'; ?>
<div id="layoutSidenav_content">
    <main class="ms-5 me-5">
<body class="container mt-4">

    <h2>Listado de Movimientos de Caja</h2>

    <!-- Filtros -->
    <form class="row g-3 mb-4" method="GET">
        <div class="col-md-3">
            <label class="form-label">Fecha inicio</label>
            <input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($fecha_inicio) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Fecha fin</label>
            <input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($fecha_fin) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Caja</label>
            <select name="caja" class="form-select">
                <option value="">Todas</option>
                <?php foreach ($cajas_unicas as $c): ?>
                    <option value="<?= htmlspecialchars($c['caja']) ?>" <?= $caja === $c['caja'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars(ucfirst($c['caja'])) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        </div>
    </form>

    <!-- Tabla -->
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>id</th>
                <th>Fecha</th>
                <th>Recibo</th>
                <th>F-PAR</th>
                <th>Descripción</th>
                <th>Ingreso</th>
                <th>Egreso</th>
                <th>Tipo</th>
                <th>Usuario</th>
                <th>Liquidado</th>
                <th>Origen</th>
                <th><input type="checkbox" id="selectAll"></th>
                
            </tr>
        </thead>
        <tbody>
            <?php foreach ($movimientos as $mov): ?>
                <tr class="<?= ($mov['liquidado'] === 'SI') ? 'table-secondary' : '' ?>">
                    <?php $fecha = new DateTime($mov['fecha_movimiento']); ?>
                    <td><?= $mov['id_movimiento'] ?></td>                    
                    <td><?= $fecha->format('d/m/Y H:i') ?></td>
                    <td><?= $mov['recibo_id'] ?></td>
                    <td><?= $mov['rec_manual'] ?></td>
                    <td><?= htmlspecialchars($mov['desc_movimiento']) ?></td>
                    <td class="text-success"><?= $mov['valor_ingreso'] ? number_format($mov['valor_ingreso']) : '' ?></td>
                    <td class="text-danger"><?= $mov['valor_egreso'] ? number_format($mov['valor_egreso']) : '' ?></td>
                    <td><?= htmlspecialchars($mov['caja_tipo']) ?></td>
                    <td><?= htmlspecialchars($mov['nombre_usuario'] ?? 'Desconocido') ?></td>
                    <td><?= $mov['liquidado'] === 'SI' ? '✔️' : '❌' ?></td>
                    <td><?= htmlspecialchars($mov['caja']) ?></td>
                    <td>
    <?php if ($mov['liquidado'] === 'SI'): ?>
        <i class="bi bi-lock-fill text-muted"></i>
    <?php else: ?>
        <input type="checkbox" class="check-movimiento" value="<?= $mov['id_movimiento'] ?>">
    <?php endif; ?>
</td>

                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="table-secondary">
                <th colspan="5">Totales</th>
                <th class="text-success"><?= number_format($total_ingresos) ?></th>
                <th class="text-danger"><?= number_format($total_egresos) ?></th>
                <th colspan="5">Saldo: <strong><?= number_format($saldo) ?></strong></th>
            </tr>
        </tfoot>
        
    </table>



<div id="resultadoLiquidacion"></div>
        <!-- Select de "Recibido por" -->
<div class="mb-3">
  <label for="recibido_por" class="form-label">Recibido por</label>
  <select class="form-select" name="recibido_por" id="recibido_por" required>
    <option value="">Seleccione un usuario</option>
    <?php
    $stmt = $pdo->query("SELECT id, nombre FROM usuarios WHERE activo = 1");
    while ($row = $stmt->fetch()) {
      echo "<option value='{$row['id']}'>{$row['nombre']}</option>";
    }
    ?>
  </select>
</div>

    <button type="button" id="btnPreliquidar" class="btn btn-primary">Preliquidar</button>


</main>
</div>
<!-- Modal de Preliquidación -->
<div class="modal fade" id="modalPreliquidacion" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabel">Resumen de Preliquidación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">

        <p><strong>Fecha:</strong> <span id="fechaActual"></span></p>

        <div class="table-responsive">
          <table class="table table-bordered table-striped">
            <thead class="table-dark">
              <tr>
                <th>Recibo</th>
                <th>F-PAR</th>
                <th>Descripción</th>
                <th>Ingreso</th>
                <th>Egreso</th>
              </tr>
            </thead>
            <tbody id="tablaDetalleMovimientos">
              <!-- JS insertará aquí las filas -->
            </tbody>
            <tfoot>
  <tr class="table-secondary">
    <th colspan="3" class="text-end">Totales:</th>
    <th id="totalIngreso" class="text-success text-end">$0</th>
    <th id="totalEgreso" class="text-danger text-end">$0</th>
  </tr>
  <tr class="table-info">
    <th colspan="4" class="text-end">💰 Total a Liquidar:</th>
    <th id="totalLiquidar" class="text-end fw-bold">$0</th>
  </tr>
</tfoot>
          </table>
          <p><strong>Recibido por:</strong> <span name="recibidoPorTexto" id="recibidoPorTexto"></span></p>
        </div>



      </div>
      <div class="mt-0 m-5">
  <label for="observaciones" class="form-label"><strong>Observaciones</strong></label>
  <textarea class="form-control" id="observaciones" rows="3" placeholder="Escribe detalles adicionales..."></textarea>
</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" id="btnLiquidarFinal" class="btn btn-success">Liquidar e Imprimir</button>

      </div>
    </div>
  </div>
</div>


<script>
document.getElementById('btnPreliquidar').addEventListener('click', function () {
    const checkboxes = document.querySelectorAll('.check-movimiento:checked');
    const ids = Array.from(checkboxes).map(cb => cb.value);

    if (ids.length === 0) {
        alert("Selecciona al menos un movimiento.");
        return;
    }

    const params = new URLSearchParams();
    ids.forEach(id => params.append('ids[]', id));

    fetch('caja_resumen_liquidacion.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('fechaActual').textContent = data.fecha;

        const tbody = document.getElementById('tablaDetalleMovimientos');
        tbody.innerHTML = '';

        let totalIngreso = 0;
        let totalEgreso = 0;

        data.movimientos.forEach(m => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${m.recibo_id}</td>
                <td>${m.rec_manual}</td>
                <td>${m.descripcion}</td>
                <td class="text-end text-success">${m.valor_ingreso ? m.valor_ingreso.toLocaleString() : ''}</td>
                <td class="text-end text-danger">${m.valor_egreso ? m.valor_egreso.toLocaleString() : ''}</td>
            `;
            tbody.appendChild(tr);

            totalIngreso += m.valor_ingreso || 0;
            totalEgreso += m.valor_egreso || 0;
        });

        document.getElementById('totalIngreso').textContent = '$' + totalIngreso.toLocaleString();
        document.getElementById('totalEgreso').textContent = '$' + totalEgreso.toLocaleString();

        const saldo = totalIngreso - totalEgreso;
        document.getElementById('totalLiquidar').textContent = '$' + saldo.toLocaleString();

        // Guardar IDs para liquidación
        document.getElementById('btnLiquidarFinal').dataset.ids = ids.join(',');

        // Mostrar el modal
        new bootstrap.Modal(document.getElementById('modalPreliquidacion')).show();

        const select = document.getElementById("recibido_por");
        const texto = select.options[select.selectedIndex].text;

        // Mostrar en el modal
        document.getElementById("recibidoPorTexto").textContent = texto;
    })
    
    .catch(error => {
        console.error("Error:", error);
    });
});
</script>
<script>
document.getElementById('btnLiquidarFinal').addEventListener('click', function () {
    const ids = this.dataset.ids.split(',');
    const observaciones = document.getElementById('observaciones').value;
    const recibido_por = document.getElementById('recibido_por').value;

    if (confirm("¿Estás seguro de que deseas liquidar estos movimientos?")) {
        const params = new URLSearchParams();
        ids.forEach(id => params.append('ids[]', id));
        params.append('observaciones', observaciones);
        params.append('recibido_por', recibido_por);

        fetch('caja_liquidar_final.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: params
})
.then(res => res.json())
.then(response => {
    if (response.success) {
        alert(response.message);
        // Abrir la nueva ventana con el ID de liquidación
        window.open("caja_ticket_liquidacion.php?id=" + response.id_liquidacion, "_blank");
        location.reload(); // opcional
    } else {
        alert("Error: " + response.message);
    }
})
.catch(error => {
    alert("Error en la solicitud: " + error);
});

        
    }
});
</script>


</body>
</html>
