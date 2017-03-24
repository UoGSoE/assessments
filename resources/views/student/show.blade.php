@extends('layouts.app')

@section('content')
    <h2 class="title is-2">
        Student Details
    </h2>
    <dl>
        <dt>Name</dt>
        <dd>{{ $student->fullName() }}</dd>
        <dt>Email</dt>
        <dd><a href="mailto:{{ $student->email }}">{{ $student->email }}</a></dd>
        <dt>Courses</dt>
        <dd>
            @foreach ($student->courses as $course)
                <a href="{!! route('course.show', $course->id) !!}">
                    {{ $course->code }}
                </a>
            @endforeach
        </dd>
    </dl>
    @if ($student->hasLeftFeedbacks())
        <hr />
        <h3 class="title is-3">
            Feedbacks Left
        </h3>
        @foreach ($student->feedbacks()->get() as $feedback)
            <li>
                <a href="{!! route('assessment.show', $feedback->assessment_id) !!}">
                    {{ $feedback->assessment->title }}
                </a>
                {{ $feedback->created_at->format('d/m/Y H:i') }}
                ({{ $feedback->created_at->diffForHumans($feedback->assessment->feedback_due) }} due date)
            </li>
        @endforeach
    @endif
    <hr />
    <h3 class="title is-3">
        Assessments for {{ $student->fullName() }}
    </h3>
    @include('partials.calendar', ['assessments' => $student->assessmentsAsJson()])
@endsection
