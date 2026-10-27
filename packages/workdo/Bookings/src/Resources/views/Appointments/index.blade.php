@extends('layouts.main')
@section('page-title')
    {{ __('Manage Appointments') }}
@endsection
@section('page-breadcrumb')
    {{ __('Appointments') }}
@endsection
@push('css')
    @include('layouts.includes.datatable-css')
    <link rel="stylesheet" href="{{ asset('packages/workdo/Bookings/src/Resources/assets/css/backend.css') }}">
@endpush
@section('page-action')
    <div>
        <a href="{{ route('bookings.appointments.calendar') }}" class="btn btn-sm btn-primary me-1" data-bs-toggle="tooltip"
            data-bs-original-title="{{ __('Calendar View') }}">
            <i class="ti ti-calendar"></i>
        </a>
        <a href="{{ route('booking-appointments.kanban') }}" class="btn btn-sm btn-primary btn-icon me-1"
            data-bs-toggle="tooltip"title="{{ __('Kanban View') }}">
            <i class="ti ti-layout-kanban"></i>
        </a>
        <a href="#" class="btn btn-sm btn-primary" data-size="lg" data-url="{{ route('booking-appointments.create') }}"
            data-ajax-popup="true" data-title="{{ __('Create Appointments') }}" data-bs-toggle="tooltip"
            data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        {{ $dataTable->table(['width' => '100%']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $('body').on('click','#searchBtn', function() {
            // Get form data
            var date = $('#date').val();
            var service = $('#service').val();
            var customer = $('#customer').val();
            var staff = $('#staff').val();
            var package = $('#package').val();
            var appointment_id = ($('#bookingsappointment_id').val()) ?  $('#bookingsappointment_id').val() : null;
            // Perform AJAX request
            if (date !== '' && service !== '' && customer !== '' && staff !== '' && package !== '') {

                $.ajax({
                    type: 'POST',
                    url: "{{ route('search.appointment') }}",
                    data: {
                        "date": date,
                        "service": service,
                        "customer": customer,
                        "staff": staff,
                        "package": package,
                        "appointment_id": appointment_id,
                        "_token": "{{ csrf_token() }}",

                    },
                    success: function(response) {

                        $('#append_div').empty();
                        $('.error_msg').empty();
                        if (response.result == "success") {
                            $('#append_div').html(response.html);
                        } else {
                            toastrs('Error', response.message, 'error');
                        }
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            } else {
                alert('Please fill in all fields before searching.');
            }
        });
    </script>
@endpush

@push('scripts')
    @include('layouts.includes.datatable-js')
    {{ $dataTable->scripts() }}
@endpush


