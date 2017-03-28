@extends('layouts.app')

@section('content')

    <section class="hero has-text-centered is-info">
      <div class="hero-body">
        <div class="container">
          <h1 class="title">
            School Of Engineering Student Assessments
          </h1>
          <h2 class="subtitle">
            Please log in to continue
          </h2>
        </div>
      </div>
    </section>

    <hr />

    <div class="columns">
        <div class="column">
        </div>
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
                <br />
                <p class="control">
                    <button class="button is-primary is-outlined">Log in</button>
                </p>
            </form>
        </div>
        <div class="column"></div>
    </div>

@endsection
