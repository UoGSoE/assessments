@extends('layouts.app')

@section('content')
    <h2 class="title is-2">
        Assessment Details
        @if ($assessment->overdue())
            <form method="POST" action="{!! route('feedback.store', $assessment->id) !!}" class="is-pulled-right">
                {!! csrf_field() !!}
                <button class="button is-danger is-outlined" type="submit">
                    Report assessment feedback as overdue
                </button>
            </form>
        @endif
    </h2>
    <p>
        Course : {{ $assessment->course->code }} {{ $assessment->course->title}}
    </p>
    <p>
        Assessment Type : {{ $assessment->type }}
    </p>
    <p>
        Due : {{ $assessment->deadline->format('d/m/Y H:i') }}
    </p>
    @if (Auth::user()->is_admin)
        <hr />
        <h3 class="title is-3">
            Feedbacks Left
        </h3>
        @foreach ($assessment->negativeFeedbacks()->get() as $feedback)
            <li>
                <a href="{!! route('student.show', $feedback->student->id) !!}">
                    {{ $feedback->student->fullName() }}
                </a>
                {{ $feedback->created_at->format('d/m/Y H:i') }}
            </li>
        @endforeach
    @endif
@endsection
