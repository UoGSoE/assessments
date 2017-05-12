    <div id="calendar">
    </div>
    <script>
        $(document).ready(function () {
            $('#calendar').fullCalendar({
                events: {!! $assessments !!},
                eventClick: function(calEvent, jsEvent, view) {
                    window.location.replace("/assessment/" + calEvent.id);
                },
                eventRender: function eventRender( event, element, view ) {
                    var year = $('#year-selector').val();
                    return ['all', event.year].indexOf(year) >= 0
                },
                weekends: false
            });
            $('#year-selector').on('change',function(){
                if (history.pushState) {
                    var newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?year=' + this.value;
                    window.history.pushState({path:newurl},'',newurl);
                }
                $('#calendar').fullCalendar('rerenderEvents');
            })
        });
    </script>
