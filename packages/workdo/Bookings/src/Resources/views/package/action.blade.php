@permission('bookings package duplicate')
    <div class="action-btn me-2">
        {!! Form::open([
            'method' => 'get',
            'route' => ['booking-package.duplicate', $bookingspackage->id],
            'id' => 'duplicate-form-' . $bookingspackage->id,
        ]) !!}
        <a href="#"
            class="bg-secondary btn btn-sm  align-items-center bs-pass-para show_confirm"
            data-bs-toggle="tooltip" title=""
            data-bs-original-title="{{ __('Duplicate') }}"
            aria-label="Delete"
            data-text="{{ __('You want to confirm duplicate this package. Press Yes to continue or Cancel to go back') }}"
            data-confirm-yes="duplicate-form-{{ $bookingspackage->id }}">
            <i class="ti ti-copy  text-white"></i>
        </a>
        {{ Form::close() }}
    </div>
@endpermission
@permission('bookings package edit')
    <div class="action-btn me-2">
        <a data-url="{{ route('booking-package.edit', $bookingspackage->id) }}"
            class="bg-info btn btn-sm align-items-center text-white"
            data-bs-toggle="tooltip" data-ajax-popup="true" data-size="lg"
            data-title="{{ __('Edit Package') }}"title="{{ __('Edit') }}"><i
                class="ti ti-pencil"></i></a>
    </div>
@endpermission
@permission('bookings package delete')
    <div class="action-btn">
        {!! Form::open(['method' => 'DELETE', 'route' => ['booking-package.destroy', $bookingspackage->id]]) !!}
        <a href="#!"
            class="bg-danger btn btn-sm  align-items-center text-white show_confirm"
            data-bs-toggle="tooltip" title='Delete'>
            <i class="ti ti-trash"></i>
        </a>
        {!! Form::close() !!}
    </div>
@endpermission
