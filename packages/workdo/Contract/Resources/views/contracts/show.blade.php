@extends('layouts.main')

@section('page-title')
    {{ __('Contract') }}
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/dropzone.min.css') }}">
    <link href="{{  asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css')  }}" rel="stylesheet">
    <style>
        .nav-tabs .nav-link-tabs.active {
            background: none;
        }
    </style>
@endpush
@push('scripts')
    <script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/dropzone-amd-module.min.js') }}"></script>
    <script>
        var scrollSpy = new bootstrap.ScrollSpy(document.body, {
            target: '#useradd-sidenav',
            offset: 300
        })
    </script>

    <script>
        Dropzone.autoDiscover = true;
        myDropzone = new Dropzone("#my-dropzone", {
            url: "{{ route('contracts.file.upload', [$contract->id]) }}",
            success: function(file, response) {
                location.reload();
                if (response.is_success) {
                    dropzoneBtn(file, response);
                    toastrs('{{ __('Success') }}', 'Attachment Create Successfully!', 'success');
                } else {
                    myDropzone.removeFile(file);
                    toastrs('{{ __('Error') }}', 'File type must be match with Storage setting.', 'error');
                }
            },
            error: function(file, response) {
                myDropzone.removeFile(file);
                if (response.error) {
                    toastrs('{{ __('Error') }}', response.error, 'error');
                } else {
                    toastrs('{{ __('Error') }}', response.error, 'error');
                }
            }
        });
        myDropzone.on("sending", function(file, xhr, formData) {
            formData.append("_token", $('meta[name="csrf-token"]').attr('content'));
            formData.append("contract_id", {{ $contract->id }});

        });

        function dropzoneBtn(file, response) {
            var download = document.createElement('a');
            download.setAttribute('href', response.download);
            download.setAttribute('class', "action-btn btn-primary mx-1 mt-1 btn btn-sm d-inline-flex align-items-center");
            download.setAttribute('data-toggle', "tooltip");
            download.setAttribute('data-original-title', "{{ __('Download') }}");
            download.innerHTML = "<i class='fas fa-download'></i>";

            var del = document.createElement('a');
            del.setAttribute('href', response.delete);
            del.setAttribute('class', "action-btn btn-danger mx-1 mt-1 btn btn-sm d-inline-flex align-items-center");
            del.setAttribute('data-toggle', "tooltip");
            del.setAttribute('data-original-title', "{{ __('Delete') }}");
            del.innerHTML = "<i class='ti ti-trash'></i>";

            del.addEventListener("click", function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (confirm("Are you sure ?")) {
                    var btn = $(this);
                    $.ajax({
                        url: btn.attr('href'),
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        type: 'DELETE',
                        success: function(response) {
                            if (response.is_success) {
                                btn.closest('.dz-image-preview').remove();
                            } else {
                                toastrs('{{ __('Error') }}', response.error, 'error');
                            }
                        },
                        error: function(response) {
                            response = response.responseJSON;
                            if (response.is_success) {
                                toastrs('{{ __('Error') }}', response.error, 'error');
                            } else {
                                toastrs('{{ __('Error') }}', response.error, 'error');
                            }
                        }
                    })
                }
            });

            var html = document.createElement('div');
            html.setAttribute('class', "text-center mt-10");
            html.appendChild(download);
            html.appendChild(del);

            file.previewTemplate.appendChild(html);
        }
        @if($contract->files)

            @foreach ($contract->files as $file)
            @endforeach
        @endif
    </script>

    <script>
        $(document).on('click', '#comment_submit', function(e) {
            var curr = $(this);

            var comment = $.trim($("#form-comment textarea[name='comment']").val());
            $.ajax({
                url: $("#form-comment").attr('data-action'),
                data: {
                    comment: comment,
                    "_token": "{{ csrf_token() }}",
                },
                type: 'POST',
                success: function(data) {

                    toastrs('{{ __('Success') }}', 'Comment Create Successfully!', 'success');


                    setTimeout(function() {
                        location.reload();
                    }, 500)
                    data = JSON.parse(data);
                    var html = "<div class='list-group-item px-0'>" +
                        "                    <div class='row align-items-center'>" +
                        "                        <div class='col-auto'>" +
                        "                            <a href='#' class='avatar avatar-sm rounded-circle ms-2'>" +
                        "                                <img src=" + data.default_img +
                        " alt='' class='avatar-sm rounded-circle'>" +
                        "                            </a>" +
                        "                        </div>" +
                        "                        <div class='col ml-n2'>" +
                        "                            <p class='d-block h6 text-sm font-weight-light mb-0 text-break'>" +
                        data.comment + "</p>" +
                        "                            <small class='d-block'>" + data.current_time +
                        "</small>" +
                        "                        </div>" +
                        "                        <div class='action-btn bg-danger me-4'><div class='col-auto'><a href='#' class='mx-3 btn btn-sm  align-items-center delete-comment' data-url='" +
                        data.deleteUrl + "'><i class='ti ti-trash text-white'></i></a></div></div>" +
                        "                    </div>" +
                        "                </div>";

                    $("#comments").prepend(html);
                    $("#form-comment textarea[name='comment']").val('');
                    load_task(curr.closest('.task-id').attr('id'));
                    toastrs('is_success', 'Comment Added Successfully!');
                },
                error: function(data) {
                    toastrs('error', 'Some Thing Is Wrong!');
                }
            });

        });


        $(document).on("click", ".delete-comment", function() {
            var btn = $(this);

            $.ajax({
                url: $(this).attr('data-url'),
                type: 'DELETE',
                dataType: 'JSON',
                data: {
                    comment: comment,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    load_task(btn.closest('.task-id').attr('id'));
                    toastrs('success', 'Comment Deleted Successfully!');
                    btn.closest('.list-group-item').remove();
                },
                error: function(data) {
                    data = data.responseJSON;
                    if (data.message) {
                        toastrs('error', data.message);
                    } else {
                        toastrs('error', 'Some Thing Is Wrong!');
                    }
                }
            });
        });
    </script>

    <script>
        $(document).on("click", ".status", function() {

            var status = $(this).attr('data-id');
            var url = $(this).attr('data-url');
            $.ajax({
                url: url,
                type: 'POST',
                data: {

                    "status": status,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    toastrs('{{ __('Success') }}', 'Status Update Successfully!', 'success');
                    location.reload();
                }

            });
        });
    </script>
@endpush

@section('page-breadcrumb')
    {{ __('Contract') }},
    {{ __('Show') }}
@endsection


@section('page-action')
    <div class="col-md-6 text-end d-flex">
        @stack('addButtonHook')
        @stack('contractButtonHook')

        @if (\Auth::user()->type == 'company')
            <a href="{{ route('send.mail.contract', $contract->id) }}" class="btn btn-sm btn-primary btn-icon m-1"
                data-bs-toggle="tooltip" data-bs-original-title="{{ __('Send Email') }}">
                <i class="ti ti-mail text-white"></i>
            </a>
        @endif

        @if (\Auth::user()->type == 'company')
            <a data-size="lg" data-url="{{ route('contracts.copy', $contract->id) }}"data-ajax-popup="true"
                data-title="{{ __('Duplicate') }}" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="tooltip"
                data-bs-placement="top" title="{{ __('Duplicate') }}"><i class="ti ti-copy text-white"></i></a>
        @endif

        <a href="{{ route('contract.download.pdf', \Crypt::encrypt($contract->id)) }}"
            class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="tooltip" data-bs-placement="top"
            title="{{ __('Download') }}" target="_blanks"><i class="ti ti-download text-white"></i></a>

        <a href="{{ route('get.contract', $contract->id) }}" target="_blank" class="btn btn-sm btn-primary btn-icon m-1"
            title="{{ __('Preview') }}" data-bs-toggle="tooltip" data-bs-placement="top">
            <i class="ti ti-eye"></i>
        </a>

        @if (\Auth::user()->type == 'company' && $contract->owner_signature == '')
            <a class="btn btn-sm btn-primary btn-icon m-1" data-url="{{ route('signature', $contract->id) }}"
                data-ajax-popup="true" data-title="{{ __('Signature') }}" data-size="md" title="{{ __('Signature') }}"
                data-bs-toggle="tooltip" data-bs-placement="top">
                <i class="ti ti-pencil"></i>
            </a>
        @elseif ($contract->client_signature == '' && $contract->status == 'accept')
            <a class="btn btn-sm btn-primary btn-icon m-1" data-url="{{ route('signature', $contract->id) }}"
                data-ajax-popup="true" data-title="{{ __('Signature') }}" data-size="md" title="{{ __('Signature') }}"
                data-bs-toggle="tooltip" data-bs-placement="top">
                <i class="ti ti-pencil"></i>
            </a>
        @endif

        @php
            $status = Modules\Contract\Entities\Contract::status();
        @endphp
        @if (\Auth::user()->type != 'company')
            <ul class="list-unstyled mb-0 m-1">
                <li class="dropdown dash-h-item drp-language mt-1">

                    <a class="dash-head-link dropdown-toggle arrow-none p-2"
                        style="border: 1px solid rgba(206, 206, 206, 0.2);" data-bs-toggle="dropdown" href="#"
                        role="button" aria-haspopup="false" aria-expanded="false">
                        <span class="drp-text text-dark">{{ ucfirst($contract->status) }}
                            <i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i></span>
                    </a>
                    <div class="dropdown-menu dash-h-dropdown">
                        @foreach ($status as $k => $status)
                            <a class="dropdown-item status" data-id="{{ $k }}"
                                data-url="{{ route('contract.status', $contract->id) }}">{{ ucfirst($status) }}</a>
                        @endforeach
                    </div>
                </li>
            </ul>
        @endif
    </div>
@endsection


@section('content')
    <div class="row">
        <div class="col-sm-12 ">
            <div class="row">
                <div class="col-md-3">
                    <div class="card sticky-top" style="top:30px">
                        <div class="list-group list-group-flush" id="useradd-sidenav">
                            <a href="#general" class="list-group-item list-group-item-action border-0">{{ __('General') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#attachments"
                                class="list-group-item list-group-item-action border-0">{{ __('Attachment') }} <div
                                    class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                            <a href="#comments" class="list-group-item list-group-item-action border-0">{{ __('Comment') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                            <a href="#notes" class="list-group-item list-group-item-action border-0">{{ __('Notes') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                            <a href="#renewals" class="list-group-item list-group-item-action border-0">{{ __('Contract Renewal History') }}
                                    <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div id="general">
                        <div class="row">
                            <div class="col-xxl-7">
                                <div class="row">
                                    <div class="col-lg-4 col-6">
                                        <div class="card report_card">
                                            <div class="card-body">
                                                <div class="theme-avtar bg-success">
                                                    <i class="ti ti-shopping-cart  text-white"></i>
                                                </div>
                                                <p class="text-dark h6 text-m mt-4 mb-4">{{ __('Attachments') }}</p>
                                                <h3 class="mb-0">{{ count($files) }}</h3>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-6">
                                        <div class="card report_card">
                                            <div class="card-body">
                                                <div class="theme-avtar bg-info">
                                                    <i class="ti ti-eye  text-white"></i>
                                                </div>
                                                <p class="text-dark h6 text-m mt-4 mb-4">{{ __('Comments') }}</p>
                                                <h3 class="mb-0">{{ count($comments) }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-6">
                                        <div class="card report_card">
                                            <div class="card-body">
                                                <div class="theme-avtar bg-warning">
                                                    <i class="ti ti-file-invoice  text-white"></i>
                                                </div>
                                                <p class="text-dark h6 text-m mt-4 mb-4">{{ __('Notes') }}</p>
                                                <h3 class="mb-0">{{ count($notes) }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xxl-5">
                                <div class="card report_card total_amount_card">
                                    <div class="card-body pt-0" style="min-height: 190px;">

                                        <div class="row mt-2 mb-0 align-items-center">
                                            <div class="col-sm-4 h6 text-m">{{ __('Subject') }}</div>
                                            <div class="col-sm-8 text-m"> {{ $contract->subject }}</div>
                                            <div class="col-sm-4 h6 text-m">{{ __('User') }}</div>
                                            <div class="col-sm-8 text-m">
                                                {{ !empty($contract->user_name) ? $contract->user_name : '-' }}</div>
                                            <div class="col-sm-4 h6 text-m">{{ __('Project') }}</div>
                                            <div class="col-sm-8 text-m">
                                                {{ !empty($contract->project_name) ? $contract->project_name : '-' }}</div>
                                            <div class="col-sm-4 h6 text-m">{{ __('Type') }}</div>
                                            <div class="col-sm-8 text-m">{{ $contract->type }}</div>
                                            <div class="col-sm-4 h6 text-m">{{ __('Value') }}</div>
                                            <div class="col-sm-8 text-m"> {{ currency_format_with_sym($renewContract->value ?? $contract->value) }}
                                            </div>
                                            <div class="col-sm-4 h6 text-m">{{ __('Start Date') }}</div>
                                            <div class="col-sm-8 text-m">{{ company_date_formate($renewContract->start_date ?? $contract->start_date) }}
                                            </div>
                                            <div class="col-sm-4 h6 text-m">{{ __('End Date') }}</div>
                                            <div class="col-sm-8 text-m">{{ company_date_formate($renewContract->end_date ?? $contract->end_date) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>{{ __('Contact Description') }}</h5>
                                    </div>
                                    <div class="card-body">
                                        {{ Form::open(['route' => ['contracts.description.store', $contract->id]]) }}
                                        <div class="form-group mt-3">
                                            <textarea class="tox-target summernote" id="pc_demo1" name="description" rows="8">{!! $contract->description !!}</textarea>
                                        </div>
                                        @if (\Auth::user()->type == 'company')
                                            <div class="col-md-12 text-end mb-0">
                                                {{ Form::submit(__('Add'), ['class' => 'btn  btn-primary']) }}
                                            </div>
                                        @endif
                                        {!! Form::close() !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="attachments">
                        <div class="row ">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>{{ __('Attachments') }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class=" ">
                                            @if (\Auth::user()->type == 'company')
                                                <div class="col-md-12 dropzone browse-file" id="my-dropzone"></div>
                                            @elseif(\Auth::user()->type != 'company' && $contract->status == 'accept')
                                                <div class="col-md-12 dropzone browse-file" id="my-dropzone"></div>
                                            @endif
                                        </div>
                                    </div>
                                    @foreach ($files as $file)
                                        <div class="px-4 py-3">
                                            <div class="list-group-item ">
                                                <div class="row align-items-center">
                                                    <div class="col">
                                                        <h6 class="text-sm mb-0">
                                                            <a href="#!">{{ $file->files }}</a>
                                                        </h6>
                                                        <p class="card-text small text-muted">
                                                            {{ number_format(get_size(get_file($file->files)) / 1048576, 2) . ' ' . __('MB') }}

                                                        </p>
                                                    </div>
                                                    <div class="action-btn bg-warning p-0 w-auto">
                                                        <a href="{{ get_file($file->files) }}"
                                                            class=" btn btn-sm d-inline-flex align-items-center"
                                                            download="" data-bs-toggle="tooltip" title="Download">
                                                            <span class="text-white"><i class="ti ti-download"></i></span>
                                                        </a>
                                                    </div>
                                                    <div class="col-auto p-0 ms-2 action-btn bg-danger">
                                                        @if (
                                                            \Auth::user()->type == 'company' ||
                                                                (\Auth::user()->type == 'client' && $contract->status == 'accept' && \Auth::user()->id == $file->user_id))
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['contracts.file.delete', $contract->id, $file->id]]) !!}
                                                            <a href="#!"
                                                                class="btn btn-sm d-inline-flex align-items-center show_confirm"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="{{ __('Delete') }}">
                                                                <span class="text-white"> <i
                                                                        class="ti ti-trash"></i></span>
                                                            </a>
                                                            {!! Form::close() !!}
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="comments">
                        <div class="row pt-2">
                            <div class="col-12">
                                <div class="card">
                                    <div id="comment">
                                        <div class="card-header">
                                            <h5>{{ __('Comments') }}</h5>
                                        </div>
                                        <div class="card-footer">
                                            @if (\Auth::user()->type == 'company' || $contract->status == 'accept')
                                                <div class="col-12 d-flex">
                                                    <div class="form-group mb-0 form-send w-100">
                                                        <form method="post" class="card-comment-box" id="form-comment"
                                                            data-action="{{ route('contract.comment.store', [$contract->id]) }}">
                                                            <textarea rows="1" class="form-control" name="comment" data-toggle="autosize" required placeholder="Add a comment..."
                                                                spellcheck="false" required></textarea>
                                                            <grammarly-extension data-grammarly-shadow-root="true"
                                                                style="position: absolute; top: 0px; left: 0px; pointer-events: none; z-index: 1;"
                                                                class="cGcvT"></grammarly-extension>
                                                            <grammarly-extension data-grammarly-shadow-root="true"
                                                                style="mix-blend-mode: darken; position: absolute; top: 0px; left: 0px; pointer-events: none; z-index: 1;"
                                                                class="cGcvT"></grammarly-extension>
                                                        </form>
                                                    </div>
                                                    @permission('comment create')
                                                        <button id="comment_submit" class="btn btn-send"><i
                                                                class="f-16 text-primary ti ti-brand-telegram">
                                                            </i>
                                                        </button>
                                                    @endpermission
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="list-group list-group-flush mb-0" id="comments">
                                        @foreach ($comments as $comment)
                                            @php
                                                $user = \App\Models\User::find($comment->user_id);
                                            @endphp
                                            <div class="list-group-item ">
                                                <div class="row align-items-center">
                                                    <div class="col-auto">
                                                        <a href="{{ !empty($user->avatar) ? get_file($user->avatar) : 'avatar.png' }}"
                                                            target="_blank">
                                                            <img src="{{ !empty($user->avatar) ? get_file($user->avatar) : 'avatar.png' }}"
                                                                class="img-fluid rounded-circle" width="30">
                                                        </a>
                                                    </div>
                                                    <div class="col ml-n2">
                                                        <p class="d-block h6 text-sm font-weight-light mb-0 text-break">
                                                            {{ $comment->comment }}</p>
                                                        <small
                                                            class="d-block">{{ $comment->created_at->diffForHumans() }}</small>
                                                    </div>
                                                    @if (\Auth::user()->type == 'company' || ($contract->status == 'accept' && \Auth::user()->id == $comment->user_id))
                                                        @permission('comment delete')
                                                            <div class="col-auto p-0 mx-5 action-btn bg-danger">
                                                                {!! Form::open(['method' => 'GET', 'route' => ['contract.comment.destroy', $comment->id]]) !!}
                                                                <a href="#!"
                                                                    class="btn btn-sm d-inline-flex align-items-center show_confirm"
                                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                                    title="{{ __('Delete') }}">
                                                                    <span class="text-white"> <i
                                                                            class="ti ti-trash"></i></span>
                                                                </a>
                                                                {!! Form::close() !!}
                                                            </div>
                                                        @endpermission
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="notes">
                        <div class="row pt-2">
                            <div class="col-12">
                                <div id="notes">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>{{ __('Notes') }}</h5>
                                        </div>
                                        <div class="card-body">
                                            @if (\Auth::user()->type == 'company' || $contract->status == 'accept')
                                                {{ Form::open(['route' => ['contracts.note.store', $contract->id]]) }}
                                                <div class="form-group">
                                                    <textarea rows="3" class="form-control" name="note" id="summernote" data-toggle="autosize"
                                                        placeholder="Add a Notes..." spellcheck="false" required></textarea>
                                                </div>
                                                @permission('contract note create')
                                                    <div class="col-md-12 text-end mb-0">
                                                        {{ Form::submit(__('Add'), ['class' => 'btn  btn-primary']) }}
                                                    </div>
                                                @endpermission
                                                {{ Form::close() }}
                                            @endif
                                            <div class="list-group list-group-flush mb-0" id="notes">
                                                @foreach ($notes as $note)
                                                    @php
                                                        $user = \App\Models\User::find($note->user_id);
                                                    @endphp
                                                    <div class="list-group-item ">
                                                        <div class="row align-items-center">
                                                            <div class="col-auto">

                                                                <a href="{{ !empty($user->avatar) ? get_file($user->avatar) : 'avatar.png' }}"
                                                                    target="_blank">
                                                                    <img src="{{ !empty($user->avatar) ? get_file($user->avatar) : 'avatar.png' }}"
                                                                        class="img-fluid rounded-circle" width="30">
                                                                </a>
                                                            </div>
                                                            <div class="col ml-n2">
                                                                <p
                                                                    class="d-block h6 text-sm font-weight-light mb-0 text-break">
                                                                    {{ $note->note }}</p>
                                                                <small
                                                                    class="d-block">{{ $note->created_at->diffForHumans() }}</small>
                                                            </div>
                                                            @if (\Auth::user()->type == 'company' || ($contract->status == 'accept' && \Auth::user()->id == $note->user_id))
                                                                @permission('contract note delete')
                                                                    <div
                                                                        class="col-auto col-auto p-0 mx-3 action-btn bg-danger">
                                                                        {!! Form::open(['method' => 'GET', 'route' => ['contracts.note.destroy', $note->id]]) !!}
                                                                        <a href="#!"
                                                                            class="btn btn-sm d-inline-flex align-items-center show_confirm"
                                                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                                                            title="{{ __('Delete') }}">
                                                                            <span class="text-white"> <i
                                                                                    class="ti ti-trash"></i></span>
                                                                        </a>
                                                                        {!! Form::close() !!}
                                                                    </div>
                                                                @endpermission
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div id="renewals">
                        <div class="row pt-2">
                            <div class="col-12">
                                <div id="renewals">
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="row">
                                                <div class="col-lg-10 col-md-10 col-sm-10">
                                                    <h5>{{ __('Renew Contract') }}</h5>
                                                </div>
                                                <div class="col-lg-2 col-md-2 col-sm-2 text-end">
                                                    <div class="float-end">
                                                        

                                                        <a class="btn btn-sm btn-primary" data-url="{{ route('contracts.renewcontract', $contract->id) }}"
                                                            data-ajax-popup="true" data-title="{{ __('Renew Contract') }}" data-size="md" title="{{ __('Renew Contract') }}"
                                                            data-bs-toggle="tooltip" data-bs-placement="top">
                                                            <i class="ti ti-plus text-white"></i>
                                                        </a>

                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="card-body">

                                            <table class="table dataTable3">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('Start Date') }}</th>
                                                        <th>{{ __('End Date') }}</th>
                                                        <th>{{ __('Value') }}</th>
                                                        <th>{{ __('Action') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($renewContracts as  $renew)
                                                    <tr>
                                            
                                                        <td class="font-style">{{ $renew->start_date }}</td>
                                                        <td class="font-style">{{ $renew->end_date }}</td>
                                                        <td class="font-style">{{ $renew->value }}</td>
                                                        <td class="Action">
                                                            <span>
                                                                
                                                                    <div class="action-btn bg-danger ms-2">
                                                                        {{ Form::open(['route' => ['contracts.renewcontract.delete', $renew['id']], 'class' => 'm-0']) }}
                                                                        @method('DELETE')
                                                                        <a class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm"
                                                                            data-bs-toggle="tooltip" title=""
                                                                            data-bs-original-title="Delete"
                                                                            aria-label="Delete"
                                                                            data-confirm="{{ __('Are You Sure?') }}"
                                                                            data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                            data-confirm-yes="delete-form-{{ $renew['id'] }}"><i
                                                                                class="ti ti-trash text-white text-white"></i></a>
                                                                        {{ Form::close() }}
                                                                    </div>
            
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

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
