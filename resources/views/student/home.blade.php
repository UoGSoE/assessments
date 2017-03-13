@extends('layouts.app')

@section('content')
    <h2 class="title is-2">
        Your Assessments
    </h2>
    <script>
        assessments = {{{ $assessments }}}
    </script>
@endsection
