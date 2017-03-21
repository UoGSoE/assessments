@extends('layouts.app')

@section('content')
    <h2 class="title is-2">
        Staff Details
    </h2>
    <p>
        Name : {{ $staff->fullName() }}
    </p>
    <p>
        Email : <a href="mailto:{{ $staff->email }}">{{ $staff->email }}</a>
    </p>
    <p>
        Courses :
        @foreach ($staff->courses as $course)
            <a href="{!! route('course.show', $course->id) !!}">
                {{ $course->code }}
            </a>
        @endforeach
    </p>
    <hr />
    <p>
        <p class="control">
            <label class="checkbox">
                <input type="checkbox" id="is_admin" name="is_admin" value="1" @if ($staff->is_admin) checked @endif> Admin User?
            </label>
        </p>
    <hr />
    <h3 class="title is-3">
        Assessments
    </h3>
    @include('report.partials.assessments_table', ['assessments' => $staff->orderedAssessments()])
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('#is_admin').change(function (e) {
                $.post("{!! route('staff.toggle_admin', $staff->id) !!}");
            });
        });
    </script>
@endsection
