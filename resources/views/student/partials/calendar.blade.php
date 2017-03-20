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
                eventRender: function eventRender( event, element, view ) {
                    return ['all', event.year].indexOf($('#year-selector').val()) >= 0
                }
            });
            $('#year-selector').on('change',function(){
                $('#calendar').fullCalendar('rerenderEvents');
            })
        });
    </script>
