@extends('layouts.app')

@section('content')
    <h2 class="title is-2">
        Edit Assessment
    </h2>
    <form method="POST" action="{!! route('assessment.update', $assessment->id) !!}">
        @include('assessment.partials.form')
        <p class="control">
            <button type="submit" class="button">Update</button>
        </p>
    </form>
@endsection