@extends('layouts.main')
@section('page-title')
    {{ __('Files') }}
@endsection
@section('page-breadcrumb')
    {{ __('Manage Files') }}
@endsection

@section('page-action')
    <div>
        <a href="{{ route('file.grid') }}" class="btn btn-sm btn-primary"
            data-bs-toggle="tooltip"title="{{ __('Grid View') }}">
            <i class="ti ti-layout-grid text-white"></i>
        </a>
        @permission('files create')
            <a class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="md" data-title="{{ __('Add Files') }}"
                data-url="{{ route('files.create') }}" data-toggle="tooltip" title="{{ __('Create') }}">
                <i class="ti ti-plus"></i>
            </a>
        @endpermission
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <h5></h5>
                    <div class="table-responsive">
                        <table class="table mb-0 pc-dt-simple" id="products">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th>{{ __('File Size') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Auto Destroy') }}</th>
                                    <th>{{ __('Share Mode') }}</th>
                                    <th>{{ __('Users') }}</th>
                                    <th>{{ __('Has password') }}</th>
                                    <th>{{ __('Total Downloads') }}</th>
                                    @if (Laratrust::hasPermission('files delete') || Laratrust::hasPermission('files edit'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($fileShares as  $index => $fileShare)
                                    @php
                                        $file_download = Modules\FileSharing\Entities\FileDownload::where('file_id', $fileShare->id)->get()->count();
                                    @endphp

                                    <tr class="font-style">
                                        <th scope="row">{{++$index}}</th>
                                        <td>{{ $fileShare->file_size }}</td>
                                        <td>
                                            @if ($fileShare->file_status == 'Available')
                                                <span class="badge bg-primary p-2 px-3 rounded"
                                                    style="width: 90px;">{{ __($fileShare->file_status) }}</span>
                                            @elseif($fileShare->file_status == 'Not Available')
                                                <span class="badge bg-danger p-2 px-3 rounded"
                                                    style="width: 90px;">{{ __($fileShare->file_status) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $fileShare->auto_destroy }}</td>
                                        <td>{{ $fileShare->filesharing_type }}</td>
                                        <td>
                                            @php
                                                $user_id = explode(',', $fileShare->user_id);
                                                $users = App\Models\User::whereIn('id', $user_id)->get();
                                            @endphp
                                            <div class="user-group">
                                                @foreach ($users as $user)
                                                    <img alt="image" data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="{{ $user->name }}"
                                                        @if ($user->avatar) src="{{ get_file($user->avatar) }}" @else src="{{ get_file('avatar.png') }}" @endif
                                                        class="rounded-circle" width="25" height="25">
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="font-style">{{ $fileShare->is_pass_enable == 1 ? __('Yes') : __('No') }}
                                        </td>
                                        <td>{{ $file_download }}</td>

                                        <td class="Action">
                                            <span>
                                                @if (check_file($fileShare->file_path))
                                                <div class="action-btn bg-primary ms-2">
                                                    <a class="mx-3 btn btn-sm align-items-center"
                                                        href="{{ get_file($fileShare->file_path) }}" download>
                                                        <i class="ti ti-download text-white"></i>
                                                    </a>
                                                </div>
                                                @endif
                                                <div class="action-btn bg-primary ms-2">
                                                    <a href="#"
                                                        class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                        id="{{ route('file.shared.link', \Illuminate\Support\Facades\Crypt::encrypt($fileShare->id)) }}"
                                                        onclick="copyToClipboard(this)" data-bs-toggle="tooltip"
                                                        data-title="{{ __('Click to copy link') }}"
                                                        title="{{ __('copy link') }}"><span
                                                            class="btn-inner--icon text-white"><i
                                                                class="ti ti-file"></i></span></a>
                                                </div>

                                                @permission('files edit')
                                                    <div class="action-btn bg-info ms-2">
                                                        <a class="mx-3 btn btn-sm align-items-center"
                                                            data-url="{{ route('files.edit', $fileShare->id) }}"
                                                            data-ajax-popup="true" data-size="md"
                                                            data-title="{{ __('Edit File') }}" data-bs-toggle="tooltip"
                                                            title="{{ __('Edit') }}"
                                                            data-original-title="{{ __('Edit') }}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                @endpermission
                                                @permission('files delete')
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['files.destroy', $fileShare->id]]) !!}
                                                        <a href="#!"
                                                            class="mx-3 btn btn-sm d-inline-flex align-items-center show_confirm"
                                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                                            title="{{ __('Delete') }}">
                                                            <span class="text-white"> <i class="ti ti-trash"></i></span>
                                                            {!! Form::close() !!}
                                                    </div>
                                                @endpermission
                                            </span>
                                        </td>
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
@push('scripts')

<script>
    function copyToClipboard(element) {
        var copyText = element.id;
        document.addEventListener('copy', function(e) {
            e.clipboardData.setData('text/plain', copyText);
            e.preventDefault();
        }, true);
        document.execCommand('copy');
        toastrs('success', 'Link Copy on Clipboard', 'success');
    }
</script>
@endpush
