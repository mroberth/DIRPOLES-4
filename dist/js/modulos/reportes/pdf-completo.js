/**
 * dist/js/modulos/reportes/pdf-completo.js
 * ---------------------------------------------------------------
 * Generador de PDF Completo para Reportes Estadísticos (pdfMake).
 * Captura las tarjetas data-stat, los gráficos Chart.js como imágenes PNG,
 * y extrae las filas de las tablas de datos.
 */
window.ReportesPdfCompleto = (function () {
    'use strict';

    function generar(config) {
        if (typeof pdfMake === 'undefined') {
            if (typeof ReportesComunes !== 'undefined') {
                ReportesComunes.error('pdfMake no está disponible para generar el PDF.');
            } else {
                alert('pdfMake no está disponible.');
            }
            return;
        }

        const tituloReporte = config.titulo || 'Reporte Estadístico';
        const canvasIds = config.canvasIds || [];
        const selectorTabla = config.selectorTabla || '.table';

        // 1. Encabezado del documento
        const content = [
            { text: 'UNIVERSIDAD POLITÉCNICA TERRITORIAL DE LARA ANDRÉS ELOY BLANCO', style: 'subHeader', alignment: 'center' },
            { text: 'DIRECCIÓN DE POLÍTICAS ESTUDIANTILES (DIRPOLES)', style: 'subHeader', alignment: 'center' },
            { text: tituloReporte.toUpperCase(), style: 'mainTitle', alignment: 'center', margin: [0, 10, 0, 15] },
            { text: `Fecha de generación: ${new Date().toLocaleString('es-VE')}`, style: 'fecha', alignment: 'right', margin: [0, 0, 0, 15] }
        ];

        // 2. Extraer tarjetas data-stat
        const statNodes = document.querySelectorAll('[data-stat]');
        if (statNodes.length > 0) {
            content.push({ text: 'Resumen Estadístico', style: 'sectionTitle' });

            const statCards = [];
            statNodes.forEach(node => {
                const card = node.closest('.card');
                const titleNode = card ? card.querySelector('.text-xs, .small, label, h6') : null;
                const title = titleNode ? titleNode.textContent.trim() : node.getAttribute('data-stat');
                const val = node.textContent.trim();
                statCards.push({ title, val });
            });

            const tableBody = [];
            for (let i = 0; i < statCards.length; i += 3) {
                const row = [];
                for (let j = 0; j < 3; j++) {
                    if (i + j < statCards.length) {
                        const item = statCards[i + j];
                        row.push({
                            text: `${item.title}\n${item.val}`,
                            style: 'statCell',
                            alignment: 'center',
                            fillColor: '#f8f9fc'
                        });
                    } else {
                        row.push({ text: '', border: [false, false, false, false] });
                    }
                }
                tableBody.push(row);
            }

            content.push({
                table: {
                    widths: ['33%', '33%', '34%'],
                    body: tableBody
                },
                layout: 'lightHorizontalLines',
                margin: [0, 0, 0, 20]
            });
        }

        // 3. Capturar gráficos Chart.js como imágenes
        const chartImages = [];
        canvasIds.forEach(id => {
            const canvas = document.getElementById(id);
            if (canvas && typeof canvas.toDataURL === 'function') {
                try {
                    const imgData = canvas.toDataURL('image/png');
                    chartImages.push(imgData);
                } catch (e) {
                    console.warn('No se pudo convertir canvas a imagen:', id, e);
                }
            }
        });

        if (chartImages.length > 0) {
            content.push({ text: 'Gráficos Estadísticos', style: 'sectionTitle' });

            if (chartImages.length === 2) {
                content.push({
                    columns: [
                        { image: chartImages[0], width: 240, alignment: 'center' },
                        { image: chartImages[1], width: 240, alignment: 'center' }
                    ],
                    columnGap: 10,
                    margin: [0, 0, 0, 20]
                });
            } else {
                chartImages.forEach(img => {
                    content.push({ image: img, width: 450, alignment: 'center', margin: [0, 0, 0, 15] });
                });
            }
        }

        // 4. Extraer tabla(s) de datos
        const tablas = document.querySelectorAll(selectorTabla);
        let tablaProcesada = false;

        tablas.forEach((tabla, index) => {
            // Solo procesar tablas visibles o si es la única
            if (tabla.offsetParent === null && tablas.length > 1) return;

            const headers = Array.from(tabla.querySelectorAll('thead th')).map(th => th.textContent.trim());
            const rows = Array.from(tabla.querySelectorAll('tbody tr')).map(tr => {
                return Array.from(tr.querySelectorAll('td')).map(td => td.textContent.trim());
            }).filter(r => r.length > 0 && !r[0].includes('No se encontraron') && !r[0].includes('No hay datos'));

            if (headers.length > 0 && rows.length > 0) {
                tablaProcesada = true;
                const cardHeader = tabla.closest('.card')?.querySelector('.card-header h6');
                const tituloTabla = cardHeader ? cardHeader.textContent.trim() : `Detalle de Datos`;

                content.push({ text: tituloTabla, style: 'sectionTitle', pageBreak: chartImages.length > 0 ? 'before' : 'none' });

                const tableBody = [
                    headers.map(h => ({ text: h, style: 'tableHeader', alignment: 'center', fillColor: '#4e73df', color: '#ffffff' }))
                ];

                rows.forEach((r, rIdx) => {
                    const rowCells = r.map(cell => ({
                        text: cell,
                        style: 'tableCell',
                        fillColor: rIdx % 2 === 1 ? '#f8f9fc' : '#ffffff'
                    }));
                    tableBody.push(rowCells);
                });

                const colWidths = headers.map(() => '*');

                content.push({
                    table: {
                        headerRows: 1,
                        widths: colWidths,
                        body: tableBody
                    },
                    layout: 'grid',
                    margin: [0, 0, 0, 20]
                });
            }
        });

        const docDefinition = {
            pageOrientation: 'portrait',
            pageSize: 'LETTER',
            pageMargins: [40, 40, 40, 40],
            content: content,
            styles: {
                mainTitle: { fontSize: 16, bold: true, color: '#2e59d9' },
                subHeader: { fontSize: 10, bold: true, color: '#5a5c69' },
                sectionTitle: { fontSize: 13, bold: true, color: '#2e59d9', margin: [0, 10, 0, 10] },
                fecha: { fontSize: 9, italic: true, color: '#858796' },
                statCell: { fontSize: 10, bold: true, color: '#333333', margin: [2, 4, 2, 4] },
                tableHeader: { fontSize: 9, bold: true },
                tableCell: { fontSize: 8 }
            },
            defaultStyle: {
                font: 'Roboto'
            }
        };

        const nombreArchivo = (tituloReporte.toLowerCase().replace(/[^a-z0-9]/g, '_')) + '_completo.pdf';
        pdfMake.createPdf(docDefinition).download(nombreArchivo);
    }

    return { generar };
})();
