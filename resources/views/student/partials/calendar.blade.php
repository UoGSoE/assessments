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
