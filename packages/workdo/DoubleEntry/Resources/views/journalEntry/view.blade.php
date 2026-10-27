@extends('layouts.main')
@section('page-title')
    {{ __('Journal Detail') }}
@endsection

@section('page-breadcrumb')
    {{ __('Journal Entry') }}
    {{ \Modules\DoubleEntry\Entities\JournalEntry::journalNumberFormat($journalEntry->journal_id) }}
@endsection


@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="invoice">
                        <div class="invoice-print">
                            <div class="row invoice-title mt-2">
                                <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-12">
                                    <h2>{{ __('Journal') }}</h2>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-12 text-end">
                                    <h3 class="invoice-number">
                                        {{ \Modules\DoubleEntry\Entities\JournalEntry::journalNumberFormat($journalEntry->journal_id) }}
                                    </h3>
                                </div>
                                <div class="col-12">
                                    <hr>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="font-style">
                                        <strong>{{ __('To') }} :</strong><br>
                                        {{ !empty($settings['company_name']) ? $settings['company_name'] : '' }}<br>
                                        {{ !empty($settings['company_telephone']) ? $settings['company_telephone'] : '' }}<br>
                                        {{ !empty($settings['company_address']) ? $settings['company_address'] : '' }}<br>
                                        {{ !empty($settings['company_city']) ? $settings['company_city'] : '' . ', ' }}
                                        {{ !empty($settings['company_state']) ? $settings['company_state'] : '' . ', ' }}
                                        {{ !empty($settings['company_country']) ? $settings['company_country'] : '' . '.' }}
                                    </small>
                                </div>
                                <div class="col-md-6 text-end">
                                    <small>
                                        <strong>{{ __('Journal No') }} :</strong>
                                        {{ \Modules\DoubleEntry\Entities\JournalEntry::journalNumberFormat($journalEntry->journal_id) }}
                                    </small><br>
                                    <small>
                                        <strong>{{ __('Journal Ref') }} :</strong>
                                        {{ $journalEntry->reference }}
                                    </small> <br>
                                    <small>
                                        <strong>{{ __('Journal Date') }} :</strong>
                                        {{ company_date_formate($journalEntry->date) }}
                                    </small><br>
                                    @if (!empty($customFields) && count($journalEntry->customField) > 0)
                                        @foreach ($customFields as $field)
                                            <small>
                                                <strong>{{ $field->name }} :</strong>
                                                @if ($field->type == 'attachment')
                                                    <a href="{{ get_file($journalEntry->customField[$field->id]) }}"
                                                        target="_blank">
                                                        <img src="{{ get_file($journalEntry->customField[$field->id]) }}"
                                                            class="wid-75 rounded me-3">
                                                    </a>
                                                @else
                                                    {{ !empty($journalEntry->customField[$field->id]) ? $journalEntry->customField[$field->id] : '-' }}
                                                @endif

                                            </small><br>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="font-bold">{{ __('Journal Account Summary') }}</div>
                                    <div class="table-responsive mt-2">
                                        <table class="table mb-0 ">
                                            <tr>
                                                <th data-width="40" class="text-dark">#</th>
                                                <th class="text-dark">{{ __('Account') }}</th>
                                                <th class="text-dark" width="25%">{{ __('Description') }}</th>
                                                <th class="text-dark">{{ __('Debit') }}</th>
                                                <th class="text-dark">{{ __('Credit') }}</th>
                                                <th class="text-dark">{{ __('Amount') }}</th>
                                                <th></th>
                                            </tr>

                                            @foreach ($accounts as $key => $account)
                                                <tr>
                                                    <td>{{ $key + 1 }}</td>
                                                    <td>{{ !empty($account->accounts) ? $account->accounts->code . ' - ' . $account->accounts->name : '' }}
                                                    </td>
                                                    <td>{{ !empty($account->description) ? $account->description : '-' }}
                                                    </td>
                                                    <td>{{ currency_format_with_sym($account->debit) }}</td>
                                                    <td>{{ currency_format_with_sym($account->credit) }}</td>
                                                    <td>
                                                        @if ($account->debit != 0)
                                                            {{ currency_format_with_sym($account->debit) }}
                                                        @else
                                                            {{ currency_format_with_sym($account->credit) }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="action-btn bg-danger ms-2">
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['journal.destroy', $account->id]]) !!}
                                                            <a href="#!"
                                                                class="btn btn-sm  align-items-center text-white show_confirm"
                                                                data-bs-toggle="tooltip" title='Delete'>
                                                                <i class="ti ti-trash"></i>
                                                            </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            <tfoot>
                                                <tr>
                                                    <td colspan="4"></td>
                                                    <td><b>{{ __('Total Credit') }}</b></td>
                                                    <td>{{ currency_format_with_sym($journalEntry->totalCredit()) }}</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4"></td>
                                                    <td><b>{{ __('Total Debit') }}</b></td>
                                                    <td>{{ currency_format_with_sym($journalEntry->totalDebit()) }}</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <div class="font-bold mt-2">
                                        {{ __('Description') }} : <br>
                                    </div>
                                    <small>{{ $journalEntry->description }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
