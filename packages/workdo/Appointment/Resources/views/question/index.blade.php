@extends('layouts.main')
@section('page-title')
    {{ __('Manage Question') }}
@endsection
@section('page-breadcrumb')
    {{ __('Question') }}
@endsection
@section('page-action')
    <div>
        @permission('question create')
            <a data-url="{{ route('questions.create') }}" data-size="lg" data-ajax-popup="true"
                data-bs-toggle="tooltip"data-title="{{ __('Create New Question') }}"title="{{ __('Create') }}"
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
                                    <th>{{ __('Question') }}</th>
                                    <th>{{ __('Question Type') }}</th>
                                    <th>{{ __('Required Answer') }}</th>
                                    <th>{{ __('Enabled') }}</th>
                                    @if (Laratrust::hasPermission('question edit') || Laratrust::hasPermission('question delete'))
                                        <th width="200px">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            @php
                                $i = 1;
                            @endphp
                            <tbody>
                                @foreach ($question as $questions)
                                    <tr>
                                        @if (!empty($questions->id))
                                            <td>
                                                <a class="">{{ $i++ }}</a>
                                            </td>
                                        @else
                                            <td>--</td>
                                        @endif
                                        <td>{{ $questions->question }}</td>
                                        <td>{{ $questions->question_type }}</td>
                                        <td>{{ $questions->is_required == 'on' ? 'Yes' : 'No' }}</td>
                                        <td>{{ $questions->is_enabled == 'on' ? 'Yes' : 'No' }}</td>
                                        @if (Laratrust::hasPermission('question edit') || Laratrust::hasPermission('question delete'))
                                            <td class="Action">

                                                <span>
                                                    @permission('question edit')
                                                        <div class="action-btn bg-info ms-2">
                                                            <a data-url="{{ route('questions.edit', $questions->id) }}"
                                                                data-size="lg" data-ajax-popup="true"
                                                                data-bs-toggle="tooltip"data-title="{{ __('Update Questions') }}"title="{{ __('Update') }}"
                                                                class="mx-3 btn btn-sm  align-items-center">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endpermission
                                                    @permission('question delete')
                                                        <div class="action-btn bg-danger ms-2">
                                                            {{ Form::open(['route' => ['questions.destroy', $questions->id], 'class' => 'm-0']) }}
                                                            @method('DELETE')
                                                            <a class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-bs-original-title="Delete" aria-label="Delete"
                                                                data-confirm="{{ __('Are You Sure?') }}"
                                                                data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="delete-form-{{ $questions->id }}"><i
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
