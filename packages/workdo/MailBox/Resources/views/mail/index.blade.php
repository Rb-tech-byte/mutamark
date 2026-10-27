@extends('layouts.main')
@section('page-title')
    {{ __('EMail Box') }}
@endsection
@section('page-breadcrumb')
    {{ __('EMail Box') }}
@endsection
@push('css')
    <style>
        .btn.dropdown-toggle {
            border: none;
            background: transparent;
            box-shadow: none;
            padding: 0;
            width: 20px;
            height: 20px;
            right: 8px;
            top: 12px;
        }

        .ml-4 {
            margin-left: 1.5rem !important;
        }

        .dropdown-toggle::after {
            display: none;
        }

        .dropdown-menu-end {
            --bs-position: start;
        }

        [data-action] {
            display: block;
            margin: 10px auto;
            font-size: 17px;
            min-width: 3em;
            text-align: left;
            background: transparent;
            border: 0;
        }
    </style>
@endpush
@section('content')
    <!-- [ Main Content ] start -->
    <div class="row">
        <!-- [ sample-page ] start -->
        <div class="col-sm-12">
            <div class="row">
                @include('mailbox::layouts.sidebar')
                <div class="col-xl-9">
                    <div id="mail-inbox" class="">
                        <div class="card">
                            <div class="card-header @if (count($messages) != 0) p-2 @endif">
                                <div class="toolbar">
                                    @if (count($messages) != 0)
                                        <div class="btn-group text-end">
                                            @permission('Emailbox mail move')
                                                <button type="button" class="btn btn-light"
                                                    style="margin-top: 10px; margin-bottom: 10px;" data-bs-toggle="dropdown"
                                                    aria-haspopup="true" aria-expanded="true" data-action=""
                                                    data-bs-tooltip="tooltip" data-bs-placement="bottom"
                                                    title="{{ __('Move') }}">
                                                    <i class="ti ti-file-export text-secondary"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-end "
                                                    data-popper-placement="bottom-end">
                                                    <a class="dropdown-item action-all-btn" data-action="move" data-id="INBOX">
                                                        <i class="ti ti-inbox"></i>
                                                        <span>{{ __('Inbox') }}</span>
                                                    </a>
                                                    <a class="dropdown-item action-all-btn" data-action="move" data-id="Sent">
                                                        <i class="ti ti-send"></i>
                                                        <span>{{ __('Sent') }}</span>
                                                    </a>
                                                    <a class="dropdown-item action-all-btn" data-action="move" data-id="Drafts">
                                                        <i class="ti ti-file"></i>
                                                        <span>{{ __('Draft') }}</span>
                                                    </a>
                                                    <a class="dropdown-item action-all-btn" data-action="move" data-id="spam">
                                                        <i class="ti ti-alert-octagon"></i>
                                                        <span>{{ __('Spam') }}</span>
                                                    </a>
                                                    <a class="dropdown-item action-all-btn" data-action="move" data-id="Trash">
                                                        <i class="ti ti-trash"></i>
                                                        <span>{{ __('Trash') }}</span>
                                                    </a>
                                                    <a class="dropdown-item action-all-btn" data-action="move"
                                                        data-id="Archive">
                                                        <i class="ti ti-archive"></i>
                                                        <span>{{ __('Archive') }}</span>
                                                    </a>
                                                </div>
                                            @endpermission
                                            <button type="button" data-bs-toggle="tooltip" title=""
                                                data-original-title="{{ __('Mark as read') }}"
                                                data-bs-original-title="{{ __('Mark as read') }}"
                                                class="btn btn-light action-all-btn" data-action="seen">
                                                <i class="ti ti-mail text-info"></i>
                                            </button>
                                            <button type="button" data-bs-toggle="tooltip" title=""
                                                data-original-title="{{ __('Mark as unread') }}"
                                                data-bs-original-title="{{ __('Mark as unread') }}"
                                                class="btn btn-light action-all-btn" data-action="unseen">
                                                <i class="ti ti-mail-opened"></i>
                                            </button>
                                            <button type="button" class="btn btn-light action-all-btn"
                                                data-bs-toggle="tooltip" title=""
                                                data-original-title="{{ __('Starred') }}"
                                                data-bs-original-title="{{ __('Starred') }}" data-action="starred">
                                                <i class="ti ti-star text-warning"></i>
                                            </button>
                                            <button type="button" class="btn btn-light action-all-btn"
                                                data-bs-toggle="tooltip" title=""
                                                data-original-title="{{ __('Not starred') }}"
                                                data-bs-original-title="{{ __('Not starred') }}" data-action="unstarred">
                                                <i class="ti ti-star"></i>
                                            </button>

                                        </div>
                                    @else
                                        <h5 class="card-title mb-4"></h5>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body ">
                                <div class="row">
                                    @if (count($messages) != 0)
                                    @foreach (array_reverse($messages->toArray()) as $oMessage)
                                            @php
                                                $flag_arr = $oMessage->getFlags();
                                            @endphp
                                            <div class="col-md-12 pb-1">
                                                <div class="row">
                                                    <div class="col-md-1">
                                                        <div class="d-flex">
                                                            <input type="checkbox" name="mail_check"
                                                                class="form-check-input"
                                                                value="{{ $oMessage->getMsgn() }}">

                                                            <a href="javascript:void(0)" class="action-btn action"
                                                                style="display: inline-block; margin: auto auto; min-width: 2em"
                                                                data-action="@if ($flag_arr->has('flagged') || $flag_arr->has('Flagged')) unstarred @else starred @endif"
                                                                data-id="{{ $oMessage->getMsgn() }}"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-original-title="@if ($flag_arr->has('flagged') || $flag_arr->has('Flagged')) {{ __('Starred') }} @else {{ __('Not starred') }} @endif"
                                                                data-bs-original-title="@if ($flag_arr->has('flagged') || $flag_arr->has('Flagged')) {{ __('Starred') }} @else {{ __('Not starred') }} @endif">
                                                                <i
                                                                    class="@if ($flag_arr->has('flagged') || $flag_arr->has('Flagged')) ti ti-star text-warning @else ti ti-star text-secondary @endif"></i>
                                                            </a>

                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        {{ $oMessage->getFrom()[0]->mailbox }}
                                                    </div>
                                                    <div class="col-md-7">
                                                        <b>{{ $oMessage->getSubject() }} - </b>
                                                        <a
                                                            href="{{ route('mailbox.show', [$oMessage->getMsgn(), request()->segment(count(request()->segments()))]) }}">{{ substr($oMessage->getTextBody(), 0, 180) . '...' }}</a>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="d-flex" style="gap: 7px">

                                                            <a href="{{ route('mailbox.show', [$oMessage->getMsgn(), request()->segment(count(request()->segments()))]) }}" class="action-btn action" >
                                                                <i
                                                                    class="ti ti-eye text-secondary"></i>
                                                            </a>

                                                            <a href="javascript:void(0)" class="action-btn action"
                                                                style="display: flex;margin:0;min-width: auto; justify-content:center;"
                                                                data-action="@if ($flag_arr->has('seen')) unseen @else seen @endif"
                                                                data-id="{{ $oMessage->getMsgn() }}"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-original-title="@if ($flag_arr->has('seen')) {{ __('Mark as unread') }} @else {{ __('Mark as read') }} @endif"
                                                                data-bs-original-title="@if ($flag_arr->has('seen')) {{ __('Mark as unread') }} @else {{ __('Mark as read') }} @endif">
                                                                <i
                                                                    class="@if ($flag_arr->has('seen')) ti ti-mail text-info @else ti ti-mail-opened text-secondary @endif"></i>
                                                            </a>
                                                            @permission('Emailbox mail delete')
                                                                    <a href="{{ route('mailbox.destroy', [$oMessage->getMsgn(), request()->segment(count(request()->segments()))]) }}"
                                                                        class="action-btn action"
                                                                        data-bs-toggle="tooltip" title=""
                                                                        data-original-title="{{ __('Delete') }}"
                                                                        data-bs-original-title="{{ __('Delete') }}">
                                                                        <i class="fa fa-trash text-danger " style="font-size: initial"></i></a>
                                                            @endpermission

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr>
                                        @endforeach
                                    @else
                                        <div class="text-center">
                                            <div class="bg-light-primary text-center p-4" style="height: 300px">
                                                <p style="margin-top: 100px;font-size:20px">{{ __('No data found') }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script type="text/javascript">
        $(".action-all-btn").on('click', function() {
            var currentURL = window.location.href;
            var urlParts = currentURL.split('/');
            var lastElement = urlParts[urlParts.length - 1];
            var checkedValues = [];
            $('input[name="mail_check"]:checked').each(function() {

                checkedValues.push($(this).val());
            });
            var token = $('meta[name="csrf-token"]').attr('content');
            var id = checkedValues;
            var action = $(this).attr('data-action');
            action = action.trim();
            var moveToFolder = '';
            if (action == 'move') {
                moveToFolder = $(this).attr('data-id');
            }
            $.ajax({
                type: 'post',
                url: '{{ url('/mailbox/action') }}',
                dataType: 'json',
                data: {
                    '_token': '{{ csrf_token() }}',
                    'id': id,
                    'action': action,
                    'folder': lastElement,
                    'moveToFolder': moveToFolder
                },
                beforeSend: function() {
                    $(".loader-wrapper").removeClass('d-none');
                },
                success: function(data) {
                    $(".loader-wrapper").addClass('d-none');
                    if (data.status == 1) {

                        toastrs('success', data.msg, 'success');
                        location.reload(true);
                    } else if (data.status == 0) {
                        toastrs('success', data.msg, 'success');
                        location.reload(true);
                    } else {
                        toastrs('error', data.msg, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    $(".loader-wrapper").addClass('d-none');
                    toastrs('error', '{{ __('Something went wrong please try again') }}', 'error');
                }
            });

        });
        $(".action-btn").on('click', function() {
            var checkedValues = [];
            var token = $('meta[name="csrf-token"]').attr('content');
            checkedValues.push($(this).attr('data-id'));
            var temp = $(this);
            var action = $(this).attr('data-action');
            action = action.trim();
            $.ajax({
                type: 'post',
                url: '{{ url('/mailbox/action') }}',
                dataType: 'json',
                data: {
                    '_token': '{{ csrf_token() }}',
                    'id': checkedValues,
                    'action': action
                },
                beforeSend: function() {
                    $(".loader-wrapper").removeClass('d-none');
                },
                success: function(data) {
                    $(".loader-wrapper").addClass('d-none');
                    if (data.status == 1) {

                        if (action == 'starred') {

                            var html = '<i class="ti ti-star text-warning"></i>';
                            temp.html(html);
                            temp.attr('data-action', 'unstarred');
                            toastrs('success', data.msg, 'success');

                        } else if (action == 'seen') {
                            var html = '<i class="ti ti-mail text-info"></i>';
                            temp.html(html);
                            temp.attr('data-action', 'unseen');
                            toastrs('success', data.msg, 'success');
                        } else {}
                    } else if (data.status == 0) {

                        if (action == 'unstarred') {
                            var html = '<i class="ti ti-star text-secondary"></i>';
                            temp.html(html);
                            temp.attr('data-action', 'starred');
                            toastrs('success', data.msg, 'success');
                        } else if (action == 'unseen') {

                            var html = '<i class="ti ti-mail-opened text-secondary"></i>';
                            temp.html(html);
                            temp.attr('data-action', 'seen');
                            toastrs('success', data.msg, 'success');
                        } else {}
                    } else {
                        toastrs('error', data.msg, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    $(".loader-wrapper").addClass('d-none');
                    toastrs('error', '{{ __('Something went wrong please try again') }}', 'error');
                }
            });
        });
    </script>
@endpush
