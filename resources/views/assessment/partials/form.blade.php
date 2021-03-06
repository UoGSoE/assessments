        {!! csrf_field() !!}
      <div class="field">
        <label class="label" for="type">Assessment Type</label>
        <p class="control">
            <input class="input" id="type" name="type" type="text" placeholder="" value="{{ $assessment->type }}">
        </p>
      </div>
      <div class="field">
        <label class="label" for="feedback_type">Staff Feedback Type</label>
        <p class="control">
            <input class="input" id="feedback_type" name="feedback_type" type="text" placeholder="" value="{{ $assessment->feedback_type }}">
        </p>
      </div>
      <div class="field">
        <label class="label" for="staff_id">Staff</label>
        <p class="control">
          <span class="select">
            <select name="staff_id" id="staff_id">
                @foreach ($staff as $user)
                    <option value="{{ $user->id }}" @if ($assessment->staff_id == $user->id) selected @endif>{{ $user->fullName() }}</option>
                @endforeach
            </select>
          </span>
        </p>
      </div>
        @if (!$assessment->id)
      <div class="field">
          <label class="label" for="course_id">Course</label>
          <p class="control">
            <span class="select">
              <select name="course_id" id="course_id">
                  @foreach ($courses as $course)
                      <option value="{{ $course->id }}" @if ($assessment->course_id == $course->id) selected @endif>{{ $course->code }}</option>
                  @endforeach
              </select>
            </span>
          </p>
      </div>
        @endif
      <div class="field">
        <label class="label" for="datepicker">Deadline Date</label>
        <p class="control">
            <input class="input" id="datepicker" name="date" type="text" placeholder="dd/mm/yyyy" value="{{ $assessment->deadlineDate() }}">
        </p>
      </div>
      <div class="field">
        <label class="label" for="time">Time</label>
        <p class="control">
            <input class="input" id="time" name="time" type="text" placeholder="hh:mm" value="{{ $assessment->deadlineTime() }}">
        </p>
      </div>
      <div class="field">
        <label class="label" for="comment">Comment</label>
        <p class="control">
          <textarea class="textarea" id="comment" name="comment">{{ $assessment->comment }}</textarea>
        </p>
      </div>
<script>
$(document).ready(function () {
    var picker = new Pikaday({
        field: document.getElementById('datepicker'),
        format: 'DD/MM/YYYY',
    });
});
</script>
