@extends('layouts.app')

@section('content')
    <div class="columns">
        <div class="column"></div>
        <div class="column">
            <form method="POST" action="{{ url('/login') }}">
                {{ csrf_field() }}

                <label>Username (your GUID)</label>
                <p class="control">
                    <input class="input" type="text" name="username" required autofocus>
                </p>
                <label>Password</label>
                <p class="control">
                    <input class="input" type="password" name="password" required>
                </p>
                <button class="button is-primary is-outlined">Log in</button>
            </form>
        </div>
        <div class="column"></div>
    </div>
@endsection
