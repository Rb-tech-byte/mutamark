@permission('fleet logbook show')
    <div class="action-btn me-2">
        <a class="btn btn-sm  align-items-center bg-warning"
            data-url="{{ route('logbook.show', $logbook->id) }}"
            data-ajax-popup="true" data-size="lg" data-bs-toggle="tooltip"
            title="" data-title="{{ __('View Log Book') }}"
            data-bs-original-title="{{ __('View') }}">
            <i class="ti ti-eye text-white"></i>
        </a>
    </div>
@endpermission
@permission('fleet logbook edit')
    <div class="action-btn me-2">
        <a class="btn btn-sm  align-items-center bg-info"
            data-url="{{ route('logbook.edit', $logbook->id) }}"
            data-ajax-popup="true" data-size="lg" data-bs-toggle="tooltip"
            title="" data-title="{{ __('Edit Log Book') }}"
            data-bs-original-title="{{ __('Edit') }}">
            <i class="ti ti-pencil text-white"></i>
        </a>
    </div>
@endpermission
@permission('fleet logbook delete')
    <div class="action-btn">
        {{ Form::open(['route' => ['logbook.destroy', $logbook->id], 'class' => 'm-0']) }}
        @method('DELETE')
        <a class="btn btn-sm  align-items-center bs-pass-para show_confirm bg-danger"
            data-bs-toggle="tooltip" title=""
            data-bs-original-title="Delete" aria-label="Delete"
            data-confirm="{{ __('Are You Sure?') }}"
            data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
            data-confirm-yes="delete-form-{{ $logbook->id }}"><i
                class="ti ti-trash text-white text-white"></i></a>
        {{ Form::close() }}
    </div>
@endpermission
