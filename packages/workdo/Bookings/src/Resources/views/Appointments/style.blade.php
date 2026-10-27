@push('scripts')
@if(isset($stripe_session) && isset($package))

    <script src="https://js.stripe.com/v3/"></script>
    <script>
    var stripe = Stripe("{{ company_setting('stripe_key', $package->created_by, $package->wokspace) }}");
    stripe.redirectToCheckout({
        sessionId: '{{ $stripe_session->id }}',
    }).then((result) => {
    });
    </script>
@endif

