@extends('layouts.invoicepayheader')
@section('page-title')
    {{ __('Contract Detail') }}
@endsection
@section('action-btn')
    <div>
        <a href="{{ route('contract.download.pdf', \Crypt::encrypt($contract->id)) }}" target="_blanks" class="btn btn-sm btn-primary py-2" title="{{ __('Download') }}" data-bs-toggle="tooltip" data-bs-placement="top"><i class="ti ti-download"></i>
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" id="printTable">
                <div class="card-body">
                    <div class="row invoice-title mt-2">
                        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-12 ">
                            <img src="{{ $img }}" style="max-width: 150px;" />
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-12 text-end">
                            <h3 class="invoice-number">
                                {{ \Modules\Contract\Entities\Contract::contractNumberFormat($contract->id, $contract->created_by, $contract->workspace) }}
                            </h3>
                        </div>
                    </div>
                    <div class="row align-items-center mb-4">
                        <div class="col-sm-6 mb-3 mb-sm-0 mt-3">
                            <div class="mb-3">
                                <h6 class="d-inline-block m-0 d-print-none">{{ __('Contract Type  :') }}</h6>
                                <span class="col-md-8"><span
                                        class="text-md">{{ isset($contract_type->name) ? $contract_type->name : '' }}</span></span>
                            </div>
                            <div class="col-lg-6 col-md-8">
                                <h6 class="d-inline-block m-0 d-print-none">{{ __('Contract Value   :') }}</h6>
                                <span class="col-md-8"><span
                                        class="text-md">{{ currency_format_with_sym($contract->value, $contract->created_by, $contract->workspace) }}</span></span>
                            </div>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <div>
                                <div class="float-end">
                                    <div class="">
                                        <h6 class="d-inline-block m-0 d-print-none">{{ __('Start Date   :') }}</h6>
                                        <span class="col-md-8"><span
                                                class="text-md">{{ company_date_formate($contract->start_date, $contract->created_by, $contract->workspace) }}</span></span>
                                    </div>
                                    <div class="mt-3">
                                        <h6 class="d-inline-block m-0 d-print-none">{{ __('End Date   :') }}</h6>
                                        <span class="col-md-8"><span
                                                class="text-md">{{ company_date_formate($contract->end_date, $contract->created_by, $contract->workspace) }}</span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p data-v-f2a183a6="">

                    <div>{!! $contract->notes !!}</div>
                    <br>
                    <div>{!! $contract->description !!}</div>
                    </p>

                    <div class="row">
                        <div class="col-6">
                            @if ($contract->owner_signature)
                                <div>
                                    <img width="150px" src="{{ $contract->owner_signature }}">
                                </div>
                            @endif
                            <div>
                                <h5 class="mt-auto">{{ __('Company Signature') }}</h5>
                            </div>
                        </div>
                        <div class="col-6 text-end">
                            @if ($contract->client_signature)
                                <div>
                                    <img width="150px" src="{{ $contract->client_signature }}">
                                </div>
                            @endif
                            <h5 class="mt-auto">{{ __('Client Signature') }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
