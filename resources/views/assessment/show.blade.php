@extends('layouts.app')

@section('content')
    <h2 class="title is-2">
        Assessment Details
        @if (Auth::user()->is_admin)
            <a href="{!! route('assessment.edit', $assessment->id) !!}" class="button">
                Edit
            </a>
            <form method="POST" action="" data-href="{!! route('assessment.destroy', $assessment->id) !!}" id="delete-form">
                {!! csrf_field() !!}
                <input type="hidden" name="_method" value="DELETE">
                <button id="delete-button" class="button is-danger is-pulled-right">
                    Delete
                </button>
            </form>
        @endif
        @if ($assessment->overdue() and Auth::user()->can('can_leave_feedback', $assessment))
            <form method="POST" action="{!! route('feedback.store', $assessment->id) !!}" class="is-pulled-right">
                {!! csrf_field() !!}
                <button class="button is-danger is-outlined" type="submit">
                    Report assessment feedback as overdue
                </button>
            </form>
        @endif
    </h2>
    <p>
        Course : 
            <a href="{!! route('course.show', $assessment->course_id) !!}">
                {{ $assessment->course->code }}
            </a>
            {{ $assessment->course->title}}
    </p>
    <p>
        Set By : {{ $assessment->user->fullName() }}
    </p>
    <p>
        Assessment Type : {{ $assessment->type }}
    </p>
    <p>
        Deadline : {{ $assessment->deadline->format('d/m/Y H:i') }}
    </p>
    <p>
        Feedback Due : {{ $assessment->feedback_due->format('d/m/Y') }}
        - {{ $assessment->feedback_due->diffForHumans() }}
        @if ($assessment->feedbackFrom(Auth::user()))
                - You reported feedback late on {{ $assessment->feedbackFrom(Auth::user())->created_at->format('d/m/Y') }}
        @endif
    </p>
    <p>
        Feedback Completed :
        @if ($assessment->feedback_left)
            {{ $assessment->feedback_left->format('d/m/Y') }}
        @else
            @if (Auth::user()->is_student)
                No
            @else
                <form method="POST" action="{!! route('feedback.complete', $assessment->id) !!}
                ">
                    {!! csrf_field() !!}
                    <div class="field has-addons">
                        <p class="control">
                            <input class="input" id="datepicker" name="date" type="text" placeholder="dd/mm/yyyy">
                        </p>
                        <p class="control">
                            <button type="submit" class="button is-info">Save</button>
                        </p>
                    </div>
                </form>
            @endif
        @endif
    </p>
    @if ($assessment->comment)
        <p>
            Comments : {{ $assessment->comment }}
        </p>
    @endif
    @can('see_feedbacks', $assessment)
        <hr />
        <h3 class="title is-3">
            Feedbacks Left
        </h3>
        @foreach ($assessment->negativeFeedbacks()->get() as $feedback)
            <li>
                @if (Auth::user()->is_admin)
                    <a href="{!! route('student.show', $feedback->student->id) !!}">
                        {{ $feedback->student->fullName() }}
                    </a>
                @else
                    {{ $feedback->student->fullName() }}
                @endif
                on {{ $feedback->created_at->format('d/m/Y H:i') }}
                ({{ $feedback->created_at->diffForHumans($assessment->feedback_due) }} due date)
            </li>
        @endforeach
    @endcan
<script>
$(document).ready(function () {
    var picker = new Pikaday({ 
        field: document.getElementById('datepicker'),
        format: 'DD/MM/YYYY',
    });
    $('#delete-button').click(function (e) {
        e.preventDefault();
        $("#pop-up").addClass('animated').addClass('fadeIn').addClass('is-active');
    });
    $('#modal-cancel').click(function (e) {
        $("#pop-up").removeClass('is-active');
    });
    $('#modal-confirm').click(function (e) {
        uri = $('#delete-form').data('href');
        $('#delete-form').attr('action', uri).submit();
    });

});
</script>
<div id="pop-up" class="modal">
  <div class="modal-background"></div>
  <div class="modal-card">
    <header class="modal-card-head">
      <p class="modal-card-title">Are you sure?</p>
    </header>
    <section class="modal-card-body">
        Do you <em>really</em> want to delete this assessment?
    </section>
    <footer class="modal-card-foot">
      <a class="button is-danger" id="modal-confirm">Yes</a>
      <a class="button is-pulled-right" id="modal-cancel">No</a>
    </footer>
  </div>
</div>
@endsection
