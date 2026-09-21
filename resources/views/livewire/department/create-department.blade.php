<div>

    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <p class="modal-section-label">Department Details</p>

    <div class="alert alert-info d-flex align-items-center gap-2">
        <i class="icofont icofont-building-alt fs-5"></i>
        <div>
            <strong>{{ strtoupper($church->name) }}</strong><br>
            <small>Departments created here will automatically belong to your church</small>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Department Name</label>
            <input type="text" class="form-control" wire:model="name">
            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label">Description</label>
            <input type="text" class="form-control" wire:model="description">
        </div>
    </div>

    <hr class="my-3">
    <p class="modal-section-label"><i class="icofont icofont-users"></i> Department Members</p>

    @foreach($members as $index => $row)
    <div class="row g-2 align-items-center mb-2">

        <div class="col-md-6">
            <select class="form-control" wire:model="members.{{ $index }}.member_id">
                <option value="">-- Select Member --</option>
                @foreach($churchMembers as $m)
                    <option value="{{ $m->id }}">
                        {{ $m->first_name }} {{ $m->last_name }} ({{ $m->phone }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <select class="form-control" wire:model="members.{{ $index }}.role">
                <option value="">-- Role --</option>
                <option value="DEPARTMENT_HEAD">Department Head</option>
                <option value="ASSISTANT_HEAD">Assistant Head</option>
                <option value="member">Member</option>
            </select>
        </div>

        <div class="col-md-2">
            <button type="button" class="btn btn-outline-danger w-100"
                    wire:click="removeMember({{ $index }})" title="Remove row">
                <i class="icofont icofont-minus-circle"></i>
            </button>
        </div>

    </div>
    @endforeach

    @error('members')
        <small class="text-danger d-block mt-1">{{ $message }}</small>
    @enderror

    <button type="button" class="btn btn-outline-secondary btn-sm mt-2" wire:click="addMember">
        <i class="icofont icofont-plus-circle"></i> Add Member
    </button>

    <hr class="my-3">

    <button type="button" class="btn btn-primary" wire:click="save">
        <i class="icofont icofont-check-circled"></i> Save Department
    </button>

</div>
