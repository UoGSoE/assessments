@extends('layouts.app')

@section('content')
    <div class="columns">
        <div class="column">
        </div>
        <div class="column">
            <form method="POST" action="{{ url('/login') }}">
                {{ csrf_field() }}

                <div class="field">
                    <label class="label" for="username">Username (your GUID)</label>
                    <p class="control">
                        <input class="input" type="text" id="username" name="username" required autofocus>
                    </p>
                </div>
                <div class="field">
                    <label class="label" for="password">Password</label>
                    <p class="control">
                        <input class="input" type="password" id="password" name="password" required>
                    </p>
                </div>
                <br />
                <div class="field">
                    <p class="control">
                        <button class="button">Log in</button>
                    </p>
                </div>
            </form>
        </div>
        <div class="column"></div>
    </div>
@endsection
