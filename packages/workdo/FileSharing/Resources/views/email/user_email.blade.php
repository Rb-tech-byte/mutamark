@php
    $path = get_file($file->file_path);

    $originalString = $path;
    $substringToRemove = 'uploads/filesshare/';
    $fileName = str_replace($substringToRemove, '', $originalString);

@endphp
@component('mail::message')
{{__('Hello')}},<br>
{{ __('You have received a file from') }} {{ company_setting('company_name') }}.<br>
{{ __('The total size of the file is') }} : {{$file->file_size }}

@component('mail::button', [
        'url' => route('file.shared.link', \Illuminate\Support\Facades\Crypt::encrypt($file->id))])
{{ __('Download file here') }}
@endcomponent

{{ __('Thanks') }},<br>
{{ config('app.name') }}
@endcomponent
