<div class="card sticky-top" style="top:30px">
    <div class="list-group list-group-flush" id="useradd-sidenav">
        @permission('jobcategory manage')
            <a href="{{ route('job-category.index') }}"
                class="list-group-item list-group-item-action border-0 {{ request()->is('job-category*') ? 'active' : '' }}">{{ __('Job Category') }}
                <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
        @endpermission

        @permission('jobstage manage')
            <a href="{{ route('job-stage.index') }}"
                class="list-group-item list-group-item-action border-0 {{ request()->is('job-stage*') ? 'active' : '' }}">{{ __('Job Stage') }}
                <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
        @endpermission

        @permission('paysliptype manage')
            <a href="{{ route('paysliptype.index') }}" class="list-group-item list-group-item-action border-0 {{ (request()->is('paysliptype*') ? 'active' : '')}}">{{__('Salary Type')}}<div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
        @endpermission
    </div>
</div>
