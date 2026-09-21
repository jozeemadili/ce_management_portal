<div>
    <div class="form-group">
        <label class="col-form-label" >Region</label>
        <select class="form-control" required value="{{ old('region_id') }}" wire:model="region_id" name="region_id">
        <option value="">--- Choose Region ---</option>  
        @foreach($regions as $region)
            <option value="{{$region->id}}">{{strtoupper($region->name)}}</option>
        @endforeach
        </select>
    </div>
    <div class="form-group" style="displayx: {{ $show_districts }}">
        <label class="col-form-label" >District </label>
        <select class="form-control" required wire:model="district_id">
        <option value="">--- Choose District ---</option>  
        @foreach($this->getDistricts() as $district)
            <option value="{{$district->id}}">{{strtoupper($district->name)}}</option>
        @endforeach
        </select>
    </div>
    <div class="form-group" style="displayx: {{ $show_wards }}">
        <label class="col-form-label" >Ward </label>
        <select class="form-control" required value="{{ old('ward_id') }}" name="ward_id">
        <option value="">--- Choose Ward ---</option>  
        @foreach($this->getWards() as $ward)
            <option value="{{$ward->id}}">{{strtoupper($ward->name)}}</option>
        @endforeach
        </select>
    </div>
</div>

