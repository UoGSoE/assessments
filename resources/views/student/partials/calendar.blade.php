    <div id="calendar">
    </div>
    <script>
        $(document).ready(function () {
            $('#calendar').fullCalendar({
                events: {!! $assessments !!},
                eventClick: function(calEvent, jsEvent, view) {
                    if (calEvent.mine) {
                        window.location.replace("/assessment/" + calEvent.id);
                    }
                    console.log(calEvent);
                },
            });
        });
    </script>
