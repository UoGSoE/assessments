@extends('layouts.app')

@section('content')
    <h2 class="title is-2">
        Course Details
    </h2>
    <p>
        Title : {{ $course->title }}
    </p>
    <p>
        Code : {{ $course->code }}
    </p>
    <hr />
    <div class="columns">
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
        <div class="column">
            <h3 class="title is-3">
                Assessments
            </h3>
            @foreach ($course->assessments()->orderBy('deadline')->get() as $assessment)
                <li>
                    <a href="{!! route('assessment.show', $assessment->id) !!}">
                        {{ $assessment->title }}
                    </a>
                    {{ $assessment->deadline->format('d/m/Y H:i') }}
                </li>
            @endforeach
        </div>
@endsection
