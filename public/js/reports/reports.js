document.addEventListener('DOMContentLoaded', function () {

    // ======================
    // Modal Subir Reporte
    // ======================
    const modal = document.getElementById('modalReporte');
    const weekLabel = document.getElementById('modal-week-label');
    const inputWeek = document.getElementById('input-week');
    const triggers = document.querySelectorAll('.btn-open-modal');

    triggers.forEach(btn => {
        btn.addEventListener('click', () => {
            const w = btn.getAttribute('data-week');
            weekLabel.textContent = '- ' + w;
            inputWeek.value = w;
        });
    });

    // ======================
    // Modal Ver Detalles
    // ======================
    const modalDetalles = document.getElementById('modalDetalles');
    const weekLabelDetalles = document.getElementById('modal-detalles-week-label');
    const triggersDetalles = document.querySelectorAll('.btn-ver-detalles');
    const dependenciaInput = document.getElementById('modal-dependency-input');
    const dependenciaDataList = document.getElementById('dependencias-list');
    const cdpInput = document.createElement('input');
    cdpInput.id = "modal-cdp-input";
    cdpInput.type = "text";
    cdpInput.placeholder = "Número CDP";
    cdpInput.classList.add('form-control', 'form-control-sm', 'w-auto');

    const btnBuscar = document.getElementById('btn-modal-buscar');
    const tbodyDetalles = document.getElementById('tabla-detalles-body');

    // Añadir input de número CDP al modal
    const headerRow = dependenciaInput.parentNode.parentNode;
    headerRow.appendChild(cdpInput);

    // ======================
    // Cargar dependencias
    // ======================
    let depsCargadas = false;
    const cargarDependencias = async () => {
        if (depsCargadas) return;
        try {
            const resp = await fetch(${BASE_URL}reports/dependencias);
            const data = await resp.json();
            if (Array.isArray(data)) {
                dependenciaDataList.innerHTML = '';
                data.forEach(dep => {
                    const opt = document.createElement('option');
                    opt.value = dep.codigo;
                    opt.label = ${dep.codigo} - ${dep.nombre};
                    opt.textContent = ${dep.codigo} - ${dep.nombre};
                    dependenciaDataList.appendChild(opt);
                });
                depsCargadas = true;
            }
        } catch (e) { console.error(e); }
    };

    // ======================
    // Utilidades
    // ======================
    const limpiarNumero = (valor) => {
        if (!valor) return 0;
        return parseFloat(String(valor).replace(/[^0-9.-]+/g, "")) || 0;
    };

    const formatoMoneda = (valor) => new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 }).format(valor);

    // ======================
    // Charts existentes
    // ======================
    let chartGastos = null;
    let chartPresupuesto = null;
    let chartDependencias = null;

    const disposeChart = (chartRef) => {
        if (chartRef && typeof chartRef.destroy === 'function') chartRef.destroy();
        return null;
    };

    const palette = ['#4e79a7', '#f28e2b', '#e15759', '#76b7b2', '#59a14f', '#edc948', '#b07aa1', '#ff9da7', '#9c755f', '#bab0ab'];

    const aggregateRows = (rows) => {
        const acc = { totalInicial:0, totalSaldo:0, totalComprometido:0, totalOperaciones:0, totalActual:0, porDependencia:new Map() };
        rows.forEach(r => {
            const inicial = limpiarNumero(r.valor_inicial);
            const saldo = limpiarNumero(r.saldo_por_comprometer);
            const operaciones = limpiarNumero(r.valor_operaciones);
            const actual = limpiarNumero(r.valor_actual);
            const comprometido = Math.max(inicial - saldo, 0);
            acc.totalInicial += inicial;
            acc.totalSaldo += saldo;
            acc.totalComprometido += comprometido;
            acc.totalOperaciones += operaciones;
            acc.totalActual += actual;
            const depName = r.dependencia_descripcion || r.dependencia || 'Sin dependencia';
            acc.porDependencia.set(depName, (acc.porDependencia.get(depName) || 0) + comprometido);
        });
        return acc;
    };

    const updateCharts = (rows) => {
        const { totalInicial, totalSaldo, totalComprometido, totalOperaciones, totalActual, porDependencia } = aggregateRows(rows);
        const totalPresEl = document.getElementById('total-presupuesto');
        if (totalPresEl) totalPresEl.textContent = formatoMoneda(totalInicial);

        // Chart 1: Distribución
        chartGastos = disposeChart(chartGastos);
        const canvasGastos = document.getElementById('canvas-gastos');
        if (canvasGastos) chartGastos = new Chart(canvasGastos.getContext('2d'), {
            type:'bar', data:{labels:['COMPROMISO','SALDO POR COMPROMETER'], datasets:[{label:'Valor', data:[totalComprometido,totalSaldo], backgroundColor:['#ef5350','#ffcc80']}]},
            options:{responsive:true, plugins:{legend:{display:false}, tooltip:{mode:'index', intersect:false}, datalabels:{color:'#000', anchor:'end', align:'end', font:{weight:'bold'}, formatter:(v)=>formatoMoneda(v)}}, scales:{x:{stacked:true}, y:{stacked:true, beginAtZero:true}}},
            plugins:[ChartDataLabels]
        });

        // Chart 2: Presupuesto
        chartPresupuesto = disposeChart(chartPresupuesto);
        const canvasPres = document.getElementById('canvas-presupuesto');
        if (canvasPres) {
            const labels = ['INICIAL','OPERACIONES','ACTUAL','COMPROMETIDO','SALDO'];
            const valores = [totalInicial,totalOperaciones,totalActual,totalComprometido,totalSaldo];
            chartPresupuesto = new Chart(canvasPres.getContext('2d'), {
                type:'bar', data:{labels:labels, datasets:[{label:'Valor', data:valores, backgroundColor:['#4e79a7','#f28e2b','#59a14f','#e15759','#b07aa1']}]},
                options:{responsive:true, plugins:{legend:{display:false}, tooltip:{mode:'index', intersect:false}, datalabels:{color:'#000', anchor:'end', align:'end', font:{weight:'bold'}, formatter:(v)=>formatoMoneda(v)}}, scales:{x:{stacked:false}, y:{beginAtZero:true}}},
                plugins:[ChartDataLabels]
            });
        }

        // Chart 3: Dependencias
        chartDependencias = disposeChart(chartDependencias);
        const entries = Array.from(porDependencia.entries()).sort((a,b)=>b[1]-a[1]);
        const top = entries.slice(0,10);
        if (entries.length>10) top.push(['Otros', entries.slice(10).reduce((acc,c)=>acc+c[1],0)]);
        const depLabels = top.map(x=>x[0]);
        const depValues = top.map(x=>x[1]);
        const canvasDeps = document.getElementById('canvas-dependencias');
        if (canvasDeps) chartDependencias = new Chart(canvasDeps.getContext('2d'), { type:'bar', data:{labels:depLabels, datasets:[{label:'Comprometido', data:depValues, backgroundColor:depLabels.map((_,i)=>palette[i%palette.length])}]}, options:{indexAxis:'y', responsive:true, plugins:{legend:{display:false}, tooltip:{mode:'nearest', intersect:true}, datalabels:{color:'#000', anchor:'end', align:'end', font:{weight:'bold'}, formatter:(v)=>formatoMoneda(v)}}, scales:{x:{beginAtZero:true}}}, plugins:[ChartDataLabels] });
    };

    // ======================
    // Filtro por Dependencia y CDP
    // ======================
    btnBuscar.addEventListener('click', async () => {
        await cargarDependencias();
        const week = weekLabelDetalles.textContent.replace('- ','');
        const dep = dependenciaInput.value.trim();
        const cdp = cdpInput.value.trim();
        try {
            const resp = await fetch(${BASE_URL}reports/detalles?week=${week}&dependencia=${dep}&cdp=${cdp});
            const data = await resp.json();
            tbodyDetalles.innerHTML = '';
            data.forEach(row=>{
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${row.numero_cdp}</td>
                    <td>${row.fecha_registro}</td>
                    <td>${row.concepto_interno}</td>
                    <td>${row.rubro}</td>
                    <td>${row.descripcion}</td>
                    <td>${row.fuente}</td>
                    <td>${formatoMoneda(row.valor_actual)}</td>
                    <td>${formatoMoneda(row.saldo_por_comprometer)}</td>
                    <td>${formatoMoneda(row.valor_comprometido)}</td>
                    <td>${row.compromiso}</td>
                    <td>${row.objeto}</td>
                `;
                tbodyDetalles.appendChild(tr);
            });

            // Mini gráfico del total de la consulta
            const totales = aggregateRows(data);
            const miniContainer = document.getElementById('mini-presupuesto-container');
            miniContainer.style.display = 'block';
            document.getElementById('mini-presupuesto-label').textContent = Totales CDP ${cdp || 'Todos'};
            generarGraficaCDP('mini-presupuesto-chart', totales.totalInicial, totales.totalComprometido, totales.totalSaldo, Totales ${cdp || 'Todos'});
        } catch(e){ console.error(e); }
    });

    // ======================
    // Generar mini gráfica
    // ======================
    function generarGraficaCDP(idCanvas, inicial, comprometido, saldo, titulo="") {
        let ctx = document.getElementById(idCanvas).getContext("2d");
        new Chart(ctx, {
            type:'bar',
            data:{ labels:["Valor Inicial","Valor Comprometido","Saldo por Comprometer"], datasets:[{label:"Valor", data:[inicial, comprometido, saldo], backgroundColor:["#2196f3","#ffcc80","#ef5350"]}]},
            options:{ responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}, title:{display:titulo!=="", text:titulo, font:{size:16, weight:"bold"}}, tooltip:{mode:"index", intersect:false}, datalabels:{anchor:"end", align:"end", color:"#000", font:{weight:"bold"}, formatter:(v)=>formatoMoneda(v)}} , scales:{x:{ticks:{autoSkip:false,maxRotation:0,minRotation:0,font:{weight:"bold"}}}, y:{beginAtZero:true}} },
            plugins:[ChartDataLabels]
        });
    }

});


