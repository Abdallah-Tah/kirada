<style>
    @page { size: A4 portrait; margin: 12mm 14mm 18mm; }
    * { box-sizing: border-box; font-family: DejaVu Sans, sans-serif; }
    html, body { margin: 0; padding: 0; color: #0f172a; background: #ffffff; }
    body { font-size: 10.5px; line-height: 1.45; }
    .pdf-top-band { height: 8px; margin: 0 0 13px; background: #071a3a; border-bottom: 3px solid #14b8a6; }
    .pdf-header { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 16px; }
    .pdf-header td { padding: 0; vertical-align: middle; }
    .pdf-logo { width: 148px; height: auto; max-height: 57px; }
    .pdf-meta { width: 42%; text-align: right; color: #64748b; font-size: 9px; }
    .pdf-meta-icon { display: inline-block; width: 8px; height: 11px; margin-right: 5px; border: 1.5px solid #0ea5e9; vertical-align: -1px; }
    .pdf-meta-reference { color: #071a3a; font-size: 13px; font-weight: bold; overflow-wrap: anywhere; }
    .pdf-title { margin: 0; color: #071a3a; font-size: 24px; line-height: 1.15; letter-spacing: -0.3px; }
    .pdf-subtitle { margin: 5px 0 0; color: #64748b; font-size: 10.5px; }
    .pdf-highlight { margin: 17px 0; padding: 11px 14px; border: 1px solid #5eead4; border-radius: 7px; background: #ecfeff; color: #0f766e; font-size: 12px; font-weight: bold; }
    .pdf-highlight-success { border-color: #86efac; background: #f0fdf4; color: #15803d; }
    .pdf-card { margin-top: 14px; border: 1px solid #dbeafe; border-radius: 8px; background: #f8fbff; overflow: hidden; page-break-inside: avoid; }
    .pdf-details { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .pdf-details th, .pdf-details td { padding: 8px 11px; border-bottom: 1px solid #dbeafe; vertical-align: top; overflow-wrap: break-word; word-wrap: break-word; }
    .pdf-details tr:last-child th, .pdf-details tr:last-child td { border-bottom: 0; }
    .pdf-details th { width: 30%; color: #0c4a6e; font-size: 8px; font-weight: bold; letter-spacing: 0.55px; text-align: left; text-transform: uppercase; }
    .pdf-details td { color: #1e293b; font-size: 10.5px; }
    .pdf-status { display: inline-block; padding: 3px 8px; border-radius: 10px; font-size: 8.5px; font-weight: bold; text-transform: uppercase; }
    .status-green { background: #dcfce7; color: #166534; }
    .status-red { background: #fee2e2; color: #b91c1c; }
    .status-amber { background: #fef3c7; color: #92400e; }
    .status-blue { background: #dbeafe; color: #1d4ed8; }
    .status-gray { background: #e2e8f0; color: #475569; }
    .pdf-lines { width: 100%; margin-top: 17px; border-collapse: collapse; table-layout: fixed; }
    .pdf-lines thead { display: table-header-group; }
    .pdf-lines tr { page-break-inside: avoid; }
    .pdf-lines th { padding: 8px 10px; background: #071a3a; color: #ffffff; font-size: 8px; letter-spacing: 0.65px; text-align: left; text-transform: uppercase; }
    .pdf-lines th:first-child { border-radius: 6px 0 0 0; }
    .pdf-lines th:last-child { border-radius: 0 6px 0 0; }
    .pdf-lines td { padding: 8px 10px; border-bottom: 1px solid #dbeafe; color: #334155; vertical-align: top; overflow-wrap: break-word; word-wrap: break-word; }
    .pdf-lines .num { width: 29%; text-align: right; white-space: nowrap; }
    .pdf-lines .qty { width: 13%; text-align: right; }
    .pdf-lines .unit-price { width: 20%; text-align: right; white-space: nowrap; }
    .pdf-lines .pdf-total td { border-top: 2px solid #38bdf8; border-bottom: 0; background: #eff6ff; color: #071a3a; font-size: 12px; font-weight: bold; }
    .pdf-lines .pdf-summary-label { text-align: right; font-weight: bold; }
    .pdf-payment-reference { margin-top: 18px; padding: 14px 16px; border-radius: 8px; background: #082f63; color: #ffffff; page-break-inside: avoid; }
    .pdf-payment-reference-label { color: #67e8f9; font-size: 8px; font-weight: bold; letter-spacing: 0.8px; text-transform: uppercase; }
    .pdf-payment-reference-value { margin-top: 3px; color: #ffffff; font-size: 18px; font-weight: bold; letter-spacing: 1.2px; overflow-wrap: anywhere; }
    .pdf-payment-reference-hint { margin-top: 5px; color: #dbeafe; font-size: 9px; }
    .pdf-note { margin-top: 13px; padding: 9px 11px; border-left: 3px solid #38bdf8; background: #f8fafc; color: #475569; overflow-wrap: break-word; page-break-inside: avoid; }
    .pdf-section-title { margin: 16px 0 6px; color: #071a3a; font-size: 12px; }
    .pdf-signature-table { width: 100%; margin-top: 20px; border-collapse: separate; border-spacing: 8px; }
    .pdf-signature-cell { width: 50%; padding: 10px; border: 1px solid #dbeafe; border-radius: 7px; vertical-align: top; page-break-inside: avoid; }
    .pdf-small-label { color: #0c4a6e; font-size: 8px; font-weight: bold; letter-spacing: 0.5px; text-transform: uppercase; }
    .pdf-muted { color: #64748b; }
    .pdf-last-page-footer { margin-top: 15px; color: #94a3b8; font-size: 8px; text-align: center; }
</style>
