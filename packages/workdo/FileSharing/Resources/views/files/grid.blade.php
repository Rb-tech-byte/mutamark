@extends('layouts.main')
@section('page-title')
    {{ __('Files') }}
@endsection
@section('page-breadcrumb')
    {{ __('Manage Files') }}
@endsection
@section('page-action')
    <div>
        <a href="{{ route('files.index') }}" class="btn btn-sm btn-primary btn-icon" data-bs-toggle="tooltip"
            title="{{ __('List View') }}">
            <i class="ti ti-list text-white"></i>
        </a>
        @permission('files create')
            <a class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="md" data-title="{{ __('Create New Article') }}"
                data-url="{{ route('files.create') }}" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}">
                <i class="ti ti-plus"></i>
            </a>
        @endpermission
    </div>
@endsection
@push('css')
    <link href="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="raw mt-3">
        <section class="section">
            <div class="row  d-flex grid">
                @foreach ($files as $file)
                    @php
                        $user_id = explode(',', $file->user_id);
                        $users = App\Models\User::whereIn('id', $user_id)->get();
                        $filename = basename($file->file_path);
                    @endphp
                    <div class="col-md-6 col-xl-3 All">
                        <div class="card">
                            <div class="card-header border-0 pb-0">
                                <div class="d-flex align-items-center">
                                    <a href="#" class="text-white">
                                        @if ($file->file_status == 'Available')
                                            <span class="badge bg-primary p-2 px-3 rounded"
                                                style="width: 90px;">{{ __($file->file_status) }}</span>
                                        @elseif($file->file_status == 'Not Available')
                                            <span class="badge bg-danger p-2 px-3 rounded"
                                                style="width: 90px;">{{ __($file->file_status) }}</span>
                                        @endif
                                    </a>
                                </div><br>
                                <div class="card-header-right">
                                    <div class="btn-group card-option">
                                        <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <i class="feather icon-more-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            @permission('files download')
                                                @if (check_file($file->file_path))
                                                    <a class="mx-3 btn btn-sm align-items-center"
                                                        href="{{ get_file($file->file_path) }}" download>
                                                        <i class="ti ti-download"></i> <span>{{ __('Download') }}</span>
                                                    </a>
                                                @endif
                                            @endpermission
                                            @if (Laratrust::hasPermission('files edit') || Laratrust::hasPermission('files delete'))
                                                @permission('files edit')
                                                    <a class="dropdown-item" data-ajax-popup="true" data-size="md"
                                                        data-title="{{ __('Edit Article') }}"
                                                        data-url="{{ route('files.edit', [$file->id]) }}">
                                                        <i class="ti ti-pencil"></i> <span>{{ __('Edit') }}</span>
                                                    </a>
                                                @endpermission
                                                @permission('files delete')
                                                    <form id="delete-form-{{ $file->id }}"
                                                        action="{{ route('files.destroy', [$file->id]) }}" method="POST">
                                                        @csrf
                                                        <a href="#"
                                                            class="dropdown-item text-danger delete-popup bs-pass-para show_confirm"
                                                            data-confirm="{{ __('Are You Sure?') }}"
                                                            data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="delete-form-{{ $file->id }}">
                                                            <i class="ti ti-trash"></i> <span>{{ __('Delete') }}</span>
                                                        </a>
                                                        @method('DELETE')
                                                    </form>
                                                @endpermission
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="align-items-center">
                                    <img src="{{ isset($file->file_path) && !empty($file->file_path) && check_file($file->file_path) ? asset($file->file_path) : asset('Modules/FileSharing/Resources/assets/upload/thumbnail-not-found.png') }}"
                                        alt="Thumbnail" id="thumbnail" class="card-img" style="height: 200px">
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="card mb-0">
                                    <div class="card-body p-3">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <p class="text-muted text-sm mb-0">{{ __('File Name') }}</p>
                                                <h6 class="mb-0">{{ $filename }}</h6>
                                            </div>
                                            <hr class="mt-2 mb-0">
                                            <div class="col-md-12 mt-2">
                                                <p class="text-muted text-sm mb-1">{{ __('Users') }}</p>
                                                <h6 class="mb-0 user-group">
                                                    @foreach ($users as $user)
                                                        <img alt="image" data-bs-toggle="tooltip" data-bs-placement="top"
                                                            title="{{ $user->name }}"
                                                            @if ($user->avatar) src="{{ get_file($user->avatar) }}" @else src="{{ get_file('avatar.png') }}" @endif
                                                            lass="rounded-circle" width="25" height="25">
                                                    @endforeach
                                                </h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="col-md-3">
                    <a data-url="{{ route('files.create') }}" class="btn-addnew-project" data-ajax-popup="true"
                        data-size="md" data-title="{{ __('Create New Driver') }}" style="padding: 90px 10px">
                        <div class="badge bg-primary proj-add-icon">
                            <i class="ti ti-plus"></i>
                        </div>
                        <h6 class="mt-4 mb-2">{{ __('New Files') }}</h6>
                        <p class="text-muted text-center">{{ __('Click here to add New Files') }}</p>
                    </a>
                </div>
            </div>
        </section>
    </div>
@endsection
