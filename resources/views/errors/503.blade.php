@extends('errors::terminal')

@section('title', __('Service Unavailable'))
@section('code', '503')
@section('message', __($exception->getMessage() ?: 'We are updating the version, please check back in 2 minutes.'))
