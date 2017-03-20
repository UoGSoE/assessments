@extends('layouts.app')

@section('content')
    <h2 class="title is-2">
        Feedback Report
        <a href="{!! route('assessment.create') !!}" class="button is-pulled-right">
            Add New Assessment
        </a>
        <a href="{!! route('export.assessments') !!}" class="button is-pulled-right">
            Export As Excel
        </a>
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
@endsection
