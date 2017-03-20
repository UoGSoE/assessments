@extends('layouts.app')

@section('content')
    <h2 class="title is-2">
        Feedback Report
        <a href="{!! route('assessment.create') !!}" id="add-assessment-button" class="button" title="Add new assessment">
          <span class="icon">
            <i class="fa fa-plus"></i>
          </span>
        </a>
        <a href="{!! route('export.assessments') !!}" id="export-excel-button" class="button" title="Export As Excel">
          <span class="icon">
            <i class="fa fa-download"></i>
          </span>
        </a>
        @if (Auth::user()->is_admin)
            <form method="POST" action="" data-href="{!! route('admin.clearold') !!}" id="delete-form" class="is-pulled-right">
                {!! csrf_field() !!}
                <input type="hidden" name="_method" value="DELETE">
                <button id="delete-button" class="button is-danger is-pulled-right" title="Delete ALL Old Data">
                  <span class="icon">
                    <i class="fa fa-trash"></i>
                  </span>
                </button>
            </form>
        @endif
    </h2>
    <table class="table is-striped datatable" id="feedback-table">
      <thead>
        <tr>
          <th>Course</th>
          <th>Type</th>
          <th>Staff</th>
          <th>Deadline</th>
          <th>Given</th>
          <th>Complaints</th>
        </tr>
      </thead>
      <tbody>
      @foreach ($assessments as $assessment)
        <tr @if ($assessment->isProblematic()) data-search="is-problematic" @endif>
          <td>
            <a href="{!! route('assessment.show', $assessment->id) !!}">
                {{ $assessment->course->code }}
            </a>
          </td>
          <td>{{ $assessment->type }}</td>
          <td>{{ $assessment->staff->fullName() }}</td>
          <td>{{ $assessment->deadline->format('Y-m-d H:i') }}</td>
          <td>{{ $assessment->reportFeedbackLeft() }}</td>
          <td>{{ $assessment->totalNegativeFeedbacks() }}</td>
        </tr>
      @endforeach
      </tbody>
    </table>
    <script src="/js/datatables.min.js"></script>
    <script>
        $(document).ready(function(){
            $('#feedback-table').DataTable({
                "paging": false,
                "order": []
            });
        });
    </script>
<script>
$(document).ready(function () {
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
        Do you <em>really</em> want to delete all old data?
    </section>
    <footer class="modal-card-foot">
      <a class="button is-danger" id="modal-confirm">Yes</a>
      <a class="button is-pulled-right" id="modal-cancel">No</a>
    </footer>
  </div>
</div>
@endsection