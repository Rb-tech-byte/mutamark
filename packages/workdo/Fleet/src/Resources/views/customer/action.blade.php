@permission('fleet customer edit')
    <div class="action-btn me-2">
        <a class="btn btn-sm  align-items-center bg-info"
            data-url="{{ route('fleet_customer.edit', $customer->id) }}"
            data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
            title="" data-title="{{ __('Edit Customer') }}"
            data-bs-original-title="{{ __('Edit') }}">
            <i class="ti ti-pencil text-white"></i>
        </a>
    </div>
@endpermission

@php
    $user = \App\Models\User::where('id', $customer->user_id)->first();
@endphp
@permission('fleet customer delete')
    <div class="action-btn">
        {{ Form::open(['route' => ['fleet_customer.destroy', $customer->id], 'class' => 'm-0']) }}
        @method('DELETE')
        <a class="btn btn-sm  align-items-center bs-pass-para show_confirm bg-danger"
            data-bs-toggle="tooltip" title=""
            data-bs-original-title="Delete" aria-label="Delete"
            data-confirm="{{ __('Are You Sure?') }}"
            data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
            data-confirm-yes="delete-form-{{ $customer->id }}"><i
                class="ti ti-trash text-white text-white"></i></a>
        {{ Form::close() }}
    </div>
@endpermission
