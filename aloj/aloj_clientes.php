<?php
session_start();
require_once "../conexion/conexion.php";

// --- Procesar formulario ---
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre      = strtoupper(trim($_POST['nombre']));
    $documento   = strtoupper(trim($_POST['documento']));
    $telefono    = trim($_POST['telefono']);
    $procedencia = strtoupper(trim($_POST['procedencia']));
    $placa_vehiculo = strtoupper(trim($_POST['placa_vehiculo']));
    $usuario_id  = $_SESSION['id'] ?? null;

    // 🔽 Aquí va la limpieza de la placa
    $placa_vehiculo = strtoupper(trim($_POST['placa_vehiculo']));
    $placa_vehiculo = str_replace('-', '', $placa_vehiculo); // Resultado: AAA111 o AAA11A

    if ($usuario_id && $nombre && $documento && $telefono && $procedencia && $placa_vehiculo) {
        try {
            $sql = "INSERT INTO aloj_clientes (nombre, documento, telefono, procedencia, placa_vehiculo, usuario_id, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $documento, $telefono, $procedencia, $placa_vehiculo, $usuario_id]);
            $mensaje = "✅ Cliente registrado correctamente.";
        } catch (PDOException $e) {
            $mensaje = "❌ Error al guardar: " . $e->getMessage();
        }
    } else {
        $mensaje = "⚠️ Todos los campos son obligatorios.";
    }
}

$municipios = [
    "Bogotá",
    "Medellín",
    "Cali",
    "Barranquilla",
    "Cartagena",
    "Bucaramanga",
    "Manizales",
    "Pereira",
    "Cúcuta",
    "Neiva",
    "Villavicencio",
    "Ibagué",
    "Santa Marta",
    "Tunja",
    "Armenia"
];

// --- Obtener clientes para mostrar ---
$clientes = [];
try {
    $stmt = $pdo->query("SELECT * FROM aloj_clientes ORDER BY id DESC");
    $clientes = $stmt->fetchAll();
} catch (PDOException $e) {
    $mensaje = "❌ Error al cargar clientes: " . $e->getMessage();
}

$fecha_reserva = isset($_GET['fecha']) ? $_GET['fecha'] : null;


?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php require '../logs/head.php'; ?>
    <!-- DataTable-->
    <?php require '../logs/datatables.php'; ?>
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
</head>
<?php require '../logs/nav-bar.php'; ?>
<div id="layoutSidenav_content">
    <main class="ms-5 me-5">
        <body class="bg-light">
            <div class="container mt-4">
                <!-- <h2 class="mb-4">Registro de Clientes</h2> -->
                <?php if (!empty($mensaje)): ?>
                    <div class="alert alert-info"><?= $mensaje ?></div>
                <?php endif; ?>

                <div class="row">
                    <!-- Formulario -->
                    <div class="col-md-12">
                        <div class="card p-4 shadow-sm">
                            <form action="aloj_clientes_reserva_procesar.php" method="POST" id="formClientes" class="row g-3">
                                <h5 class="mt-1">Datos del cliente</h5>
                                <!-- Datos del cliente -->
                                <div class="col-md-6">
                                    <label>Nombre:</label>
                                    <input type="text" class="form-control" name="nombre" placeholder="Nombre" onkeyup="javascript:this.value=this.value.toUpperCase();" aria-label="nombre" aria-describedby="basic-addon1" required autofocus>
                                </div>

                                <div class="col-md-3">
                                    <label>Documento:</label>
                                    <input type="text"
                                        placeholder="Documento"
                                        name="documento"
                                        id="documento"
                                        class="form-control"
                                        required
                                        minlength="5"
                                        maxlength="10"
                                        oninput="validarDocumento(this)">
                                    <div id="documentoError" class="text-danger small mt-1"></div>
                                </div>

                                <div class="col-md-3">
                                    <label>Teléfono:</label>
                                    <input type="text"
                                        placeholder="Telefono"
                                        name="telefono"
                                        id="telefono"
                                        class="form-control"
                                        required
                                        maxlength="12"
                                        oninput="formatearTelefono(this)">
                                    <div id="telefonoError" class="text-danger small mt-1"></div>
                                </div>

                                <div class="col-md-6">
                                    <label>Procedencia:</label>
                                    <select name="procedencia" id="procedencia" class="form-select" required>
                                        <option value="">Cargando municipios...</option>
                                    </select>
                                    <div id="procedenciaError" class="text-danger small mt-1"></div>
                                </div>

                                <div class="col-md-3">
                                    <label>Placa del vehículo:</label>
                                    <input type="text"
                                        placeholder="Placa vehiculo"
                                        name="placa_vehiculo"
                                        id="placa_vehiculo"
                                        class="form-control"
                                        maxlength="7"
                                        required
                                        oninput="formatearPlaca(this)">
                                    <div id="placaError" class="text-danger small mt-1"></div>
                                </div>

                                <!-- Datos de la reserva -->
                                <h5 class="mt-1">Datos de la Reserva</h5>

                                <div class="col-md-6">
                                    <label>Habitación:</label>
                                    <select name="habitacion_id" class="form-select" required>
                                        <option value="">Seleccione una habitación</option>
                                        <?php
                                        $habitaciones = $pdo->query("SELECT id, nombre FROM aloj_habitaciones ORDER BY nombre")->fetchAll();
                                        foreach ($habitaciones as $h) {
                                            echo "<option value='{$h['id']}'>" . strtoupper($h['nombre']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label>Rango de fechas:</label>
                                    <input type="text" name="rango_fechas" id="rango_fechas" class="form-control" required>
                                </div>

                                <div class="col-md-3">
                                    <label>Valor total:</label>
                                    <input type="text" name="valor_total" class="form-control" required>
                                </div>

                                <div class="col-md-3">
                                    <label>Huespedes:</label>
                                    <input type="number" name="cantidad_personas" id="cantidad_personas" class="form-control" min="1" readonly required>
                                </div>

                                

                                <div class="col-md-3">
                                    <label for="total_noches" class="form-label">Total de Noches</label>
                                    <input type="number" class="form-control" id="total_noches" name="total_noches" readonly>
                                </div>

                                <input type="hidden" name="estado" value="pendiente">

                                <!-- Plantilla oculta para clonar -->
                                <div class="acompanante row d-none" id="plantilla-acompanante">
                                    <div class="col-md-4 mt-2">
                                        <input type="text" name="acompanantes[nombre][]" class="form-control" placeholder="Nombre del acompañante" onkeyup="javascript:this.value=this.value.toUpperCase();"required>
                                    </div>
                                    <div class="col-md-3 mt-2">
                                        <input type="text" name="acompanantes[documento][]" class="form-control" placeholder="Documento" required>
                                    </div>
                                    <div class="col-md-3 mt-2">
                                        <input type="text" name="acompanantes[parentesco][]" class="form-control" placeholder="Parentesco" onkeyup="javascript:this.value=this.value.toUpperCase();"required>
                                    </div>
                                    <div class="col-md-2 mt-2">
                                        <button type="button" class="btn btn-danger btn-remove-acompanante w-100">Eliminar</button>
                                    </div>

                                </div>
                                <div class="col col-md-12 col-xl-12 mt-3">
                                    <button type="button" class="btn btn-success mt-2" id="btnAgregarAcompanante">
                                        + Agregar Acompañante
                                    </button>
                                </div>
                                <div id="acompanantes-container"></div>                                
                                
                                <button type="button" class="btn btn-secondary" onclick="mostrarModal()">Revisar y Confirmar</button>
                                
                            </form>
                        </div>
                        <!-- Modal de Confirmación -->
                            <div class="modal fade" id="modalConfirmacion" tabindex="-1" aria-labelledby="modalConfirmacionLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content border-primary">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title" id="modalConfirmacionLabel">Confirmar Reserva</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Nombre:</strong> <span id="confirmNombre"></span></p>
                                            <p><strong>Cantidad de personas:</strong> <span id="confirmPersonas"></span></p>
                                            <p><strong>Fechas:</strong> <span id="confirmFechas"></span></p>
                                            <p><strong>Noches a pagar:</strong> <span id="confirmNoches"></span></p>
                                            <p><strong>Valor total:</strong> $<span id="confirmValor"></span></p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-success" id="btnConfirmarFinal">Confirmar y Guardar</button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <!-- Modal de Confirmación -->                        
                    </div>
                </div>
                <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
                <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        const container = document.getElementById("acompanantes-container");
                        const plantilla = document.getElementById("plantilla-acompanante");
                        const btnAgregar = document.getElementById("btnAgregarAcompanante");
                        const cantidadInput = document.getElementById("cantidad_personas");

                        if (!container || !plantilla || !btnAgregar || !cantidadInput) {
                            console.error("❌ Uno de los elementos del DOM no se encontró. Revisa los IDs.");
                            return;
                        }

                        function actualizarCantidadPersonas() {
                            const totalAcompanantes = container.querySelectorAll(".acompanante").length;
                            cantidadInput.value = 1 + totalAcompanantes;
                        }

                        btnAgregar.addEventListener("click", function() {
                            const nuevaFila = plantilla.cloneNode(true);
                            nuevaFila.classList.remove("d-none");
                            nuevaFila.removeAttribute("id");
                            nuevaFila.querySelectorAll("input").forEach(input => input.value = "");
                            container.appendChild(nuevaFila);
                            actualizarCantidadPersonas();
                        });

                        container.addEventListener("click", function(e) {
                            if (e.target.classList.contains("btn-remove-acompanante")) {
                                const fila = e.target.closest(".acompanante");
                                fila.remove();
                                actualizarCantidadPersonas();
                            }
                        });

                        actualizarCantidadPersonas();
                    });
                </script>

                <script>
                    function mostrarModal() {
                        const nombre = document.querySelector('[name="nombre"]').value.trim();
                        const personas = parseInt(document.querySelector('[name="cantidad_personas"]').value);
                        const fechas = document.querySelector('[name="rango_fechas"]').value;
                        const valor = parseFloat(document.querySelector('[name="valor_total"]').value);

                        if (!nombre || !fechas || !valor || isNaN(personas) || personas <= 0) {
                            alert("Por favor completa todos los datos correctamente.");
                            return;
                        }

                        // Calcular noches
                        const [inicio, fin] = fechas.split(" / ");
                        const fechaInicio = new Date(inicio);
                        const fechaFin = new Date(fin);
                        const diferencia = (fechaFin - fechaInicio) / (1000 * 60 * 60 * 24);
                        if (diferencia < 1) {
                            alert("El rango de fechas debe ser al menos 1 noche.");
                            return;
                        }

                        // Mostrar en el modal
                        document.getElementById("confirmNombre").textContent = nombre;
                        document.getElementById("confirmPersonas").textContent = personas;
                        document.getElementById("confirmFechas").textContent = fechas;
                        document.getElementById("confirmNoches").textContent = diferencia;
                        document.getElementById("confirmValor").textContent = valor.toLocaleString('es-CO');

                        // Mostrar modal
                        const modal = new bootstrap.Modal(document.getElementById('modalConfirmacion'));
                        modal.show();

                        // Vincular botón de confirmación final al submit
                        document.getElementById("btnConfirmarFinal").onclick = function() {
                            document.getElementById("formClientes").submit();
                        };
                    }
                </script>


                <script>
                     // Función para obtener parámetros de la URL
  function obtenerParametroURL(nombre) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(nombre);
  }

  $(function () {
    const fechaDesdeURL = obtenerParametroURL('fecha');
    let fechaInicio = moment(); // por defecto: hoy
    let fechaFin = moment().add(1, 'days'); // por defecto: mañana

    // Si viene la fecha desde el calendario, usarla
    if (fechaDesdeURL) {
      fechaInicio = moment(fechaDesdeURL);
      fechaFin = moment(fechaDesdeURL).add(1, 'days');

      // Establecer automáticamente el total de noches
      const diferencia = fechaFin.diff(fechaInicio, 'days');
      $('#total_noches').val(diferencia);
    }

    $('#rango_fechas').daterangepicker({
      startDate: fechaInicio,
      endDate: fechaFin,
      locale: {
        format: 'YYYY-MM-DD',
        separator: ' / ',
        applyLabel: 'Aplicar',
        cancelLabel: 'Cancelar',
        fromLabel: 'Desde',
        toLabel: 'Hasta',
        customRangeLabel: 'Personalizado',
        daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
        monthNames: [
          'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
          'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ],
        firstDay: 1
      }
    }, function (start, end, label) {
      const fechaInicio = new Date(start.format('YYYY-MM-DD'));
      const fechaFin = new Date(end.format('YYYY-MM-DD'));
      const diferencia = (fechaFin - fechaInicio) / (1000 * 60 * 60 * 24);

      if (diferencia < 1) {
        alert("El rango de fechas debe ser al menos 1 noche.");
        $('#rango_fechas').val('');
        $('#total_noches').val('');
        return;
      }

      $('#total_noches').val(diferencia);
    });
  });
                </script>
                <script>
                    document.querySelectorAll('input[type="text"]').forEach(input => {
                        input.addEventListener('input', () => {
                            input.value = input.value.toUpperCase();
                        });
                    });
                </script>
                <script>
                    function validarDocumento(input) {
                        const valor = input.value;
                        const error = document.getElementById('documentoError');

                        // Limpiar caracteres no numéricos
                        input.value = valor.replace(/\D/g, '');

                        // Validaciones
                        if (/\D/.test(valor)) {
                            error.textContent = "❌ Solo se permiten números.";
                            return false;
                        } else if (input.value.length < 5) {
                            error.textContent = "⚠️ El documento debe tener al menos 5 dígitos.";
                            return false;
                        } else if (input.value.length > 10) {
                            error.textContent = "⚠️ El documento no puede tener más de 10 dígitos.";
                            return false;
                        } else {
                            error.textContent = "";
                            return true;
                        }
                    }

                    // Bloqueo del formulario si el documento es inválido
                    document.getElementById("formClientes").addEventListener("submit", function(e) {
                        const documentoInput = document.getElementById("documento");
                        const valido = validarDocumento(documentoInput);
                        if (!valido) {
                            e.preventDefault(); // Evita el envío
                            documentoInput.focus();
                        }
                    });

                    function formatearTelefono(input) {
                        // Quitar todo lo que no sea número
                        let valor = input.value.replace(/\D/g, '');

                        // Limitar a 10 dígitos
                        if (valor.length > 10) valor = valor.slice(0, 10);

                        // Aplicar formato xxx-xxxxxxx
                        let formato = valor;
                        if (valor.length > 3) {
                            formato = valor.slice(0, 3) + '-' + valor.slice(3);
                        }

                        input.value = formato;

                        // Mostrar errores
                        const error = document.getElementById('telefonoError');
                        if (valor.length < 10) {
                            error.textContent = "⚠️ El número debe tener exactamente 10 dígitos.";
                        } else {
                            error.textContent = "";
                        }
                    }

                    document.getElementById("formClientes").addEventListener("submit", function(e) {
                        const documentoInput = document.getElementById("documento");
                        const telefonoInput = document.getElementById("telefono");

                        const docOk = validarDocumento(documentoInput);
                        const telOk = validarTelefono(telefonoInput);

                        if (!docOk || !telOk) {
                            e.preventDefault();
                        }
                    });

                    function validarTelefono(input) {
                        const valor = input.value.replace(/\D/g, ''); // Solo números
                        const error = document.getElementById('telefonoError');

                        if (valor.length !== 10) {
                            error.textContent = "⚠️ El número debe tener exactamente 10 dígitos.";
                            return false;
                        }

                        error.textContent = "";
                        return true;
                    }
                </script>

                <!-- Select2 JS -->
                <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
                <script>
                    $(document).ready(function() {
                        $(document).ready(function() {
                            fetch("../aloj/data/municipios_colombia.json")
                                .then(response => response.json())
                                .then(data => {
                                    const $select = $('#procedencia');
                                    $select.empty().append('<option value="">Seleccione un municipio</option>');

                                    data.forEach(municipio => {
                                        $select.append(new Option(municipio, municipio));
                                    });

                                    $select.select2({
                                        placeholder: "Seleccione un municipio",
                                        allowClear: true,
                                        width: '100%'
                                    });
                                })
                                .catch(error => {
                                    $('#procedencia').html('<option value="">Error al cargar municipios</option>');
                                });
                        });
                    });

                    function validarProcedencia() {
                        const select = document.getElementById("procedencia");
                        const error = document.getElementById("procedenciaError");

                        if (!select.value) {
                            error.textContent = "⚠️ Debes seleccionar una procedencia.";
                            return false;
                        }

                        error.textContent = "";
                        return true;
                    }

                    document.getElementById("formClientes").addEventListener("submit", function(e) {
                        const procOK = validarProcedencia();
                        const telOK = validarTelefono(document.getElementById("telefono"));
                        const docOK = validarDocumento(document.getElementById("documento"));

                        if (!procOK || !telOK || !docOK) {
                            e.preventDefault();
                        }
                    });

                    function formatearPlaca(input) {
                        let valor = input.value.toUpperCase().replace(/[^A-Z0-9]/g, '');

                        // Separamos lo primero (letras) y lo segundo (alfa)
                        let letras = valor.substring(0, 3).replace(/[^A-Z]/g, '');
                        let resto = valor.substring(3, 6).replace(/[^A-Z0-9]/g, '');

                        // Armamos formato visual
                        input.value = letras + (resto ? '-' + resto : '');

                        // Validación
                        const error = document.getElementById('placaError');
                        if (letras.length !== 3 || resto.length !== 3) {
                            error.textContent = "⚠️ Formato inválido. Ej: AAA-111 o AAA-11A.";
                        } else {
                            error.textContent = "";
                        }
                    }

                    function validarPlaca() {
                        const input = document.getElementById("placa_vehiculo");
                        const error = document.getElementById("placaError");
                        const valor = input.value.toUpperCase().replace(/[^A-Z0-9]/g, '');

                        const letras = valor.substring(0, 3);
                        const resto = valor.substring(3, 6);

                        if (!/^[A-Z]{3}$/.test(letras) || resto.length !== 3) {
                            error.textContent = "⚠️ La placa debe tener 3 letras seguidas de 3 letras o números.";
                            return false;
                        }

                        error.textContent = "";
                        return true;
                    }
                </script>



        </body>


    </main>
</div>

</html>