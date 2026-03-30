document.addEventListener('DOMContentLoaded', () => {
    const config = window.loanHistoryReportConfig || {};
    const excelBtn = document.getElementById('btnSolicitarExcelHistorial');
    const pdfBtn = document.getElementById('btnSolicitarPdfHistorial');

    const notify = (message, type = 'success') => {
        if (typeof window.alerta === 'function') {
            window.alerta(message, type);
            return;
        }
        window.alert(message);
    };

    const requestReport = async (formato) => {
        if (!config.requestUrl) return;

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const payload = new URLSearchParams();
        payload.append('formato', formato);

        const filters = config.filters || {};
        Object.entries(filters).forEach(([key, value]) => {
            if (value !== null && value !== undefined && value !== '') {
                payload.append(key, value);
            }
        });

        try {
            const response = await fetch(config.requestUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token || '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: payload.toString()
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                notify(data.message || 'No se pudo solicitar el reporte.', 'error');
                return;
            }

            notify(data.message, 'success');
            window.setTimeout(() => window.location.href = (config.historyUrl || window.location.href), 1200);
        } catch (error) {
            notify('Ocurrio un error al solicitar el reporte.', 'error');
        }
    };

    excelBtn?.addEventListener('click', () => requestReport('excel'));
    pdfBtn?.addEventListener('click', () => requestReport('pdf'));
});

