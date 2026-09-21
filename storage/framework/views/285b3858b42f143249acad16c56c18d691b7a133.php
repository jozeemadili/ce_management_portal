
<style>
    .pledge-stat-card {
        border: none; border-radius: 14px; box-shadow: 0 2px 10px rgba(46, 90, 172, 0.08);
        overflow: hidden; height: 100%;
    }
    .pledge-stat-card .stat-body { display: flex; align-items: center; gap: 16px; padding: 20px; }
    .pledge-stat-icon {
        width: 54px; height: 54px; min-width: 54px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center; font-size: 24px; color: #fff;
    }
    .pledge-stat-icon.bg-1 { background: linear-gradient(135deg,#2e5aac,#4d7de0); }
    .pledge-stat-icon.bg-2 { background: linear-gradient(135deg,#1fa971,#34d399); }
    .pledge-stat-icon.bg-3 { background: linear-gradient(135deg,#a855f7,#c084fc); }
    .pledge-stat-icon.bg-4 { background: linear-gradient(135deg,#d08c1d,#f0b429); }
    .pledge-stat-icon.bg-5 { background: linear-gradient(135deg,#e04b4b,#f0796f); }
    .pledge-stat-icon.bg-6 { background: linear-gradient(135deg,#0ea5a0,#2dd4bf); }
    .pledge-stat-icon.bg-7 { background: linear-gradient(135deg,#c2418c,#e879b9); }
    .pledge-stat-value { font-size: 1.4rem; font-weight: 700; line-height: 1.1; margin: 0; }
    .pledge-stat-label { font-size: .76rem; color: #8a92a6; margin: 0; text-transform: uppercase; letter-spacing: .03em; }

    .pledge-card { border: none; border-radius: 14px; box-shadow: 0 2px 10px rgba(46,90,172,.06); }
    .pledge-filter-bar { background: #f7f9fc; border-radius: 12px; padding: 16px; margin-bottom: 20px; }

    .pledge-table thead th {
        background: #f0f3f9; border-bottom: none; font-size: .78rem;
        text-transform: uppercase; letter-spacing: .03em; color: #6b7280; white-space: nowrap;
    }
    .pledge-table tbody tr { transition: background .15s ease; }
    .pledge-table tbody tr:hover { background: #f7f9fc; }
    .pledge-table td { vertical-align: middle; }

    .pledge-empty { padding: 60px 20px; text-align: center; color: #9aa2b1; }
    .pledge-empty i { font-size: 48px; display: block; margin-bottom: 12px; color: #c8cedb; }

    .badge-pill { font-weight: 600; font-size: .72rem; padding: 5px 12px; border-radius: 20px; display: inline-block; }
    .badge-status-draft     { background:#eef0f3; color:#4b5563; }
    .badge-status-active    { background:#e6f7ee; color:#0f9d58; }
    .badge-status-completed { background:#e8f0fe; color:#2e5aac; }
    .badge-status-closed    { background:#fdecec; color:#d93025; }
    .badge-status-pledged            { background:#eef2ff; color:#4338ca; }
    .badge-status-partially_fulfilled{ background:#fff4e5; color:#b45309; }
    .badge-status-fulfilled          { background:#e6f7ee; color:#0f9d58; }
    .badge-status-cancelled          { background:#fdecec; color:#d93025; }
    .badge-source-staff { background:#fff4e5; color:#b45309; }
    .badge-source-member { background:#eef2ff; color:#4338ca; }
    .badge-scope-global { background:#e8f0fe; color:#2e5aac; }
    .badge-scope-church { background:#f3e8ff; color:#7e22ce; }

    .pledge-progress { height: 10px; border-radius: 20px; background: #eef1f6; overflow: hidden; }
    .pledge-progress-bar { height: 100%; border-radius: 20px; background: linear-gradient(90deg,#2e5aac,#4d7de0); transition: width .4s ease; }

    .campaign-card {
        border: none; border-radius: 16px; box-shadow: 0 2px 10px rgba(46,90,172,.08);
        overflow: hidden; height: 100%; display: flex; flex-direction: column;
    }
    .campaign-card-banner {
        height: 120px; background: linear-gradient(135deg,#2e5aac,#4d7de0);
        display: flex; align-items: center; justify-content: center; color: #fff; font-size: 40px;
    }
    .campaign-card-body { padding: 18px; display: flex; flex-direction: column; gap: 10px; flex: 1; }
    .campaign-card-title { font-weight: 700; font-size: 1.05rem; margin: 0; }
    .campaign-card-desc { font-size: .82rem; color: #6b7280; flex: 1; }
    .campaign-card-meta { display: flex; justify-content: space-between; font-size: .78rem; color: #6b7280; }

    /* ---- modal polish ---- */
    .modal-content { border: none; border-radius: 16px; overflow: hidden; }
    .modal-header { border-bottom: none; padding: 18px 24px; }
    .modal-header .modal-title { font-weight: 600; }
    .modal-body { padding: 22px 24px; }
    .modal-body label.form-label { font-weight: 600; font-size: .82rem; color: #4b5563; }
    .modal-body .form-control, .modal-body .form-select { border-radius: 8px; border: 1px solid #e2e6ee; }
    .modal-body .form-control:focus, .modal-body .form-select:focus { border-color: #4d7de0; box-shadow: 0 0 0 3px rgba(77,125,224,.15); }
    .modal-footer { border-top: 1px solid #f0f2f7; padding: 16px 24px; }
    .modal-section-label { font-size: .72rem; text-transform: uppercase; letter-spacing: .05em; color: #9aa2b1; font-weight: 700; margin: 4px 0 10px; }
</style>
<?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_management_portal/resources/views/portal/pledges/partials/styles.blade.php ENDPATH**/ ?>