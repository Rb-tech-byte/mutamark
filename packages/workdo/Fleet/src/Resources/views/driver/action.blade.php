@if (!empty($driver->id))
    @permission('driver show')
        <div class="action-btn me-2">
            <a class="btn btn-sm align-items-center bg-warning" href="{{ route('driver.show',$driver->id) }}" data-bs-toggle="tooltip" title="{{__('View')}}">
                <i class="ti ti-eye text-white"></i>
            </a>
        </div>
    @endpermission
@endif
@permission('driver edit')
    <div class="action-btn me-2">
        <a class="btn btn-sm  align-items-center bg-info"
            data-url="{{ route('driver.edit', $driver->ID) }}"
            data-ajax-popup="true" data-size="lg" data-bs-toggle="tooltip"
            title="" data-title="{{ __('Edit Driver') }}"
            data-bs-original-title="{{ __('Edit') }}">
            <i class="ti ti-pencil text-white"></i>
        </a>
    </div>
@endpermission

@if (!empty($driver->id))
    @permission('driver delete')
        <div class="action-btn">
            {{ Form::open(['route' => ['driver.destroy', $driver->id], 'class' => 'm-0']) }}
            @method('DELETE')
            <a class="btn btn-sm  align-items-center bs-pass-para show_confirm bg-danger"
                data-bs-toggle="tooltip" title=""
                data-bs-original-title="Delete" aria-label="Delete"
                data-confirm="{{ __('Are You Sure?') }}"
                data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                data-confirm-yes="delete-form-{{ $driver->id }}"><i
                    class="ti ti-trash text-white text-white"></i></a>
            {{ Form::close() }}
        </div>
    @endpermission
@endif
