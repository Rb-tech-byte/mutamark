@permission('vehicle show')
    <div class="action-btn me-2">
        <a class="btn btn-sm align-items-center bg-warning" href="{{ route('vehicle.show',$vehicle->id) }}" data-bs-toggle="tooltip" title="{{__('View')}}">
            <i class="ti ti-eye text-white"></i>
        </a>
    </div>
@endpermission
@permission('vehicle edit')
    <div class="action-btn me-2">
        <a class="btn btn-sm  align-items-center bg-info"
            data-url="{{ route('vehicle.edit', $vehicle->id) }}"
            data-ajax-popup="true" data-size="lg" data-bs-toggle="tooltip"
            title="" data-title="{{ __('Edit vehicle') }}"
            data-bs-original-title="{{ __('Edit') }}">
            <i class="ti ti-pencil text-white"></i>
        </a>
    </div>
@endpermission
@permission('vehicle delete')
    <div class="action-btn">
        {{ Form::open(['route' => ['vehicle.destroy', $vehicle->id], 'class' => 'm-0']) }}
        @method('DELETE')
        <a class="btn btn-sm  align-items-center bs-pass-para show_confirm bg-danger"
            data-bs-toggle="tooltip" title=""
            data-bs-original-title="Delete" aria-label="Delete"
            data-confirm="{{ __('Are You Sure?') }}"
            data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
            data-confirm-yes="delete-form-{{ $vehicle->id }}"><i
                class="ti ti-trash text-white text-white"></i></a>
        {{ Form::close() }}
    </div>
@endpermission
