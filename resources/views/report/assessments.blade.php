@extends('layouts.app')

@section('content')
    <h2 class="title is-2">
        All Assessments
    </h2>
    @include('report.partials.assessments_table', ['assessments' => $assessments])
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
