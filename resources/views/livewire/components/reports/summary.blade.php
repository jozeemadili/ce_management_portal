<div class="row">

  {{-- Total Invoiced --}}
  <div class="col-lg-4">
      <a href="#">
          <div class="card income-card card-secondary">
              <br />
              <div class="card-body text-center">
                  <div class="round-box">
                      <i class="icofont icofont-chart-bar-graph" style="font-size: 40px;"></i>
                  </div>
                  <h5>{{ $summary['total_invoiced'] }}</h5>
                  <p>Total Invoiced Amount</p>
              </div><br />
          </div>
      </a>
  </div>

  {{-- Paid Invoices --}}
  <div class="col-lg-4">
      <a href="#">
          <div class="card income-card card-primary">
              <br />
              <div class="card-body text-center">
                  <div class="round-box">
                      <i class="icofont icofont-tick-mark" style="font-size: 40px;"></i>
                  </div>
                  <h5>{{ $summary['total_paid'] }}</h5>
                  <p>Total Paid Amount</p>
              </div><br />
          </div>
      </a>
  </div>

  {{-- Unpaid Invoices --}}
  <div class="col-lg-4">
      <a href="#">
          <div class="card income-card card-danger">
              <br />
              <div class="card-body text-center">
                  <div class="round-box">
                      <i class="icofont icofont-warning-alt" style="font-size: 40px;"></i>
                  </div>
                  <h5>{{ $summary['total_unpaid'] }}</h5>
                  <p>Total Unpaid Amount</p>
              </div><br />
          </div>
      </a>
  </div>

</div>
