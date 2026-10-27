@extends('layouts.main')
@section('page-title')
    {{ __('Manage Schedule') }}
@endsection
@section('page-breadcrumb')
    {{ __('Schedule') }}
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
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Starting Time') }}</th>
                                    <th>{{ __('Ending Time') }}</th>
                                    <th>{{ __('Appointments') }}</th>
                                    <th>{{ __('Owner') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    @if (Laratrust::hasPermission('schedule edit') || Laratrust::hasPermission('schedule delete'))
                                        <th width="200px">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            @php
                                $i = 1;
                            @endphp
                            <tbody>
                                @foreach ($schedule as $schedules)
                                    <tr>
                                        @if (!empty($schedules->id))
                                            <td>
                                                <a class="">{{ $i++ }}</a>
                                            </td>
                                        @else
                                            <td>--</td>
                                        @endif
                                        <td>{{ $schedules->name }}</td>
                                        <td>{{ !empty($schedules->date) ? company_date_formate($schedules->date) : '-' }}</td>
                                        <td>{{ !empty($schedules->start_time) ? company_time_formate($schedules->start_time) : '-' }}</td>
                                        <td>{{ !empty($schedules->end_time) ? company_time_formate($schedules->end_time) : '-' }}</td>
                                        <td>{{ $schedules->appointment->name }}</td>
                                        <td>{{ $schedules->creatorName->name }}</td>
                                        <td>
                                            @if ($schedules->status == 'Pending')
                                                <div class="badge bg-warning p-2 px-3 rounded status-badge5">
                                                    {{ $schedules->status }}</div>
                                            @elseif($schedules->status == 'Approved')
                                                <div class="badge bg-success p-2 px-3 rounded status-badge5">
                                                    {{ $schedules->status }}</div>
                                            @elseif($schedules->status == 'Complete')
                                                <div class="badge bg-info p-2 px-3 rounded status-badge5">
                                                    {{ $schedules->status }}</div>
                                            @else
                                                <div class="badge bg-danger p-2 px-3 rounded status-badge5">
                                                    {{ $schedules->status }}</div>
                                            @endif
                                        </td>
                                        @if (Laratrust::hasPermission('schedule edit') || Laratrust::hasPermission('schedule delete') || Laratrust::hasPermission('schedule action'))
                                            <td class="Action">
                                                <span>
                                                    @permission('schedule show')
                                                        @if ($schedules->status == 'Approved')
                                                            <div class="action-btn bg-dark ms-2">
                                                                <a data-url="{{ URL::to('schedules/' . \Crypt::encrypt($schedules->id) . '/action') }}"
                                                                    data-size="md" data-ajax-popup="true"
                                                                    data-bs-toggle="tooltip"data-title="{{ __('Manage Schedule Details') }}"
                                                                    title="{{ __('Schedule Status Confirm') }}"
                                                                    class="mx-3 btn btn-sm  align-items-center">
                                                                    <i class="ti ti-eye text-white"></i>
                                                                </a>
                                                            </div>
                                                        @else
                                                            <div class="action-btn bg-primary ms-2">
                                                                <a data-url="{{ URL::to('schedules/' . \Crypt::encrypt($schedules->id) . '/action') }}"
                                                                    data-size="md" data-ajax-popup="true"
                                                                    data-bs-toggle="tooltip"data-title="{{ __('Manage Schedule Details') }}"
                                                                    title="{{ __('Schedule Action') }}"
                                                                    class="mx-3 btn btn-sm  align-items-center">
                                                                    <i class="ti ti-caret-right text-white"></i>
                                                                </a>
                                                            </div>
                                                        @endif
                                                    @endpermission
                                                    @if ($schedules->status == 'Pending')
                                                        @if (!empty($schedules->id))
                                                            @permission('schedule delete')
                                                                <div class="action-btn bg-danger ms-2">
                                                                    {{ Form::open(['route' => ['schedules.destroy', \Crypt::encrypt($schedules->id)], 'class' => 'm-0']) }}
                                                                    @method('DELETE')
                                                                    <a class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm"
                                                                        data-bs-toggle="tooltip" title=""
                                                                        data-bs-original-title="Delete" aria-label="Delete"
                                                                        data-confirm="{{ __('Are You Sure?') }}"
                                                                        data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                        data-confirm-yes="delete-form-{{ $schedules->id }}"><i
                                                                            class="ti ti-trash text-white text-white"></i></a>

                                                                    {{ Form::close() }}
                                                                </div>
                                                            @endpermission
                                                        @endif
                                                    @endif
                                                </span>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                                @foreach ($callbacks as $callback)
                                    <tr>
                                        @if (!empty($callback->id))
                                            <td>
                                                <a class="">{{ $i++ }}</a>
                                            </td>
                                        @else
                                            <td>--</td>
                                        @endif
                                        <td>{{ !empty($callback->schedule) ? $callback->schedule->name : '' }}</td>
                                        <td>{{ company_date_formate($callback->date) }}</td>
                                        <td>{{ company_time_formate($callback->start_time) }}</td>
                                        <td>{{ company_time_formate($callback->end_time) }}</td>
                                        <td>{{ $callback->appointment->name }}</td>
                                        <td>{{ $callback->creatorName->name }}</td>
                                        <td>
                                            @if ($callback->status == 'Pending')
                                                <div class="badge bg-warning p-2 px-3 rounded status-badge5">
                                                    {{ $callback->status }}</div>
                                            @elseif($callback->status == 'Approved')
                                                <div class="badge bg-success p-2 px-3 rounded status-badge5">
                                                    {{ $callback->status }}</div>
                                            @elseif($callback->status == 'Complete')
                                                <div class="badge bg-info p-2 px-3 rounded status-badge5">
                                                    {{ $callback->status }}</div>
                                            @else
                                                <div class="badge bg-danger p-2 px-3 rounded status-badge5">
                                                    {{ $callback->status }}</div>
                                            @endif
                                        </td>
                                        @if (Laratrust::hasPermission('schedule edit') || Laratrust::hasPermission('schedule delete') || Laratrust::hasPermission('schedule action'))
                                            <td class="Action">
                                                <span>
                                                    @permission('schedule show')
                                                        @if ($callback->status == 'Approved')
                                                            <div class="action-btn bg-dark ms-2">
                                                                <a data-url="{{ URL::to('callbacks/' . \Crypt::encrypt($callback->id) . '/action') }}"
                                                                    data-size="md" data-ajax-popup="true"
                                                                    data-bs-toggle="tooltip"data-title="{{ __('Manage Schedule Details') }}"
                                                                    title="{{ __('Schedule Status Confirm') }}"
                                                                    class="mx-3 btn btn-sm  align-items-center">
                                                                    <i class="ti ti-eye text-white"></i>
                                                                </a>
                                                            </div>
                                                        @else
                                                            <div class="action-btn bg-primary ms-2">
                                                                <a data-url="{{ URL::to('callbacks/' . \Crypt::encrypt($callback->id) . '/action') }}"
                                                                    data-size="md" data-ajax-popup="true"
                                                                    data-bs-toggle="tooltip"data-title="{{ __('Manage Schedule Details') }}"
                                                                    title="{{ __('Schedule Action') }}"
                                                                    class="mx-3 btn btn-sm  align-items-center">
                                                                    <i class="ti ti-caret-right text-white"></i>
                                                                </a>
                                                            </div>
                                                        @endif
                                                    @endpermission
                                                    @if ($callback->status == 'Pending')
                                                        @if (!empty($callback->id))
                                                            @permission('schedule delete')
                                                                <div class="action-btn bg-danger ms-2">
                                                                    {{ Form::open(['route' => ['callback.destroy', \Crypt::encrypt($callback->id)], 'class' => 'm-0']) }}
                                                                    @method('DELETE')
                                                                    <a class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm"
                                                                        data-bs-toggle="tooltip" title=""
                                                                        data-bs-original-title="Delete" aria-label="Delete"
                                                                        data-confirm="{{ __('Are You Sure?') }}"
                                                                        data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                        data-confirm-yes="delete-form-{{ $callback->id }}"><i
                                                                            class="ti ti-trash text-white text-white"></i></a>

                                                                    {{ Form::close() }}
                                                                </div>
                                                            @endpermission
                                                        @endif
                                                    @endif
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
