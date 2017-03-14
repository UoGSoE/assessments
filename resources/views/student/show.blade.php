@extends('layouts.app')

@section('content')
    <h2 class="title is-2">
        Student Details
    </h2>
    <p>
        Name : {{ $student->fullName() }}
    </p>
    <hr />
    <h3 class="title is-3">
        Feedbacks Left
    </h3>
    @foreach ($student->feedbacks()->get() as $feedback)
        <li>
            <a href="{!! route('assessment.show', $feedback->assessment_id) !!}">
                {{ $feedback->assessment->title }}
                {{ $feedback->created_at->format('d/m/Y H:i') }}
            </a>
        </li>
    @endforeach
@endsection
