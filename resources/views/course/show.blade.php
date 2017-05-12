@extends('layouts.app')

@section('content')
    <h2 class="title is-2">
        Course Details
    </h2>
    <dl>
        <dt>Title</dt>
        <dd>{{ $course->title }}</dd>
        <dt>Code</dt>
        <dd>{{ $course->code }}</dd>
    </dl>
    <hr />
    <div class="columns">
        @if (Auth::check() and Auth::user()->isStaff())
            <div class="column">
                <h3 class="title is-3">
                    Students
                </h3>
                @foreach ($course->students as $student)
                    <li>
                            <a href="{!! route('student.show', $student->id) !!}">
                                {{ $student->fullName() }}
                            </a>
                    </li>
                @endforeach
            </div>
        @endif
        <div class="column">
            <h3 class="title is-3">
                Assessments
            </h3>
            @foreach ($course->orderedAssessments() as $assessment)
                <li>
                    <a href="{!! route('assessment.show', $assessment->id) !!}">
                        {{ $assessment->title }}
                    </a>
                    {{ $assessment->deadline->format('d/m/Y H:i') }}
                </li>
            @endforeach
        </div>
@endsection
