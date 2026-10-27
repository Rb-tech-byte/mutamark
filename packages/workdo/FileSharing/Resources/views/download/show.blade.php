@php
    $file_downlod = json_decode($files_log->details);
@endphp

<div class="modal-body">
    <div class="row">
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{__('Status')}}</b></div>
            <p class="text-muted mb-4">{{$file_downlod->status}}</p>
        </div>
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{__('Country')}} </b></div>
            <p class="text-muted mb-4">{{$file_downlod->country}}</p>
        </div>
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{__('Country Code')}} </b></div>
            <p class="text-muted mb-4">{{$file_downlod->countryCode}}</p>
        </div>
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{__('Region')}}</b></div>
            <p class="mt-1">{{$file_downlod->region}}</p>
        </div>
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{__('Region Name')}}</b></div>
            <p class="mt-1">{{$file_downlod->regionName}}</p>
        </div>
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{__('City')}}</b></div>
            <p class="mt-1">{{$file_downlod->city}}</p>
        </div>
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{__('Zip')}}</b></div>
            <p class="mt-1">{{$file_downlod->zip}}</p>
        </div>
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{__('Latitude')}}</b></div>
            <p class="mt-1">{{$file_downlod->lat}}</p>
        </div>
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{__('Longitude')}}</b></div>
            <p class="mt-1">{{$file_downlod->lon}}</p>
        </div>
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{__('Timezone')}}</b></div>
            <p class="mt-1">{{$file_downlod->timezone}}</p>
        </div>
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{__('Isp')}}</b></div>
            <p class="mt-1">{{$file_downlod->isp}}</p>
        </div>
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{__('Org')}}</b></div>
            <p class="mt-1">{{$file_downlod->org}}</p>
        </div>
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{__('As')}}</b></div>
            <p class="mt-1">{{$file_downlod->as}}</p>
        </div>
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{__('IP')}}</b></div>
            <p class="mt-1">{{$file_downlod->query}}</p>
        </div>
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{__('Browser Name')}}</b></div>
            <p class="mt-1">{{$file_downlod->browser_name}}</p>
        </div>
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{__('Os Name')}}</b></div>
            <p class="mt-1">{{$file_downlod->os_name}}</p>
        </div>
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{__('Browser Language')}}</b></div>
            <p class="mt-1">{{$file_downlod->browser_language}}</p>
        </div>
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{__('Device Type')}}</b></div>
            <p class="mt-1">{{$file_downlod->device_type}}</p>
        </div>
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{__('Referrer Host')}}</b></div>
            <p class="mt-1">{{$file_downlod->referrer_host}}</p>
        </div>
        <div class="col-md-6 ">
            <div class="form-control-label"><b>{{__('Referrer Path')}}</b></div>
            <p class="mt-1">{{$file_downlod->referrer_path}}</p>
        </div>
    </div>
</div>


