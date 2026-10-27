@extends('layouts.main')
@section('page-title')
    {{ __('Manage Appointment') }}
@endsection
@section('page-breadcrumb')
    {{ __('Appointment') }}
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            $('.cp_link').on('click', function() {
                var value = $(this).attr('data-link');
                var $temp = $("<input>");
                $("body").append($temp);
                $temp.val(value).select();
                document.execCommand("copy");
                $temp.remove();
                toastrs('Success', '{{ __('Link Copy on Clipboard') }}', 'success')
            });
        });
    </script>
@endpush
@section('page-action')
    <div>
        <a href="{{ route('appointments.calender') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
            data-bs-original-title="{{ __('Calendar View') }}">
            <i class="ti ti-calendar"></i>
        </a>
        @permission('appointments create')
            <a data-url="{{ route('appointments.create') }}" data-size="lg" data-ajax-popup="true"
                data-bs-toggle="tooltip"data-title="{{ __('Create New Appointment') }}" title="{{ __('Create') }}"
                class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endpermission
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0 pc-dt-simple" id="assets">
                            <thead>
                                <tr>
                                    <th>{{ __('#') }}</th>
                                    <th>{{ __('Appointment Name') }}</th>
                                    <th>{{ __('Owner') }}</th>
                                    <th>{{ __('Appointment Type') }}</th>
                                    <th>{{ __('Enabled') }}</th>
                                    @if (Laratrust::hasPermission('appointments edit') ||
                                            Laratrust::hasPermission('appointments delete') ||
                                            Laratrust::hasPermission('appointments show') ||
                                            Laratrust::hasPermission('appointments copy link') ||
                                            Laratrust::hasPermission('schedule show'))
                                        <th width="200px">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            @php
                                $i = 1;
                            @endphp
                            <tbody>
                                @foreach ($appointment as $appointments)
                                    <tr>
                                        @if (!empty($appointments->id))
                                            <td>
                                                <a class="">{{ $i++ }}</a>
                                            </td>
                                        @else
                                            <td>--</td>
                                        @endif
                                        <td>{{ $appointments->name }}</td>
                                        <td>{{ $appointments->creatorName->name }}</td>
                                        <td>{{ $appointments->appointment_type }}</td>
                                        <td>{{ $appointments->is_enabled == 'on' ? 'Yes' : 'No' }}</td>
                                        @if (Laratrust::hasPermission('appointments edit') ||
                                                Laratrust::hasPermission('appointments delete') ||
                                                Laratrust::hasPermission('appointments show') ||
                                                Laratrust::hasPermission('appointments copy link') ||
                                                Laratrust::hasPermission('schedule show'))
                                            <td class="Action">
                                                <span>
                                                    @permission('schedule show')
                                                        <div class="action-btn bg-black ms-2">
                                                            <a href="{{ route('schedules.show', \Crypt::encrypt($appointments->id)) }}"
                                                                data-title="{{ __('Schedule Details') }}"
                                                                data-bs-toggle="tooltip" title=""
                                                                class="mx-3 btn btn-sm align-items-center"
                                                                data-bs-original-title="{{ __('Schedule Show') }}">
                                                                <i class="ti ti-eye text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endpermission
                                                    @permission('appointments copy link')
                                                        <div class="action-btn bg-primary ms-2">
                                                            <a data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="{{ __('Click to copy link') }}"
                                                                class="mx-3 btn btn-sm d-inline-flex align-items-center cp_link"
                                                                data-link="{{ route('appointments', [$workspace->slug, \Crypt::encrypt($appointments->id)]) }}"data-toggle="tooltip"
                                                                data-original-title="{{ __('Click to copy link') }}"><i
                                                                    class="ti ti-link text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endpermission
                                                    @permission('appointments show')
                                                        <div class="action-btn bg-warning ms-2">
                                                            <a data-url="{{ route('appointments.show', $appointments->id) }}"
                                                                data-size="lg" data-ajax-popup="true" data-bs-toggle="tooltip"
                                                                data-title="{{ __('Appointment Details') }}"title="{{ __('Appointment Show') }}"
                                                                class="mx-3 btn btn-sm  align-items-center">
                                                                <i class="ti ti-eye text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endpermission
                                                    @permission('appointments edit')
                                                        <div class="action-btn bg-info ms-2">
                                                            <a data-url="{{ route('appointments.edit', $appointments->id) }}"
                                                                data-size="lg" data-ajax-popup="true"
                                                                data-bs-toggle="tooltip"data-title="{{ __('Update Appointment') }}"
                                                                title="{{ __('Update') }}"
                                                                class="mx-3 btn btn-sm  align-items-center">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endpermission
                                                    @permission('appointments delete')
                                                        <div class="action-btn bg-danger ms-2">
                                                            {{ Form::open(['route' => ['appointments.destroy', $appointments->id], 'class' => 'm-0']) }}
                                                            @method('DELETE')
                                                            <a class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-bs-original-title="Delete" aria-label="Delete"
                                                                data-confirm="{{ __('Are You Sure?') }}"
                                                                data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="delete-form-{{ $appointments->id }}"><i
                                                                    class="ti ti-trash text-white text-white"></i></a>
                                                            {{ Form::close() }}
                                                        </div>
                                                    @endpermission
                                                </span>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
