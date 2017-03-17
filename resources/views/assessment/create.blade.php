@extends('layouts.app')

@section('content')
    <h2 class="title is-2">
        New Assessment
    </h2>
    <form method="POST" action="{!! route('assessment.store') !!}">
        @include('assessment.partials.form')
        <p class="control">
            <button type="submit" class="button is-primary is-outlined">Create</button>
        </p>
    </form>
@endsection