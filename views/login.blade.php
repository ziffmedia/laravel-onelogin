@extends('onelogin::layout')

@section('title', __('Login'))
@section('headline', 'Login via onelogin')

@section('image')
    <div style="background-image: url({{ asset('/svg/403.svg') }});" class="absolute pin bg-cover bg-no-repeat md:bg-left lg:bg-center">
    </div>
@endsection

@section('message', __('You will be redirected to onelogin then back to this application once successful.'))

@section('action')
    <a href="{{ $oneloginLoginUrl }}">
        <button class="bg-transparent text-grey-darkest font-bold uppercase tracking-wide py-3 px-6 border-2 border-grey-light hover:border-grey rounded-lg">
            Login
        </button>
    </a>
@endsection
