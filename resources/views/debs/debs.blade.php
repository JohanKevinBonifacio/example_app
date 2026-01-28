<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Deudas</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Añade SheetJS aquí -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

</head>
<body>
    <div class="container-fluid mt-3">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="mb-0">📊 Gestión de Deudas</h3>
                <p class="text-muted">Administra y consulta las deudas de clientes</p>
            </div>
        </div>
        
        <!-- Filtros -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Buscar por Cliente:</label>
                                <input type="text" class="form-control" id="filtroNombre" 
                                       placeholder="Nombre, DNI, email o teléfono">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Fecha Desde:</label>
                                <input type="date" class="form-control" id="filtroFechaDesde">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Fecha Hasta:</label>
                                <input type="date" class="form-control" id="filtroFechaHasta">
                            </div>
                            <div class="col-md-2 d-flex align-items-end gap-2">
                                <button class="btn btn-primary w-50" onclick="aplicarFiltros()">
                                    <i class="fas fa-filter"></i> Filtrar
                                </button>
                                <button class="btn btn-outline-secondary w-50" onclick="limpiarFiltros()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tabla -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Lista de Deudas</h5>
                        <div>
                            <button class="btn btn-success btn-sm me-2" onclick="exportarExcel()">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                            <button class="btn btn-outline-primary btn-sm" onclick="cargarDatos()">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="alert-container"></div>
                        <div class="table-responsive">
                            <table id="tablaDeudas" class="table table-hover table-striped" style="width:100%">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>DNI</th>
                                        <th>Contacto</th>
                                        <th>Fecha Suscripción</th>
                                        <th>Fecha Registro</th>
                                        <th>Tipo</th>
                                        <th>Monto</th>
                                        <th>Días</th>
                                        <th>Situación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Los datos se cargan con JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Resumen -->
        <div class="row mt-4">
            <div class="col-md-3 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h6 class="card-title">Total Deuda</h6>
                        <h4 id="totalDeuda" class="card-text">$0.00</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h6 class="card-title">Clientes</h6>
                        <h4 id="totalClientes" class="card-text">0</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <h6 class="card-title">Vencidas</h6>
                        <h4 id="totalVencidas" class="card-text">0</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h6 class="card-title">Registro Reciente</h6>
                        <h6 id="fechaReciente" class="card-text">-</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        let tablaDeudas = null;
        let datosOriginales = [];
        
        // Configuración de idioma ES
        const spanishLanguage = {
            "processing": "Procesando...",
            "lengthMenu": "Mostrar _MENU_ registros",
            "zeroRecords": "No se encontraron resultados",
            "emptyTable": "Ningún dato disponible",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "infoEmpty": "Mostrando 0 a 0 de 0 registros",
            "infoFiltered": "(filtrado de _MAX_ registros totales)",
            "search": "Buscar:",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        };
        
        $(document).ready(function() {
            // Configurar fechas por defecto: 20 de enero hasta hoy
            const hoy = new Date();
            const fechaInicio = new Date('2024-01-20'); // 20 de enero
            
            // Si la fecha de inicio es mayor que hoy, usar hace 30 días
            let fechaDesde = fechaInicio;
            if (fechaInicio > hoy) {
                fechaDesde = new Date();
                fechaDesde.setDate(hoy.getDate() - 30);
            }
            
            $('#filtroFechaDesde').val(fechaDesde.toISOString().split('T')[0]);
            $('#filtroFechaHasta').val(hoy.toISOString().split('T')[0]);
            
            // Inicializar DataTable
            inicializarDataTable();
            
            // Cargar datos
            cargarDatos();
            
            // Permitir filtrar con Enter
            $('#filtroNombre').keypress(function(e) {
                if(e.which === 13) aplicarFiltros();
            });
        });
        
        function inicializarDataTable() {
            tablaDeudas = $('#tablaDeudas').DataTable({
                language: spanishLanguage,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                order: [[0, 'desc']]
            });
        }
        
        function cargarDatos() {
            mostrarAlerta('info', '<i class="fas fa-spinner fa-spin"></i> Cargando datos...', false);
            
            fetch('/api/debs')
                .then(response => response.json())
                .then(data => {
                    datosOriginales = data;
                    actualizarResumen(data);
                    
                    if (data.length === 0) {
                        mostrarAlerta('warning', 'No hay registros de deudas disponibles.');
                        return;
                    }
                    
                    // Limpiar tabla
                    tablaDeudas.clear();
                    
                    // Agregar datos
                    data.forEach(cliente => {
                        // Determinar color según situación
                        let situacionColor = cliente.situacion === 'Vencido' ? 'danger' : 'success';
                        
                        // Formatear fecha de suscripción
                        let fechaSuscripcion = 'N/A';
                        if (cliente.fecha_suscripcion_real) {
                            const fecha = new Date(cliente.fecha_suscripcion_real);
                            fechaSuscripcion = fecha.toLocaleDateString('es-ES');
                        }
                        
                        // Formatear fecha de creación (registro)
                        let fechaRegistro = 'N/A';
                        if (cliente.fecha_creacion) {
                            const fecha = new Date(cliente.fecha_creacion);
                            fechaRegistro = fecha.toLocaleDateString('es-ES');
                        }
                        
                        // Determinar color de días
                        let diasColor = 'success';
                        let diasTexto = cliente.dias_vencimiento;
                        if (cliente.dias_vencimiento <= 0) {
                            diasColor = 'danger';
                            diasTexto = `<strong>VENCIDO (${Math.abs(cliente.dias_vencimiento)})</strong>`;
                        } else if (cliente.dias_vencimiento <= 7) {
                            diasColor = 'warning';
                        }
                        
                        // Agregar fila
                        tablaDeudas.row.add([
                            cliente.id,
                            `<strong>${cliente.nombre_completo || 'Sin nombre'}</strong>`,
                            cliente.dni || 'N/A',
                            `<div><small><i class="fas fa-phone text-primary"></i> ${cliente.telefono || 'N/A'}</small></div>
                             <div><small><i class="fas fa-envelope text-success"></i> ${cliente.email || 'N/A'}</small></div>`,
                            `<span class="badge bg-secondary">${fechaSuscripcion}</span>`,
                            `<span class="badge bg-dark">${fechaRegistro}</span>`,
                            `<span class="badge bg-info">${cliente.tipo_registro || 'N/A'}</span>`,
                            `<strong class="text-primary">$${parseFloat(cliente.monto_deuda || 0).toFixed(2)}</strong>`,
                            `<span class="badge bg-${diasColor}">${diasTexto} días</span>`,
                            `<span class="badge bg-${situacionColor}">${cliente.situacion || 'N/A'}</span>`
                        ]);
                    });
                    
                    tablaDeudas.draw();
                    mostrarAlerta('success', `Cargados ${data.length} registros`, true);
                })
                .catch(error => {
                    console.error('Error:', error);
                    mostrarAlerta('danger', 'Error al cargar los datos');
                });
        }
        
        function aplicarFiltros() {
            const filtroNombre = $('#filtroNombre').val().toLowerCase().trim();
            const fechaDesde = $('#filtroFechaDesde').val();
            const fechaHasta = $('#filtroFechaHasta').val();
            
            // Validar fechas
            if (fechaDesde && fechaHasta && new Date(fechaDesde) > new Date(fechaHasta)) {
                mostrarAlerta('warning', 'Fecha "Desde" no puede ser mayor que "Hasta"');
                return;
            }
            
            let datosFiltrados = datosOriginales;
            
            // Filtro por nombre
            if (filtroNombre) {
                datosFiltrados = datosFiltrados.filter(cliente => {
                    const nombre = (cliente.nombre_completo || '').toLowerCase();
                    const dni = (cliente.dni || '').toLowerCase();
                    const email = (cliente.email || '').toLowerCase();
                    const telefono = (cliente.telefono || '').toLowerCase();
                    
                    return nombre.includes(filtroNombre) || 
                           dni.includes(filtroNombre) ||
                           email.includes(filtroNombre) ||
                           telefono.includes(filtroNombre);
                });
            }
            
            // Filtro por FECHA DE CREACIÓN (fecha_creacion)
            if (fechaDesde || fechaHasta) {
                const desde = fechaDesde ? new Date(fechaDesde) : null;
                const hasta = fechaHasta ? new Date(fechaHasta) : null;
                
                if (desde) desde.setHours(0, 0, 0, 0);
                if (hasta) hasta.setHours(23, 59, 59, 999);
                
                datosFiltrados = datosFiltrados.filter(cliente => {
                    // Usar fecha_creacion para el filtro
                    if (!cliente.fecha_creacion) return false;
                    
                    const fechaCreacion = new Date(cliente.fecha_creacion);
                    
                    if (desde && fechaCreacion < desde) return false;
                    if (hasta && fechaCreacion > hasta) return false;
                    
                    return true;
                });
            }
            
            // Actualizar tabla
            actualizarTablaFiltrada(datosFiltrados);
        }
        
        function actualizarTablaFiltrada(datosFiltrados) {
            tablaDeudas.clear();
            
            if (datosFiltrados.length === 0) {
                mostrarAlerta('warning', 'No se encontraron registros con los filtros aplicados.');
                actualizarResumen([]);
                return;
            }
            
            // Volver a agregar datos filtrados
            datosFiltrados.forEach(cliente => {
                let situacionColor = cliente.situacion === 'Vencido' ? 'danger' : 'success';
                
                // Formatear fechas
                let fechaSuscripcion = 'N/A';
                if (cliente.fecha_suscripcion_real) {
                    const fecha = new Date(cliente.fecha_suscripcion_real);
                    fechaSuscripcion = fecha.toLocaleDateString('es-ES');
                }
                
                let fechaRegistro = 'N/A';
                if (cliente.fecha_creacion) {
                    const fecha = new Date(cliente.fecha_creacion);
                    fechaRegistro = fecha.toLocaleDateString('es-ES');
                }
                
                // Determinar color de días
                let diasColor = 'success';
                let diasTexto = cliente.dias_vencimiento;
                if (cliente.dias_vencimiento <= 0) {
                    diasColor = 'danger';
                    diasTexto = `<strong>VENCIDO (${Math.abs(cliente.dias_vencimiento)})</strong>`;
                } else if (cliente.dias_vencimiento <= 7) {
                    diasColor = 'warning';
                }
                
                tablaDeudas.row.add([
                    cliente.id,
                    `<strong>${cliente.nombre_completo || 'Sin nombre'}</strong>`,
                    cliente.dni || 'N/A',
                    `<div><small><i class="fas fa-phone text-primary"></i> ${cliente.telefono || 'N/A'}</small></div>
                     <div><small><i class="fas fa-envelope text-success"></i> ${cliente.email || 'N/A'}</small></div>`,
                    `<span class="badge bg-secondary">${fechaSuscripcion}</span>`,
                    `<span class="badge bg-dark">${fechaRegistro}</span>`,
                    `<span class="badge bg-info">${cliente.tipo_registro || 'N/A'}</span>`,
                    `<strong class="text-primary">$${parseFloat(cliente.monto_deuda || 0).toFixed(2)}</strong>`,
                    `<span class="badge bg-${diasColor}">${diasTexto} días</span>`,
                    `<span class="badge bg-${situacionColor}">${cliente.situacion || 'N/A'}</span>`
                ]);
            });
            
            tablaDeudas.draw();
            actualizarResumen(datosFiltrados);
            mostrarAlerta('info', `Mostrando ${datosFiltrados.length} de ${datosOriginales.length} registros`, true);
        }
        
        function limpiarFiltros() {
            $('#filtroNombre').val('');
            
            // Restablecer a 20 de enero hasta hoy
            const hoy = new Date();
            const fechaInicio = new Date('2024-01-20');
            
            let fechaDesde = fechaInicio;
            if (fechaInicio > hoy) {
                fechaDesde = new Date();
                fechaDesde.setDate(hoy.getDate() - 30);
            }
            
            $('#filtroFechaDesde').val(fechaDesde.toISOString().split('T')[0]);
            $('#filtroFechaHasta').val(hoy.toISOString().split('T')[0]);
            
            actualizarTablaFiltrada(datosOriginales);
            mostrarAlerta('info', 'Filtros limpiados', true);
        }
        
        function actualizarResumen(data) {
            if (!data || data.length === 0) {
                $('#totalDeuda').text('$0.00');
                $('#totalClientes').text('0');
                $('#totalVencidas').text('0');
                $('#fechaReciente').text('-');
                return;
            }
            
            // Calcular total deuda
            const totalDeuda = data.reduce((sum, cliente) => 
                sum + parseFloat(cliente.monto_deuda || 0), 0);
            
            // Contar vencidas
            const vencidas = data.filter(cliente => 
                parseInt(cliente.dias_vencimiento || 0) <= 0).length;
            
            // Encontrar fecha de registro más reciente
            let fechaReciente = null;
            data.forEach(cliente => {
                if (cliente.fecha_creacion) {
                    const fecha = new Date(cliente.fecha_creacion);
                    if (!fechaReciente || fecha > fechaReciente) {
                        fechaReciente = fecha;
                    }
                }
            });
            
            // Actualizar UI
            $('#totalDeuda').text('$' + totalDeuda.toLocaleString('es-ES', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            
            $('#totalClientes').text(data.length);
            $('#totalVencidas').text(vencidas);
            
            if (fechaReciente) {
                const opciones = { 
                    day: 'numeric', 
                    month: 'short',
                    year: 'numeric'
                };
                $('#fechaReciente').text(fechaReciente.toLocaleDateString('es-ES', opciones));
            } else {
                $('#fechaReciente').text('-');
            }
        }
        

function exportarExcel() {
    if (datosOriginales.length === 0) {
        mostrarAlerta('warning', 'No hay datos para exportar');
        return;
    }
    
    // Verificar si SheetJS está disponible
    if (typeof XLSX === 'undefined') {
        mostrarAlerta('danger', 'Error: La biblioteca SheetJS no está cargada.');
        return;
    }
    
    // Crear array de datos en formato de COLUMNAS con cabeceras
    const datosExcel = [];
    
    // Primera fila: CABECERAS DE COLUMNAS
    datosExcel.push([
        'Identificador del reporte',
        'Nombre del suscriptor', 
        'Documento de identidad',
        'Correo electrónico',
        'Número de contacto',
        'Banco o entidad asociada a la deuda',
        'Préstamo, Tarjeta de crédito u Otra deuda',
        'Estado del crédito (NOR, CPP, DEF, PER)',
        'Días de vencimiento',
        'Entidad financiera o comercial',
        'Monto de la deuda',
        'Línea de crédito aprobada (aplica para tarjetas)',
        'Línea de crédito utilizada (aplica para tarjetas)',
        'Fecha de creación del reporte',
        'Estado general del registro'
    ]);
    
    // Agregar los datos de cada cliente como FILAS
    datosOriginales.forEach(cliente => {
        // Formatear fecha de registro
        let fechaRegistro = '';
        if (cliente.fecha_creacion) {
            const fecha = new Date();
            fechaRegistro = fecha.toLocaleDateString('es-ES');
        }
        
        // Determinar tipo de deuda
        let tipoDeuda = 'Otra deuda';
        if (cliente.tipo_registro) {
            const tipo = cliente.tipo_registro.toLowerCase();
            if (tipo.includes('préstamo') || tipo.includes('prestamo')) tipoDeuda = 'Préstamo';
            else if (tipo.includes('tarjeta') || tipo.includes('crédito')) tipoDeuda = 'Tarjeta de crédito';
        }
        
        // Mapear situación
        let situacion = cliente.situacion || '';
        if (situacion.includes('Normal') || situacion.includes('NOR')) situacion = 'NOR';
        else if (situacion.includes('CPP')) situacion = 'CPP';
        else if (situacion.includes('Def') || situacion.includes('DEF')) situacion = 'DEF';
        else if (situacion.includes('Per') || situacion.includes('PER')) situacion = 'PER';
        
        // Determinar estado
        let estadoGeneral = 'Activo';
        if (cliente.dias_vencimiento <= 0) estadoGeneral = 'Vencido';
        else if (cliente.dias_vencimiento <= 7) estadoGeneral = 'Por vencer';
        
        // Crear FILA con los datos del cliente
        datosExcel.push([
            cliente.id || '',
            cliente.nombre_completo || '',
            cliente.dni || '',
            cliente.email || '',
            cliente.telefono || '',
            cliente.entidad || '',
            tipoDeuda,
            situacion,
            cliente.dias_vencimiento || 0,
            cliente.entidad || '',
            cliente.monto_deuda || 0,
            cliente.linea_credito_aprobada || '',
            cliente.linea_credito_utilizada || '',
            fechaRegistro,
            estadoGeneral
        ]);
    });
    
    // Crear hoja de cálculo con los datos en formato de tabla
    const ws = XLSX.utils.aoa_to_sheet(datosExcel);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Deudas");
    
    // Ajustar anchos de columnas
    const wscols = [
        { wch: 8 },   // ID
        { wch: 25 },  // Nombre Completo
        { wch: 12 },  // DNI
        { wch: 25 },  // Email
        { wch: 15 },  // Teléfono
        { wch: 20 },  // Compañía
        { wch: 18 },  // Tipo de deuda
        { wch: 10 },  // Situación
        { wch: 8 },   // Atraso
        { wch: 20 },  // Entidad
        { wch: 12 },  // Monto total
        { wch: 12 },  // Línea total
        { wch: 12 },  // Línea usada
        { wch: 15 },  // Reporte subido el
        { wch: 10 }   // Estado
    ];
    ws['!cols'] = wscols;
    
    // Aplicar formato a los montos (columnas K, L, M - índice 10, 11, 12)
    const range = XLSX.utils.decode_range(ws['!ref']);
    for (let R = 1; R <= range.e.r; R++) {
        // Columna Monto total (columna K, índice 10)
        const cellMonto = XLSX.utils.encode_cell({ r: R, c: 10 });
        if (ws[cellMonto] && ws[cellMonto].v) {
            ws[cellMonto].t = 'n'; // Tipo número
            ws[cellMonto].z = '#,##0.00'; // Formato con separador de miles
        }
        
        // Columna Línea total (columna L, índice 11)
        const cellLineaTotal = XLSX.utils.encode_cell({ r: R, c: 11 });
        if (ws[cellLineaTotal] && ws[cellLineaTotal].v) {
            ws[cellLineaTotal].t = 'n';
            ws[cellLineaTotal].z = '#,##0.00';
        }
        
        // Columna Línea usada (columna M, índice 12)
        const cellLineaUsada = XLSX.utils.encode_cell({ r: R, c: 12 });
        if (ws[cellLineaUsada] && ws[cellLineaUsada].v) {
            ws[cellLineaUsada].t = 'n';
            ws[cellLineaUsada].z = '#,##0.00';
        }
    }
    
    // Generar archivo
    XLSX.writeFile(wb, `deudas_formato_${new Date().toISOString().split('T')[0]}.xlsx`);
    
    mostrarAlerta('success', `Archivo Excel generado con ${datosOriginales.length} registros`, true);
}
        
        function mostrarAlerta(tipo, mensaje, autoOcultar = true) {
            const alertContainer = $('#alert-container');
            alertContainer.empty();
            
            const alertId = 'alert-' + Date.now();
            const alerta = `
                <div id="${alertId}" class="alert alert-${tipo} alert-dismissible fade show" role="alert">
                    ${mensaje}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            alertContainer.html(alerta);
            
            if (autoOcultar) {
                setTimeout(() => {
                    $(`#${alertId}`).alert('close');
                }, 3000);
            }
        }
    </script>
</body>
</html>