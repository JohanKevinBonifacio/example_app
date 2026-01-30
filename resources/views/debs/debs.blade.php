<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deudas - Vista Web</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="container py-4">
        <h1 class="mb-4">📊 Gestión de Deudas</h1>
        
        <!-- Botón de Exportación -->
        <div class="mb-4">
            <button class="btn btn-success" onclick="exportExcel()" id="exportBtn">
                📥 Exportar XLSX
            </button>
            <div class="mt-2">
                <small class="text-muted">Exporta todos los datos a Excel (XLSX)</small>
            </div>
        </div>
        
        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-body">
                <form id="filterForm" class="row g-3">
                    <!-- Filtros de Fecha para Exportación -->
                    <div class="col-md-3">
                        <label class="form-label">Fecha Inicio</label>
                        <input type="date" id="startDate" class="form-control" 
                               value="{{ date('Y-m-d', strtotime('-30 days')) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha Fin</label>
                        <input type="date" id="endDate" class="form-control" 
                               value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Situación</label>
                        <select name="situation" class="form-select">
                            <option value="">Todas</option>
                            <option value="NOR">NOR</option>
                            <option value="CPP">CPP</option>
                            <option value="DEF">DEF</option>
                            <option value="PER">PER</option>
                        </select>
                    </div>
                    
                    <div class="col-md-12 mt-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Filtrar Tabla</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="resetFilters()">Limpiar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Tabla -->
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">Lista de Deudas</h5>
                <button class="btn btn-sm btn-primary" onclick="loadData()">Actualizar</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="debsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>DNI</th>
                                <th>Monto</th>
                                <th>Días</th>
                                <th>Situación</th>
                                <th>Fecha</th>
                                <th>Tipo</th>
                            </tr>
                        </thead>
                        <tbody id="debsBody">
                            <!-- Cargado por JS -->
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginación -->
                <nav>
                    <ul class="pagination justify-content-center" id="pagination"></ul>
                </nav>
            </div>
        </div>
    </div>

    <script>
        let currentPage = 1;
        let currentFilters = {};
        
        document.addEventListener('DOMContentLoaded', function() {
            loadData();
            
            // Manejar filtros de tabla
            document.getElementById('filterForm').addEventListener('submit', function(e) {
                e.preventDefault();
                currentPage = 1;
                currentFilters = {
                    min_amount: this.min_amount.value,
                    situation: this.situation.value
                };
                loadData();
            });
        });
        
        // Función para exportar a Excel
        async function exportExcel() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            const btn = document.getElementById('exportBtn');
            
            if (!startDate || !endDate) {
                alert('Seleccione ambas fechas para exportar');
                return;
            }
            
            // Validar que fecha fin sea mayor o igual
            if (new Date(endDate) < new Date(startDate)) {
                alert('La fecha fin debe ser mayor o igual a la fecha inicio');
                return;
            }
            
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Generando...';
            
            try {
                // Crear formulario dinámico
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/exportar-excel';
                
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = document.querySelector('meta[name="csrf-token"]').content;
                
                const startInput = document.createElement('input');
                startInput.type = 'hidden';
                startInput.name = 'start_date';
                startInput.value = startDate;
                
                const endInput = document.createElement('input');
                endInput.type = 'hidden';
                endInput.name = 'end_date';
                endInput.value = endDate;
                
                form.appendChild(csrf);
                form.appendChild(startInput);
                form.appendChild(endInput);
                document.body.appendChild(form);
                
                // Enviar formulario
                form.submit();
                
                // Restaurar botón después de 3 segundos
                setTimeout(() => {
                    btn.disabled = false;
                    btn.innerHTML = '📥 Exportar XLSX';
                }, 3000);
                
            } catch (error) {
                console.error('Error al exportar:', error);
                alert('Error al exportar el archivo');
                btn.disabled = false;
                btn.innerHTML = '📥 Exportar XLSX';
            }
        }
        
        function loadData(page = 1) {
            currentPage = page;
            
            // Construir URL con filtros
            let url = `/api/debs/filtered?page=${page}`;
            if (currentFilters.min_amount) {
                url += `&min_amount=${currentFilters.min_amount}`;
            }
            if (currentFilters.situation) {
                url += `&situation=${currentFilters.situation}`;
            }
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    updateTable(data.data);
                    updatePagination(data.pagination);
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar datos');
                });
        }
        
        function resetFilters() {
            document.getElementById('filterForm').reset();
            currentFilters = {};
            currentPage = 1;
            loadData();
        }
        
        function updateTable(deudas) {
            const tbody = document.getElementById('debsBody');
            tbody.innerHTML = '';
            
            if (deudas.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            No hay registros con los filtros aplicados
                        </td>
                    </tr>
                `;
                return;
            }
            
            deudas.forEach(deuda => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${deuda.id}</td>
                    <td>${deuda.nombre_completo}</td>
                    <td>${deuda.dni}</td>
                    <td>$${parseFloat(deuda.monto_deuda).toLocaleString('es-ES', {minimumFractionDigits: 2})}</td>
                    <td><span class="badge ${deuda.dias_vencimiento > 30 ? 'bg-danger' : 'bg-warning'}">${deuda.dias_vencimiento} días</span></td>
                    <td><span class="badge bg-secondary">${deuda.situacion}</span></td>
                    <td>${new Date(deuda.fecha_creacion).toLocaleDateString('es-ES')}</td>
                    <td>${deuda.tipo_registro || 'N/A'}</td>
                `;
                tbody.appendChild(tr);
            });
        }
        
        function updatePagination(pagination) {
            const ul = document.getElementById('pagination');
            ul.innerHTML = '';
            
            if (pagination.last_page <= 1) return;
            
            // Botón anterior
            if (currentPage > 1) {
                const li = document.createElement('li');
                li.className = 'page-item';
                li.innerHTML = `<a class="page-link" href="#" onclick="loadData(${currentPage - 1})">Anterior</a>`;
                ul.appendChild(li);
            }
            
            // Números de página
            for (let i = 1; i <= pagination.last_page; i++) {
                const li = document.createElement('li');
                li.className = `page-item ${i === currentPage ? 'active' : ''}`;
                li.innerHTML = `<a class="page-link" href="#" onclick="loadData(${i})">${i}</a>`;
                ul.appendChild(li);
            }
            
            // Botón siguiente
            if (currentPage < pagination.last_page) {
                const li = document.createElement('li');
                li.className = 'page-item';
                li.innerHTML = `<a class="page-link" href="#" onclick="loadData(${currentPage + 1})">Siguiente</a>`;
                ul.appendChild(li);
            }
        }
    </script>
</body>
</html>