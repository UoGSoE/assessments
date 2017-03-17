        {!! csrf_field() !!}
        <label class="label">Type</label>
        <p class="control">
          <span class="select">
            <select name="type">
              <option value="something">something</option>
              <option value="blah">blah</option>
              <option value="carrots">carrots</option>
              <option value="bikes">bikes</option>
            </select>
          </span>
        </p>
        <label class="label">Staff</label>
        <p class="control">
          <span class="select">
            <select name="user_id">
                @foreach ($staff as $user)
                    <option value="{{ $user->id }}">{{ $user->fullName() }}</option>
                @endforeach
            </select>
          </span>
        </p>
        @if (!$assessment->id)
          <label class="label">Course</label>
          <p class="control">
            <span class="select">
              <select name="course_id">
                  @foreach ($courses as $course)
                      <option value="{{ $course->id }}">{{ $course->code }}</option>
                  @endforeach
              </select>
            </span>
          </p>
        @endif
        <label class="label">Deadline Date</label>
        <p class="control">
            <input class="input" name="date" type="text" placeholder="dd/mm/yyyy">
        </p>
        <label class="label">Time</label>
        <p class="control">
            <input class="input" name="time" type="text" placeholder="hh:mm">
        </p>
        <label class="label">Comment</label>
        <p class="control">
          <textarea class="textarea" name="comment"></textarea>
        </p>
