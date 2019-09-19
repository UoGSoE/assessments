@extends('layouts.app')

@section('content')
    <nav class="level">
      <div class="level-left">
        <div class="level-item">
          <h2 class="title is-2">
              Staff Report
          </h2>
        </div>
        <div class="level-item">
            <a href="{!! route('export.staff') !!}" id="export-excel-button" class="button" title="Export As Excel" aria-label="Export as Excel">
              <span class="icon">
                <i class="fa fa-download"></i>
              </span>
            </a>
        </div>
      </div>
    </nav>
    <table class="table is-striped" id="staffTable">
        <thead>
            <tr>
                <th>Name</th>
                <th>Assessments</th>
                <th>Student Feedback</th>
                <th>Missed Deadlines</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($staff as $user)
                <tr>
                    <td>
                        <a href="{!! route('staff.show', $user->id) !!}">
                            {{ $user->fullName() }}
                        </a>
                        @if ($user->is_admin)
                            <span class="icon is-admin-{{ $user->id }}" title="Admin">
                              <i class="fa fa-user-circle"></i>
                            </span>
                        @endif
                    </td>
                    <td>{{ $user->numberOfAssessments() }}</td>
                    <td>{{ $user->totalStudentFeedbacks() }}</td>
                    <td>{{ $user->numberOfMissedDeadlines() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <script src="/js/datatables.min.js"></script>
    <script>
        $(document).ready(function(){
            $('#staffTable').DataTable({
                "paging": false,
                "order": []
            });
        });
    </script>
@endsection
