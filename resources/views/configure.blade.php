@extends('statamic::layout')

@section('title', __('Configure Acumbamail'))

@section('content')

    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('acumbamail.index'),
        'title' => 'Acumbamail'
    ])

    <acumbamail-publish-form
        title="{{ $title }}"
        initial-action="{{ $action }}"
        :blueprint="{{ json_encode($blueprint) }}"
        :initial-meta='{{ empty($meta) ? '{}' : json_encode($meta) }}'
        :initial-values='{{ empty($values) ? '{}' : json_encode($values) }}'
        :initial-localizations="[]"
        initial-site=""
    ></acumbamail-publish-form>

@stop
