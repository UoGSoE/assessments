    <div id="calendar">
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                plugins: ['dayGrid'],
                events: {!!$assessments!!},
                eventRender: function eventRender(info) {
                    var year = $('#year-selector').val();
                    return ['all', info.event.extendedProps.year].indexOf(year) >= 0
                },
                weekends: false,
                displayEventTime: false
            });

            calendar.render();

            $('#year-selector').on('change', function() {
                if (history.pushState) {
                    var newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?year=' + this.value;
                    window.history.pushState({
                        path: newurl
                    }, '', newurl);
                }
                calendar.rerenderEvents();
            })
        });
    </script>