@extends('layouts.app')

@section('content')

<nav class="level">
  <div class="level-left">
    <div class="level-item">
      <p class="subtitle is-2">
        Assessment Details
      </p>
      @if (Auth::user()->is_admin)
        <p>&nbsp;
          <a href="{!! route('assessment.edit', $assessment->id) !!}" id="edit-assessment-button" class="button" title="Edit">
              <span class="icon">
                <i class="fa fa-edit"></i>
              </span>
          </a>
        </p>
        @endif
    </div>
  </div>

  <div class="level-right">
        @if (Auth::user()->is_admin)
            <form method="POST" action="" data-href="{!! route('assessment.destroy', $assessment->id) !!}" id="delete-form">
                {!! csrf_field() !!}
                <input type="hidden" name="_method" value="DELETE">
                <button id="delete-button" class="button is-danger is-pulled-right" title="Delete">
                    <span class="icon">
                      <i class="fa fa-trash"></i>
                    </span>
                </button>
            </form>
        @endif
        @if ($assessment->overdue() and Auth::user()->can('leave_feedback', $assessment))
            <form method="POST" action="{!! route('feedback.store', $assessment->id) !!}" class="is-pulled-right">
                {!! csrf_field() !!}
                <button class="button is-danger is-outlined" type="submit">
                    Report assessment feedback as overdue
                </button>
            </form>
        @endif
  </div>
</nav>
    <dl>
        <dt>Course</dt>
        <dd>
            <a href="{!! route('course.show', $assessment->course_id) !!}">
                {{ $assessment->course->code }}
            </a>
            {{ $assessment->course->title}}
        </dd>
        <dt>Set By</dt>
        <dd>{{ $assessment->staff->fullName() }}</dd>
        <dt>Assessment Type</dt>
        <dd>{{ $assessment->type }}</dd>
        <dt>Deadline</dt>
        <dd>{{ $assessment->deadline->format('d/m/Y H:i') }}</dd>
        <dt>Feedback Due</dt>
        <dd>
            {{ $assessment->feedback_due->format('d/m/Y') }} - {{ $assessment->feedback_due->diffForHumans() }}
            @if ($assessment->hasFeedbackFrom(Auth::user()))
                    <em>- You reported feedback late on {{ $assessment->hasFeedbackFrom(Auth::user())->created_at->format('d/m/Y') }}</em>
            @endif
        </dd>
        <dt>Feedback Completed</dt>
        <dd>
            @if ($assessment->feedback_left)
                {{ $assessment->feedback_left->format('d/m/Y') }}
            @else
                @can('complete_feedback', $assessment)
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
                @else
                    No
                @endcan
            @endif
        </dd>
        @if ($assessment->comment)
            <dt>Comments</dt>
            <dd>{{ $assessment->comment }}</dd>
        @endif
    </dl>
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
