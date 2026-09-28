{{--
    Edit Church modal, shared by Church Management and the Church Hierarchy
    tree. Needs $designations and $members. Open it from JS with
    openEditChurch({id, name, location, designation, parent, head}).
    The page must load select2 (assets/js/select2/select2.full.min.js).
--}}
@push('css')
<style>
    /* Same look as on Church Management, scoped to this modal. */
    #editChurchModal .modal-content { border: none; border-radius: 16px; overflow: hidden; }
    #editChurchModal .modal-header { border-bottom: none; padding: 18px 24px; }
    #editChurchModal .modal-header .modal-title { font-weight: 600; display: flex; align-items: center; gap: 8px; }
    #editChurchModal .modal-body { padding: 22px 24px; }
    #editChurchModal .modal-body label { font-weight: 600; font-size: .82rem; color: #4b5563; margin-bottom: 4px; display: block; }
    #editChurchModal .modal-body .form-control { border-radius: 8px; border: 1px solid #e2e6ee; padding: 9px 12px; }
    #editChurchModal .modal-body .form-control:focus { border-color: #4d7de0; box-shadow: 0 0 0 3px rgba(77,125,224,.15); }
    #editChurchModal .modal-footer { border-top: 1px solid #f0f2f7; padding: 16px 24px; }
    #editChurchModal .modal-section-label { font-size: .72rem; text-transform: uppercase; letter-spacing: .05em; color: #9aa2b1; font-weight: 700; margin: 4px 0 10px; }
</style>
@endpush
{{-- ================= EDIT CHURCH MODAL ================= --}}
<div class="modal fade" id="editChurchModal">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<form method="POST" id="editChurchForm" action="">
@csrf

<div class="modal-header bg-primary text-white">
    <h5 class="modal-title"><i class="icofont icofont-edit"></i> Edit Church</h5>
    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<p class="modal-section-label">Church Details</p>
<div class="row">

<div class="col-md-6">
    <label>Name</label>
    <input type="text" name="name" id="edit_name" class="form-control" required>
</div>

<div class="col-md-6">
    <label>Location</label>
    <input type="text" name="physical_location" id="edit_physical_location" class="form-control" required>
</div>

<div class="col-md-6 mt-3">
    <label>Church Designation</label>
    <select name="designation_id" id="edit_designation_id" class="form-control" required>
        <option value="">-- Select --</option>
        @foreach($designations as $des)
            <option value="{{ $des->id }}">{{ strtoupper($des->name) }}</option>
        @endforeach
    </select>
</div>

<div class="col-md-6 mt-3">
    <label>Parent Church</label>
    <select name="parent_church_id" id="edit_parent_church_id" class="form-control">
        <option value="">-- ROOT --</option>
    </select>
</div>

</div>

<hr class="my-3">
<p class="modal-section-label"><i class="icofont icofont-user-alt-3"></i> Leadership</p>
<div class="row">
<div class="col-md-12">
    <label>Head of Church (Pastor)</label>
    <select name="head_of_unit" id="edit_head_of_unit" class="form-control church-head-select">
        <option value="">-- Not Assigned --</option>
        @foreach($members as $m)
            <option value="{{ $m->id }}">{{ $m->first_name }} {{ $m->last_name }}@if($m->member_roles->first()) — {{ ucwords($m->member_roles->first()->member_designation->name ?? '') }}@endif (@if($m->church){{ strtoupper($m->church->name) }}@endif)</option>
        @endforeach
    </select>
    <small class="text-muted">Selecting a different member records a new leadership assignment for this church.</small>
</div>
</div>

</div>

<div class="modal-footer">
    <button class="btn btn-light" type="button" data-bs-dismiss="modal">Close</button>
    <button class="btn btn-primary">Save Changes</button>
</div>

</form>
</div>
</div>
</div>

@push('scripts')
<script>
(function () {
    var designationOrder = @json($designations->sortBy('id')->values()->pluck('id'));

    // Parent options = active churches one designation level up.
    function loadEditParents(designationId, selectedParentId) {
        var parentSelect = document.getElementById('edit_parent_church_id');
        parentSelect.innerHTML = '<option value="">-- ROOT --</option>';

        var index = designationOrder.indexOf(parseInt(designationId, 10));
        if (index <= 0) return;

        fetch('/v1/api/churches/by-designation/' + designationOrder[index - 1])
            .then(function (res) { return res.json(); })
            .then(function (data) {
                parentSelect.innerHTML = '<option value="">-- ROOT --</option>';
                data.forEach(function (ch) {
                    var option = document.createElement('option');
                    option.value = ch.id;
                    option.textContent = ch.name;
                    option.selected = selectedParentId && parseInt(selectedParentId, 10) === ch.id;
                    parentSelect.appendChild(option);
                });
            });
    }

    document.getElementById('edit_designation_id').addEventListener('change', function () {
        loadEditParents(this.value, null);
    });

    window.openEditChurch = function (data) {
        document.getElementById('editChurchForm').action = '/v1/churches/' + data.id + '/update';
        document.getElementById('edit_name').value = data.name || '';
        document.getElementById('edit_physical_location').value = data.location || '';
        document.getElementById('edit_designation_id').value = data.designation || '';
        document.getElementById('edit_head_of_unit').value = data.head || '';
        loadEditParents(data.designation, data.parent);

        var modalEl = document.getElementById('editChurchModal');
        (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)).show();
    };

    // Searchable "Head of Church" picker. select2 may be loaded by a script
    // pushed after this one, so check for it only when the modal opens.
    var $ = window.jQuery;
    if ($) {
        $('#editChurchModal').on('shown.bs.modal', function () {
            if (!$.fn.select2) return;
            var $modal = $(this);
            $modal.find('select.church-head-select').each(function () {
                var $el = $(this);
                if ($el.hasClass('select2-hidden-accessible')) {
                    $el.select2('destroy');
                }
                $el.select2({ placeholder: 'Search for a member...', allowClear: true, width: '100%', dropdownParent: $modal });
            });
        });
    }
})();
</script>
@endpush
