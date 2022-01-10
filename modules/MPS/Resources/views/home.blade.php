@extends('mps::layouts.app')

@section('content')
    @verbatim
        <router-view v-hotkey.native="keymap"></router-view>
    @endverbatim
@endsection
