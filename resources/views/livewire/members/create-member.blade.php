<div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form wire:submit.prevent="save">

        <p class="modal-section-label">Personal Details</p>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">First Name</label>
                <input type="text" class="form-control" wire:model.defer="first_name">
                @error('first_name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Last Name</label>
                <input type="text" class="form-control" wire:model.defer="last_name">
                @error('last_name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" wire:model.defer="email">
                @error('email') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Phone</label>
                <input type="text" class="form-control" wire:model.defer="phone">
            </div>
        </div>

        <hr class="my-3">
        <p class="modal-section-label"><i class="icofont icofont-building-alt"></i> Church Assignment</p>
        <div class="row">
            <div class="col-md-12 mb-3">
                <label class="form-label">Church</label>
                <select class="form-control" wire:model.defer="church_id">
                    <option value="">-- Select Church --</option>
                    @foreach($churches as $church)
                        <option value="{{ $church->id }}">
                            {{ strtoupper($church->name) }}@if(optional($church->current_head)->member) ({{ $church->current_head->member->first_name }} {{ $church->current_head->member->last_name }}) @endif
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">The church's current head/pastor is shown in brackets.</small>
                @error('church_id') <small class="text-danger d-block">{{ $message }}</small> @enderror
            </div>
        </div>

        <hr class="my-3">
        <p class="modal-section-label"><i class="icofont icofont-badge"></i> Member Designation(s)</p>
        <div class="row">
            <div class="col-md-12 mb-2">
                <small class="text-muted d-block mb-2">A member can hold more than one designation — tick all that apply.</small>
                <div class="row">
                    @foreach($designations as $designation)
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       id="designation_{{ $designation->id }}"
                                       value="{{ $designation->id }}"
                                       wire:model="designation_ids">
                                <label class="form-check-label" for="designation_{{ $designation->id }}">
                                    {{ ucwords($designation->name) }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
                @error('designation_ids') <small class="text-danger d-block">{{ $message }}</small> @enderror
            </div>
        </div>

        <button class="btn btn-primary mt-2">
            <i class="icofont icofont-plus-circle"></i> Save Member
        </button>

    </form>
</div>
