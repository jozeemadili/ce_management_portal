<div>
    <div class="form-group">
        <label class="col-form-label" >Company</label>
        <select class="form-control" required value="{{ old('company_id') }}" wire:model="company_id" name="company_id" id="company_id">
        <option value="">--- Choose Company ---</option>  
        @foreach($companies as $company)
            <option value="{{$company->id}}">{{strtoupper($company->name)}}</option>
        @endforeach
        </select>
    </div>
    <div class="form-group" style="display: {{ $show_branches }}">
        <label class="col-form-label" >Branch </label>
        <select class="form-control js-example-basic-singlex" value="{{ old('branch_id') }}" name="branch_id" id="branch_id">
        <option value="">--- Choose Branch ---</option>  
        @foreach($this->getBranches() as $branch)
            <option value="{{$branch->id}}">{{strtoupper($branch->name)}}</option>
        @endforeach
        </select>
    </div>
</div>

