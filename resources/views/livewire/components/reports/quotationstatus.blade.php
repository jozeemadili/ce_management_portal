
<div class="card">
    <div class="card-header">
      <div class="header-top d-sm-flex align-items-center">
        <h5>Applications Status</h5>
      </div>
    </div>
    <div class="card-body p-0">
        <figure class="highcharts-figure">
            <div id="container_quotation_status"></div>
        </figure>
        
    </div>
  </div>

@push('css')
<style>
    #container_quotation_status
    {
        width: 100%;
    }
</style>
@endpush

@push('scripts')
<script>
Highcharts.chart('container_quotation_status', 
{
chart: 
{
    type: 'pie',
    options3d: 
    {
        enabled: true,
        alpha: 45
    }
},
title: 
{
    text: '',
    align: 'left'
},
subtitle: 
{
    text: '',
    align: 'left'
},
plotOptions: 
{
    pie: {
        innerSize: 100,
        depth: 50
    }
},
series: [{
    name: 'Customers',
    data: {!! json_encode($products_performance) !!}
}]
});

</script>
@endpush


