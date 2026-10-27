@permission('bookings appointments show')
    <div class="action-btn me-2">
        <a class="bg-warning btn btn-sm  align-items-center"
            data-url="{{ route('booking-appointments.show', $appointment->id) }}"
            data-ajax-popup="true" data-size="lg" data-bs-toggle="tooltip"
            title="" data-title="{{ __('Appointments Details') }}"
            data-bs-original-title="{{ __('View') }}">
            <i class="ti ti-eye text-white"></i>
        </a>
    </div>
@endpermission
@permission('bookings appointments edit')
    <div class="action-btn me-2">
        <a class="bg-info btn btn-sm  align-items-center"
            data-url="{{ route('booking-appointments.edit', $appointment->id) }}"
            data-ajax-popup="true" data-size="lg" data-bs-toggle="tooltip"
            title="" data-title="{{ __('Edit Appointments') }}"
            data-bs-original-title="{{ __('Edit') }}">
            <i class="ti ti-pencil text-white"></i>
        </a>
    </div>
@endpermission
@permission('bookings appointments delete')
    <div class="action-btn me-2">
        {{ Form::open(['route' => ['booking-appointments.destroy', $appointment->id], 'class' => 'm-0']) }}
        @method('DELETE')
        <a class="bg-danger btn btn-sm  align-items-center bs-pass-para show_confirm"
            data-bs-toggle="tooltip" title=""
            data-bs-original-title="Delete" aria-label="Delete"
            data-confirm="{{ __('Are You Sure?') }}"
            data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
            data-confirm-yes="delete-form-{{ $appointment->id }}"><i
                class="ti ti-trash text-white text-white"></i></a>
        {{ Form::close() }}
    </div>
@endpermission
