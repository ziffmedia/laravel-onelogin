@extends('onelogin::layout')

@section('title', __('Logged Out.'))
@section('headline', 'Logged Out.')

@section('image')
    <div style="background-image: url({{ asset('/svg/403.svg') }});" class="absolute pin bg-cover bg-no-repeat md:bg-left lg:bg-center">
    </div>
@endsection

@section('message', __('Bye, Y\'all come back now! Ya hear?'))

@section('action')
    <a href="{{ $oneloginLoginUrl }}">
        <button class="bg-transparent text-grey-darkest font-bold uppercase tracking-wide py-3 px-6 border-2 border-grey-light hover:border-grey rounded-lg">
            Login
        </button>
    </a>
@endsection