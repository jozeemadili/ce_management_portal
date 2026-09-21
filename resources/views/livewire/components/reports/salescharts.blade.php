<div class="card">
  <div class="card-header">
    <div class="header-top d-sm-flex align-items-center">
      <h5>Payment Trends</h5>
    </div>
  </div>
  <div class="card-body p-0">
    <figure class="highcharts-figure">
        <div id="container_sales"></div>
    </figure>
  </div>
</div>

@push('css')
<style>
#container_sales
{
    height: 39vh;
}

.highcharts-figure,
.highcharts-data-table table {
    min-width: 510px;
    max-width: 900px;
    margin: 1em auto;
}

.highcharts-data-table table {
    font-family: Verdana, sans-serif;
    border-collapse: collapse;
    border: 1px solid #ebebeb;
    margin: 10px auto;
    text-align: center;
    width: 100%;
    max-width: 500px;
}

.highcharts-data-table caption {
    padding: 1em 0;
    font-size: 1.2em;
    color: #555;
}

.highcharts-data-table th {
    font-weight: 600;
    padding: 0.5em;
}

.highcharts-data-table td,
.highcharts-data-table th,
.highcharts-data-table caption {
    padding: 0.5em;
}

.highcharts-data-table thead tr,
.highcharts-data-table tr:nth-child(even) {
    background: #f8f8f8;
}

.highcharts-data-table tr:hover {
    background: #f1f7ff;
}

</style>
@endpush

@push('scripts')
<script src="{{asset('assets/js/highcharts/highcharts.js')}}"></script>
<script src="{{asset('assets/js/highcharts/highcharts-3d.js')}}"></script>
<script src="{{asset('assets/js/highcharts/exporting.js')}}"></script>
<script src="{{asset('assets/js/highcharts/accessibility.js')}}"></script>

<script>
Highcharts.chart('container_sales', 
{
    chart: 
    {
        type: 'column',
        options3d: {
            enabled: true,
            alpha: 10,
            beta: 25,
            depth: 70
        }
    },
    title: {
        text: '',
        align: 'left'
    },
    subtitle: {
        text: '',
        align: 'left'
    },
    plotOptions: {
        column: {
            depth: 25
        }
    },
    xAxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul','Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        labels: {
            skew3d: true,
            style: {
                fontSize: '16px'
            }
        }
    },
    yAxis: {
        title: {
            text: 'TZS',
            margin: 20
        }
    },
    tooltip: {
        valueSuffix: 'TZS'
    },
    series: [{
        name: 'Total Sales',
        data: {{ json_encode($sales)  }}
    }]
});

</script>
@endpush


