@permission('bookingcustomers edit')
    <div class="action-btn me-2">
        <a class="bg-info btn btn-sm  align-items-center"
            data-url="{{ route('booking-customers.edit', $booking->id) }}"
            data-ajax-popup="true" data-size="lg" data-bs-toggle="tooltip"
            title="" data-title="{{ __('Edit Customer') }}"
            data-bs-original-title="{{ __('Edit') }}">
            <i class="ti ti-pencil text-white"></i>
        </a>
    </div>
@endpermission
@permission('bookingcustomers delete')
    <div class="action-btn">
        {{ Form::open(['route' => ['booking-customers.destroy', $booking->id], 'class' => 'm-0']) }}
        @method('DELETE')
        <a class="bg-danger btn btn-sm  align-items-center bs-pass-para show_confirm"
            data-bs-toggle="tooltip" title=""
            data-bs-original-title="Delete" aria-label="Delete"
            data-confirm="{{ __('Are You Sure?') }}"
            data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
            data-confirm-yes="delete-form-{{ $booking->id }}"><i
                class="ti ti-trash text-white text-white"></i></a>
        {{ Form::close() }}
    </div>
@endpermission
