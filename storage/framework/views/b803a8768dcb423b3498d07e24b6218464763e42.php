<?php $__env->startSection('title','Church Hierarchy'); ?>

<?php $__env->startPush('css'); ?>
<style>
    .no-print { }
    .print-only { display: none; }

    .tree-card { border: none; border-radius: 14px; box-shadow: 0 2px 10px rgba(46,90,172,.06); }

    .tree-stat-row { display: flex; flex-wrap: wrap; gap: 16px; margin-bottom: 20px; }
    .tree-stat { flex: 1 1 200px; display: flex; align-items: center; gap: 12px; background: #f7f9fc; border-radius: 12px; padding: 14px 16px; }
    .tree-stat-icon { width: 42px; height: 42px; min-width: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 19px; }
    .tree-stat-icon.total   { background: linear-gradient(135deg,#2e5aac,#4d7de0); }
    .tree-stat-icon.with    { background: linear-gradient(135deg,#1fa971,#34d399); }
    .tree-stat-icon.without { background: linear-gradient(135deg,#e04b4b,#f0796f); }
    .tree-stat-value { font-size: 1.25rem; font-weight: 700; line-height: 1.1; margin: 0; }
    .tree-stat-label { font-size: .72rem; color: #8a92a6; margin: 0; text-transform: uppercase; letter-spacing: .03em; }

    .tree-toolbar {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
        gap: 12px; margin-bottom: 16px;
    }
    .tree-toolbar-hint { color: #6b7280; font-size: .85rem; display: flex; align-items: center; gap: 6px; }
    .tree-toolbar-actions { display: flex; gap: 8px; flex-wrap: wrap; }

    .org-chart-scroll { overflow-x: auto; padding: 16px 4px 24px; }

    ul.org-tree, ul.org-tree ul {
        list-style: none; margin: 0; padding: 0; position: relative; display: flex;
    }
    ul.org-tree { justify-content: flex-start; width: max-content; min-width: 100%; }
    ul.org-tree ul { justify-content: center; padding-top: 34px; }

    ul.org-tree li {
        display: flex; flex-direction: column; align-items: center; position: relative;
        padding: 34px 14px 0 14px; text-align: center; flex-shrink: 0;
    }

    ul.org-tree li::before, ul.org-tree li::after {
        content: ''; position: absolute; top: 0; right: 50%;
        border-top: 2px solid #c7d3da; width: 50%; height: 34px;
    }
    ul.org-tree li::after { right: auto; left: 50%; border-left: 2px solid #c7d3da; }
    ul.org-tree li:only-child::before, ul.org-tree li:only-child::after { display: none; }
    ul.org-tree > li::before, ul.org-tree > li::after { display: none; }
    ul.org-tree li:first-child::before, ul.org-tree li:last-child::after { border: 0 none; }
    ul.org-tree li:last-child::before { border-right: 2px solid #c7d3da; border-radius: 0 6px 0 0; }
    ul.org-tree li:first-child::after { border-radius: 6px 0 0 0; }

    ul.org-tree ul::before {
        content: ''; position: absolute; top: 0; left: 50%;
        border-left: 2px solid #c7d3da; width: 0; height: 34px;
    }

    .org-card {
        position: relative; display: inline-block; min-width: 200px; max-width: 230px;
        background: #fff; border: 1px solid #e3edf1; border-top: 3px solid #2e5aac;
        border-radius: 8px; padding: 12px 16px; box-shadow: 0 2px 6px rgba(20,30,60,.08);
        cursor: pointer; transition: box-shadow .15s ease;
    }
    .org-card:hover { box-shadow: 0 4px 14px rgba(20,30,60,.16); }

    .org-card-name { font-weight: 700; font-size: .86rem; color: #1c1c1e; white-space: normal; }
    .org-card-badge {
        display: inline-block; font-size: .66rem; font-weight: 700; letter-spacing: .02em;
        border-radius: 20px; padding: 2px 10px; margin-top: 6px;
    }
    .org-card-head {
        font-size: .76rem; color: #4b5563; margin-top: 8px;
        display: flex; align-items: center; justify-content: center; gap: 5px;
    }
    .org-card-nohead { color: #d00c0c; font-style: italic; }
    .org-card-meta {
        font-size: .66rem; color: #9aa2b1; margin-top: 6px;
        display: flex; align-items: center; justify-content: center; gap: 10px;
    }

    ul.org-tree li.org-collapsed > ul { display: none; }

    .context-menu {
        position: absolute; display: none; background: #fff; border: none; border-radius: 10px;
        z-index: 1000; width: 190px; box-shadow: 0 8px 24px rgba(0,0,0,.18); overflow: hidden;
    }
    .context-menu ul { list-style: none; padding: 6px; margin: 0; }
    .context-menu li {
        padding: 9px 12px; cursor: pointer; border-radius: 6px; font-size: .86rem;
        display: flex; align-items: center; gap: 8px; color: #374151;
    }
    .context-menu li:hover { background: #f0f3f9; }

    @media print {
        .no-print, .page-sidebar, .page-header, header, .footer { display: none !important; }
        .print-only { display: block; text-align: center; margin-bottom: 16px; }
        .page-body, .page-body-wrapper, .container-fluid { margin: 0 !important; padding: 0 !important; }
        .org-chart-scroll { overflow: visible; }
        .org-card, ul.org-tree li { break-inside: avoid; page-break-inside: avoid; }
        ul.org-tree li.org-collapsed > ul { display: flex !important; }
        @page { size: landscape; margin: 10mm; }
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('breadcrumb_title'); ?>
        <h3>Church Hierarchy</h3>
    <?php $__env->endSlot(); ?>

    <?php $__env->slot('breadcrumb_action_buttons'); ?>
        <li>
            <div class="dropdown">
                <button class="btn btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="icofont icofont-download-alt"></i> Export
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="javascript:void(0)" onclick="window.print()"><i class="icofont icofont-file-pdf"></i> Export as PDF</a></li>
                    <li><a class="dropdown-item" href="<?php echo e(route('churches-export')); ?>"><i class="icofont icofont-file-excel"></i> Export as Excel</a></li>
                </ul>
            </div>
        </li>
        <li>
            <a class="btn btn-primary" href="<?php echo e(route('churches-management')); ?>">
                <i class="icofont icofont-listing-box"></i> Manage Churches
            </a>
        </li>
    <?php $__env->endSlot(); ?>

    <li class="breadcrumb-item"><a href="<?php echo e(route('churches-management')); ?>">Church</a></li>
    <li class="breadcrumb-item active">Hierarchy</li>
<?php echo $__env->renderComponent(); ?>

<div class="container-fluid">

<div class="print-only">
    <h3>Church Hierarchy</h3>
    <p class="text-muted">Generated <?php echo e(now()->format('d M Y, H:i')); ?></p>
</div>

<div class="tree-stat-row no-print">
    <div class="tree-stat">
        <div class="tree-stat-icon total"><i class="icofont icofont-building-alt"></i></div>
        <div>
            <p class="tree-stat-value"><?php echo e($stats['total']); ?></p>
            <p class="tree-stat-label">Total Churches</p>
        </div>
    </div>
    <div class="tree-stat">
        <div class="tree-stat-icon with"><i class="icofont icofont-crown"></i></div>
        <div>
            <p class="tree-stat-value"><?php echo e($stats['with_head']); ?></p>
            <p class="tree-stat-label">With Head Assigned</p>
        </div>
    </div>
    <div class="tree-stat">
        <div class="tree-stat-icon without"><i class="icofont icofont-warning"></i></div>
        <div>
            <p class="tree-stat-value"><?php echo e($stats['without_head']); ?></p>
            <p class="tree-stat-label">Without Head</p>
        </div>
    </div>
</div>

<div class="card tree-card">
<div class="card-body">

<div class="tree-toolbar no-print">
    <div class="tree-toolbar-hint">
        <i class="icofont icofont-info-circle"></i>
        Click a card to collapse/expand its branch &middot; Right-click for quick actions
    </div>
    <div class="tree-toolbar-actions">
        <button type="button" class="btn btn-sm btn-outline-secondary" id="org-expand-all">
            <i class="icofont icofont-expand"></i> Expand All
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="org-collapse-all">
            <i class="icofont icofont-collapse"></i> Collapse All
        </button>
    </div>
</div>

<div class="org-chart-scroll">
    <ul class="org-tree" id="org-tree">
        <?php $__empty_1 = true; $__currentLoopData = $roots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $root): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php echo $__env->make('portal.churches.partials.tree-node', ['church' => $root, 'byParent' => $byParent, 'designationColors' => $designationColors], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <li><div class="org-card">No churches found.</div></li>
        <?php endif; ?>
    </ul>
</div>


<div id="contextMenu" class="context-menu no-print">
    <ul>
        <li onclick="transferChurch()"><i class="icofont icofont-exchange"></i> Transfer</li>
        <li onclick="viewHistory()"><i class="icofont icofont-history"></i> View History</li>
    </ul>
</div>

</div>
</div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var tree = document.getElementById('org-tree');
    if (!tree) return;

    let selectedNodeId = null;

    tree.addEventListener('click', function (e) {
        var card = e.target.closest('.org-card');
        if (!card) return;
        var li = card.closest('li');
        var childUl = li.querySelector(':scope > ul');
        if (childUl) {
            li.classList.toggle('org-collapsed');
        }
    });

    document.getElementById('org-expand-all')?.addEventListener('click', function () {
        tree.querySelectorAll('li.org-collapsed').forEach(li => li.classList.remove('org-collapsed'));
    });

    document.getElementById('org-collapse-all')?.addEventListener('click', function () {
        tree.querySelectorAll('li').forEach(function (li) {
            if (li.querySelector(':scope > ul')) li.classList.add('org-collapsed');
        });
    });

    /* --------------------------------
     | RIGHT CLICK MENU
     |---------------------------------*/
    tree.addEventListener('contextmenu', function (e) {
        var card = e.target.closest('.org-card');
        if (!card) return;
        e.preventDefault();
        selectedNodeId = card.dataset.churchId;
        var menu = document.getElementById('contextMenu');
        menu.style.display = 'block';
        menu.style.left = e.pageX + 'px';
        menu.style.top = e.pageY + 'px';
    });

    document.addEventListener('click', function () {
        document.getElementById('contextMenu').style.display = 'none';
    });

    window.transferChurch = function () {
        if (!selectedNodeId) return;
        window.location.href = `/v1/churches/management?transfer=${selectedNodeId}`;
    };

    window.viewHistory = function () {
        if (!selectedNodeId) return;
        window.location.href = `/v1/churches/${selectedNodeId}/transfer-history`;
    };
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_management_portal/resources/views/portal/churches/tree.blade.php ENDPATH**/ ?>