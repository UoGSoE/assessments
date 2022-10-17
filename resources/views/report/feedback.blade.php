@extends('layouts.app')

@section('content')
<nav class="level">
  <div class="level-left">
    <div class="level-item">
      <h2 class="title is-2">
        Feedback Report
      </h2>
    </div>

  </div>
  <div class="level-right">
    <div class="level-item">
      @if (Auth::user()->is_admin)
      <form method="POST" action="" data-href="{!! route('admin.clearold') !!}" id="delete-form" class="is-pulled-right">
        {!! csrf_field() !!}
        <input type="hidden" name="_method" value="DELETE">
        <button id="delete-button" class="button is-danger" title="Delete ALL Old Data" aria-label="Delete ALL old data">
          <span class="icon">
            <i class="fa fa-trash"></i>
          </span>
        </button>
      </form>
      @endif
    </div>
    <div class="level-item">
        <div class="dropdown is-right is-hoverable">
        <div class="dropdown-trigger">
            <button class="button" aria-haspopup="true" aria-controls="dropdown-menu6">
                <span>More</span>
                <span class="icon is-small">
                    <i class="fa fa-angle-double-down" aria-hidden="true"></i>
                </span>
            </button>
        </div>
        <div class="dropdown-menu" id="dropdown-menu6" role="menu">
            <div class="dropdown-content">
                <a href="{!! route('assessment.create') !!}" id="add-assessment-button" class="dropdown-item" title="Add new assessment" aria-label="Add new assessment">
                    <span class="icon">
                        <i class="fa fa-plus"></i>
                    </span>
                    Add new assessment
                </a>
                <a href="{!! route('export.assessments') !!}" id="export-excel-button" class="dropdown-item" title="Export As Excel" aria-label="Export as Excel">
                    <span class="icon">
                        <i class="fa fa-file-excel-o"></i>
                    </span>
                    Export as Excel
                </a>
                <a href="{!! route('coursework.edit') !!}" id="upload-coursework-button" class="dropdown-item" title="Import Coursework Sheet" aria-label="Import coursework sheet">
                    <span class="icon">
                        <i class="fa fa-upload"></i>
                    </span>
                    Import coursework sheet
                </a>
                <a href="{!! route('report.staff') !!}" id="staff-report-button" class="dropdown-item" title="Staff Report" aria-label="Staff Report">
                    <span class="icon">
                        <i class="fa fa-user"></i>
                    </span>
                    Staff Report
                </a>
                <hr class="dropdown-divider">
                <a href="{!! route('course.import') !!}" id="import-courses-button" class="dropdown-item" title="Import courses" aria-label="Import courses">
                    <span class="icon">
                        <i class="fa fa-upload"></i>
                    </span>
                    Import courses
                </a>
                <a href="{!! route('course.students.import') !!}" id="import-student-allocations-button" class="dropdown-item" title="Import student allocations" aria-label="Import student allocations">
                    <span class="icon">
                        <i class="fa fa-upload"></i>
                    </span>
                    Import student allocations
                </a>
                <a href="{!! route('course.staff.import') !!}" id="import-staff-allocations-button" class="dropdown-item" title="Import staff allocations" aria-label="Import staff allocations">
                    <span class="icon">
                        <i class="fa fa-upload"></i>
                    </span>
                    Import staff allocations
                </a>
            </div>
        </div>
    </div>
  </div>
</nav>
<table class="table is-striped is-narrow datatable" id="feedback-table">
  <thead>
    <tr>
      <th>Course</th>
      <th>Level</th>
      <th>Assessment Type</th>
      <th>Feedback Type</th>
      <th>Staff</th>
      <th>Submission Date</th>
      <th>Feedback Deadline</th>
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
      <td>
        {{ $assessment->course->level }}
      </td>
      <td>{{ $assessment->type }}</td>
      <td>{{ $assessment->feedback_type }}</td>
      <td>
        <a href="{!! route('staff.show', $assessment->staff->id) !!}">
          {{ $assessment->staff->fullName() }}
        </a>
      </td>
      <td>
        {{ $assessment->deadline->format('d/m/Y H:i') }}
      </td>
      <td>{{ $assessment->feedback_due->format('Y-m-d') }}</td>
      <td>{{ $assessment->reportSignedOff() }}</td>
      <td>{{ $assessment->totalNegativeFeedbacks() }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
<script src="/js/datatables.min.js"></script>
<script>
  $(document).ready(function() {
    $('#feedback-table').DataTable({
      "paging": false,
      "order": []
    });
  });
</script>
<script>
  $(document).ready(function() {
    $('#delete-button').click(function(e) {
      e.preventDefault();
      $("#pop-up").addClass('animated').addClass('fadeIn').addClass('is-active');
    });
    $('#modal-cancel').click(function(e) {
      $("#pop-up").removeClass('is-active');
    });
    $('#modal-confirm').click(function(e) {
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
      <a class="button is-danger" id="modal-confirm" href="#">Yes</a>
      <a class="button is-pulled-right" id="modal-cancel" href="#">No</a>
    </footer>
  </div>
</div>
@endsection
