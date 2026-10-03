{{-- Shared Create/Edit Program form. $mode is 'new' or 'edit' - every field
     id is prefixed with it so both modals can exist on the page at once. --}}
<div class="modal fade" id="{{ $modalId }}">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<form method="POST" @if($mode === 'edit') id="editProgramForm" @endif action="{{ $formAction }}" enctype="multipart/form-data">
@csrf

<div class="modal-header bg-primary text-white">
    <h5 class="modal-title">
        <i class="icofont icofont-{{ $mode === 'new' ? 'plus-circle' : 'edit' }}"></i>
        {{ $mode === 'new' ? 'Create Program' : 'Edit Program' }}
    </h5>
    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<p class="modal-section-label">Basic Information</p>
<div class="row">
    <div class="col-md-8">
        <label class="form-label">Program Name</label>
        <input type="text" name="name" id="{{ $mode }}_name" class="form-control" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Category</label>
        <select name="category" id="{{ $mode }}_category" class="form-control" required>
            @foreach(['service'=>'Service','meeting'=>'Meeting','program'=>'Program','event'=>'Event','course'=>'Course','crusade'=>'Crusade','conference'=>'Conference','cell_meeting'=>'Cell Meeting','other'=>'Other'] as $val => $label)
                <option value="{{ $val }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-12 mt-3">
        <label class="form-label">Description</label>
        <textarea name="description" id="{{ $mode }}_description" class="form-control" rows="2"></textarea>
    </div>
    <div class="col-md-6 mt-3">
        <label class="form-label">Organizer</label>
        <input type="text" name="organizer" id="{{ $mode }}_organizer" class="form-control">
    </div>
    <div class="col-md-6 mt-3">
        <label class="form-label">Location</label>
        <input type="text" name="location" id="{{ $mode }}_location" class="form-control">
    </div>
    <div class="col-md-6 mt-3">
        <label class="form-label">Contact Phone</label>
        <input type="tel" name="contact_phone" id="{{ $mode }}_contact_phone" class="form-control" placeholder="e.g. 0712345678">
        <small class="text-muted">Shown to attendees, e.g. on the check-in page before the program starts.</small>
    </div>
    <div class="col-md-12 mt-3">
        <label class="form-label">Banner / Image</label>
        @if($mode === 'edit')
        <div id="edit_banner_preview_wrap" class="mb-2" style="display:none">
            <img id="edit_banner_preview" src="" alt="Current banner" style="max-height:90px;border-radius:8px;">
        </div>
        @endif
        <input type="file" name="banner" accept="image/*" class="form-control">
        @if($mode === 'edit')<small class="text-muted">Leave empty to keep the current image.</small>@endif
    </div>
</div>

<hr class="my-3">
<p class="modal-section-label">Classification &amp; Schedule</p>
<div class="row">
    <div class="col-md-4">
        <label class="form-label">Program Type</label>
        <select name="classification" id="{{ $mode }}_classification" class="form-control" required>
            <option value="special">Special Event</option>
            <option value="recurring">Recurring</option>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" id="{{ $mode }}_status" class="form-control" required>
            <option value="draft">Draft</option>
            <option value="active" selected>Active</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>
</div>

{{-- Sessions: e.g. Morning 09:00-12:00, Noon 14:00-17:00 - repeated on every
     day of the program; QR check-in is per session. Rows are added by the
     page script (programSessions.add). --}}
<div class="mt-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <label class="form-label mb-0">Sessions <small class="text-muted">(each day of the program)</small></label>
        <button type="button" class="btn btn-sm btn-outline-primary" data-add-session="{{ $mode }}">
            <i class="icofont icofont-plus"></i> Add Session
        </button>
    </div>
    <div id="{{ $mode }}_sessions" class="program-sessions"></div>
    <small class="text-muted">QR check-in opens {{ \App\Models\ProgramSession::CHECK_IN_OPENS_BEFORE_MINUTES }} minutes before each session starts and closes when it ends.</small>
</div>

<div class="row mt-2" id="{{ $mode }}_recurring_wrap">
    <div class="col-md-4 mt-2">
        <label class="form-label">Repeats</label>
        <select name="recurrence_frequency" id="{{ $mode }}_frequency" class="form-control">
            <option value="">-- Select --</option>
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
            <option value="custom">Custom</option>
        </select>
    </div>
    <div class="col-md-4 mt-2 d-flex align-items-end">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="qr_enabled" value="1" id="{{ $mode }}_qr">
            <label class="form-check-label" for="{{ $mode }}_qr">Enable QR/Barcode Check-in</label>
        </div>
    </div>
    <div class="col-md-12 mt-2" id="{{ $mode }}_days_wrap">
        <label class="form-label">Repeat On</label>
        <div class="d-flex flex-wrap gap-3">
            @foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day)
            <div class="form-check">
                <input class="form-check-input {{ $mode }}-day-check" type="checkbox" name="recurrence_days[]" value="{{ $day }}" id="{{ $mode }}_day_{{ $day }}">
                <label class="form-check-label" for="{{ $mode }}_day_{{ $day }}">{{ ucfirst($day) }}</label>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div class="row mt-2" id="{{ $mode }}_special_wrap">
    <div class="col-md-6 mt-2">
        <label class="form-label">Start Date</label>
        <input type="date" name="start_date" id="{{ $mode }}_start_date" class="form-control">
    </div>
    <div class="col-md-6 mt-2">
        <label class="form-label">End Date</label>
        <input type="date" name="end_date" id="{{ $mode }}_end_date" class="form-control">
    </div>
</div>

<hr class="my-3">
<p class="modal-section-label">Church / Branch Scope</p>
<div class="row">
    <div class="col-md-4">
        <label class="form-label">Scope</label>
        <select name="scope" id="{{ $mode }}_scope" class="form-control" required>
            <option value="global">Global</option>
            <option value="church" selected>Specific Church</option>
            <option value="department">Specific Department</option>
            <option value="cell">Specific Cell Group</option>
        </select>
    </div>
    <div class="col-md-8" id="{{ $mode }}_church_wrap">
        <label class="form-label">Church(es) <span class="text-muted">&mdash; tick every church holding this program together</span></label>
        <input type="search" class="form-control form-control-sm mb-1 church-pick-search" data-target="{{ $mode }}_churches" placeholder="Search churches...">
        <div id="{{ $mode }}_churches" class="church-pick" style="max-height:170px;overflow-y:auto;border:1px solid #e2e6ee;border-radius:8px;padding:6px 10px;">
            @foreach($churches as $church)
                <div class="form-check church-pick-item">
                    <input class="form-check-input" type="checkbox" name="church_ids[]" value="{{ $church->id }}" id="{{ $mode }}_church_{{ $church->id }}">
                    <label class="form-check-label" for="{{ $mode }}_church_{{ $church->id }}">{{ strtoupper($church->name) }}</label>
                </div>
            @endforeach
        </div>
        <small class="text-muted"><span class="church-pick-count" data-target="{{ $mode }}_churches">0</span> selected. The first one is shown as the main church.</small>
    </div>
    <div class="col-md-8" id="{{ $mode }}_department_wrap">
        <label class="form-label">Department</label>
        <select name="department_id" id="{{ $mode }}_department" class="form-control">
            <option value="">-- Select Department --</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-8" id="{{ $mode }}_cell_wrap">
        <label class="form-label">Cell Group</label>
        <select name="cell_group_id" id="{{ $mode }}_cell" class="form-control">
            <option value="">-- Select Cell Group --</option>
            @foreach($cellGroups as $cell)
                <option value="{{ $cell->id }}">{{ $cell->name }}</option>
            @endforeach
        </select>
    </div>
</div>

<hr class="my-3">
<p class="modal-section-label">Program Access</p>
<div class="row">
    <div class="col-md-4">
        <label class="form-label">Access</label>
        <select name="access_type" id="{{ $mode }}_access" class="form-control" required>
            <option value="free">Free</option>
            <option value="paid">Paid</option>
        </select>
    </div>
    <div class="col-md-4" id="{{ $mode }}_currency_wrap2">
        <label class="form-label">Currency</label>
        <input type="text" name="currency" id="{{ $mode }}_currency" class="form-control" value="TZS">
    </div>
</div>

<div class="mt-3" id="{{ $mode }}_fee_wrap">
    <label class="form-label">Price per Group</label>
    <p class="text-muted small mb-2">
        Each person pays the price of their <strong>most senior</strong> group (top of this list first).
        People with no group, such as first-time visitors, register free. Use 0 for a free group.
    </p>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <tbody>
            @foreach($designations as $designation)
                <tr>
                    <td style="width: 55%;"><label class="mb-0" for="{{ $mode }}_price_{{ $designation->id }}">{{ ucwords($designation->name) }}</label></td>
                    <td>
                        <input type="number" step="0.01" min="0" name="prices[{{ $designation->id }}]"
                               id="{{ $mode }}_price_{{ $designation->id }}" class="form-control form-control-sm {{ $mode }}-price-input"
                               data-designation="{{ $designation->id }}" placeholder="0">
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

</div>

<div class="modal-footer">
    <button class="btn btn-light" type="button" data-bs-dismiss="modal">Close</button>
    <button class="btn btn-primary">{{ $mode === 'new' ? 'Create Program' : 'Save Changes' }}</button>
</div>

</form>
</div>
</div>
</div>
