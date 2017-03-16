@extends('layouts.app')

@section('content')
    <h2 class="title is-2">
        All Assessments
    </h2>
    <table class="table is-striped" id="assessmentTable">
        <thead>
            <tr>
                <th>Course</th>
                <th>Type</th>
                <th>Deadline</th>
                <th>Feedback Due</th>
                <th>Negative Feedbacks</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($assessments as $assessment)
                <tr @if ($assessment->isProblematic()) data-search="is-problematic" @endif>
                    <td>
                        @if ($assessment->isProblematic())
                            <span class="icon">
                              <i class="fa fa-bell animated infinite tada"></i>
                            </span>
                        @endif
                        <a href="{!! route('assessment.show', $assessment->id) !!}">
                            {{ $assessment->course->code }}
                        </a>
                         {{ $assessment->course->title }}
                    </td>
                    <td>{{ $assessment->type }}</td>
                    <td>{{ $assessment->deadline->format('Y-m-d H:i') }}</td>
                    <td>{{ $assessment->feedback_due->format('Y-m-d H:i') }}</td>
                    <td>
                        {{ $assessment->totalNegativeFeedbacks() }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <script src="/js/datatables.min.js"></script>
    <script>
        $(document).ready(function(){
            $('#assessmentTable').DataTable({
                "paging": false,
                "order": []
            });
        });
    </script>
@endsection
