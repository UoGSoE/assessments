@extends('layouts.app')

@section('content')
    <h2 class="title is-2">
        Your Assessments
    </h2>
    <div id="calendar">
    </div>
    <script>
        $(document).ready(function () {
            $('#calendar').fullCalendar({
                events: {!! $assessments !!},
                //header: { center: 'month,agendaWeek,agendaDay' },
                eventClick: function(calEvent, jsEvent, view) {
                    window.location.replace("/assessment/" + calEvent.id);
                },
            });
        });
    </script>
@endsection
