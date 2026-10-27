@extends('layouts.main')
@section('page-title')
    {{ __('Manage Appointments') }}
@endsection
@section('page-breadcrumb')
    {{ __('Appointments') }}
@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('packages/workdo/Bookings/src/Resources/assets/css/backend.css') }}">
@endpush
@push('scripts')
    <script src="{{ asset('assets/js/plugins/dropzone-amd-module.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/dragula.min.js') }}"></script>
    <link href="{{ asset('assets/css/plugins/dragula.min.css') }}" rel="stylesheet" />

    <script>
        !function (a) {
            "use strict";
            var t = function () {
                this.$body = a("body")
            };
            t.prototype.init = function () {
                a('[data-plugin="dragula"]').each(function () {
                    var t = a(this).data("containers"), n = [];
                    if (t) for (var i = 0; i < t.length; i++) n.push(a("#" + t[i])[0]); else n = [a(this)[0]];
                    var r = a(this).data("handleclass");
                    r ? dragula(n, {
                        moves: function (a, t, n) {
                            return n.classList.contains(r)
                        }
                    }) : dragula(n).on('drop', function (el, target, source, sibling) {


                        var order = [];
                        $("#" + target.id + " > div").each(function () {
                            order[$(this).index()] = $(this).attr('data-id');
                        });

                        var id = $(el).attr('data-id');
                        var stage_id = $(target).attr('data-id');

                        $("#" + source.id).parent().find('.count').text($("#" + source.id + " > div").length);
                        $("#" + target.id).parent().find('.count').text($("#" + target.id + " > div").length);


                        $.ajax({
                            url: '{{route('bookings.appointments.stage.order')}}',
                            type: 'POST',
                            data: {booking_id: id, stage_id: stage_id, _token: "{{ csrf_token() }}"},
                            success: function (data) {
                                toastrs('{{__("Success")}}', 'Appointment Move Successfully!', 'success');
                            },
                            error: function (data) {
                                data = data.responseJSON;
                                toastrs('Error', data.error, 'error')
                            }
                        });
                    });
                })
            }, a.Dragula = new t, a.Dragula.Constructor = t
        }(window.jQuery), function (a) {
            "use strict";

            a.Dragula.init()

        }(window.jQuery);
    </script>
@endpush
@section('page-action')
    <div class="">
        <a href="{{ route('bookings.appointments.calendar') }}" class="btn btn-sm btn-primary me-1" data-bs-toggle="tooltip"
            data-bs-original-title="{{ __('Calendar View') }}">
            <i class="ti ti-calendar"></i>
        </a>
        <a href="{{ route('booking-appointments.index') }}" class="btn btn-sm btn-primary btn-icon me-1"
            data-bs-toggle="tooltip"title="{{ __('list View') }}">
            <i class="ti ti-list text-white"></i>
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
    <div class="card overflow-hidden mt-0">
        <div class="container-kanban">
            <div class="row kanban-wrapper horizontal-scroll-cards" data-plugin="dragula"
                data-containers='["booking-0", "booking-1", "booking-2", "booking-3", "booking-4"]'>
                @foreach ($stages as $key => $stage)
                    <div class="col">
                        <div class="card">
                            <div class="card-header">
                                <div class="float-end">
                                    @if (isset($appointments[$key]))
                                        <span class="btn btn-sm btn-primary btn-icon count">{{ count($appointments[$key]) }}</span>
                                    @else
                                        <span class="btn btn-sm btn-primary btn-icon count">0</span>
                                    @endif
                                </div>
                                <h4 class="mb-0">{{ $stage }}</h4>
                            </div>
                            <div class="card-body kanban-box" data-id="{{ $key }}" id="booking-{{ $key }}">
                                @if (array_key_exists($key, $appointments))
                                    @foreach ($appointments[$key] as $appointment)
                                        <div class="card grid-card" data-id="{{ $appointment['id'] }}">
                                            <div class="card-header d-flex align-items-center justify-content-between gap-2 border-0 pb-0">
                                                <h5> {{ Workdo\Bookings\Entities\BookingsAppointment::appointmentNumberFormat($appointment['appointment_id']) }}</h5>
                                                <div class="card-header-right">
                                                    <div class="btn-group card-option">
                                                        <button type="button" class="btn dropdown-toggle"
                                                            data-bs-toggle="dropdown" aria-haspopup="true"
                                                            aria-expanded="false">
                                                            <i class="feather icon-more-vertical"></i>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-end">
                                                            @permission('bookings appointments show')
                                                                <a data-url="{{ route('booking-appointments.show', $appointment['id']) }}"
                                                                data-ajax-popup="true" data-size="lg" data-title="{{ __('Appointments Details') }}"
                                                                class="dropdown-item" ><i class="ti ti-eye"></i><span
                                                                class="ms-2">{{ __('View') }}</span></a>
                                                            @endpermission

                                                            @permission('bookings appointments edit')
                                                                <a data-url="{{ route('booking-appointments.edit', $appointment['id']) }}"
                                                                data-ajax-popup="true" data-size="lg" data-title="{{ __('Edit Appointments') }}"
                                                                class="dropdown-item" ><i class="ti ti-pencil "></i><span
                                                                class="ms-2">{{ __('Edit') }}</span></a>
                                                            @endpermission

                                                            @permission('bookings appointments delete')
                                                                {{-- @if ($application->is_archive == 0) --}}
                                                                    {!! Form::open([
                                                                        'method' => 'DELETE',
                                                                        'route' => ['booking-appointments.destroy', $appointment['id']],
                                                                        'id' => 'delete-form-' . $appointment['id'],
                                                                    ]) !!}
                                                                    <a class="bs-pass-para dropdown-item show_confirm text-danger"
                                                                        data-bs-toggle="tooltip" data-bs-placement="top"><i
                                                                            class="ti ti-trash"></i><span
                                                                            class="ms-2">{{ __('Delete') }}</span></a>
                                                                    {!! Form::close() !!}
                                                                {{-- @endif --}}
                                                            @endpermission
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <ul class="list-inline mb-0 mt-0">
                                                        <div class="row align-items-center">
                                                            <div class="col-md-12">
                                                                <span class="d-block">{{ $appointment['appointment_staff']['name'] ?? '-' }}</span>
                                                                    <span class="d-block mt-2">{{ isset($appointment['customer_data']) ? (isset($appointment['customer_data']['client']) ? $appointment['customer_data']['client']['name'] : $appointment['customer_data']['name'] ) : '-' }}</span>
                                                                <span class="d-block mt-2">{{ $appointment['appointment_service']['name'] ?? '-' }}</span>
                                                            </div>
                                                        </div>
                                                    </ul>

                                                    <div class="avatar-group hover-avatar-ungroup">
                                                        <span class="d-block">{{  $appointment['date'] ?? '-' }}</span>
                                                        <span class="d-block mt-2">{{ isset($appointment['start_time']) ? $appointment['start_time'] : '-'}} - {{isset ($appointment['end_time']) ? $appointment['end_time'] : '-'}}</span>
                                                        <span class="d-block mt-2">{{  $appointment['payment'] ?? '-' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <span class="empty-container" data-placeholder="Empty"></span>
                        </div>
                    </div>
                @endforeach
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

