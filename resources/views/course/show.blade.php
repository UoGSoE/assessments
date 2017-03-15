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
        @if (Auth::user()->isStaff())
            <div class="column">
                <h3 class="title is-3">
                    Students
                </h3>
                @foreach ($course->students as $student)
                    <li>
                        @if (Auth::user()->is_admin)
                            <a href="{!! route('student.show', $student->id) !!}">
                                {{ $student->fullName() }}
                            </a>
                        @else
                            {{ $student->fullName() }}
                        @endif
                    </li>
                @endforeach
            </div>
        @endif
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
