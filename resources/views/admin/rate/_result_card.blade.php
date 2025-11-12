<div class="card mt-4 shadow-sm">
  <div class="card-body">
    <h6>{{ $calc->from_location }} → {{ $calc->to_location }}</h6>
    <p>Distance: {{ number_format($calc->distance_km, 2) }} km</p>

    <hr>

    <div class="row">
      <div class="col-md-3"><strong>Base:</strong> ₹{{ number_format($calc->breakdown['base'], 2) }}</div>
      <div class="col-md-3"><strong>Extras:</strong> ₹{{ number_format($calc->breakdown['extras'], 2) }}</div>
      <div class="col-md-3"><strong>Tax:</strong> ₹{{ number_format($calc->breakdown['tax'], 2) }}</div>
      <div class="col-md-3"><strong>Total:</strong> ₹{{ number_format($calc->total_amount, 2) }}</div>
    </div>
  </div>
</div>
