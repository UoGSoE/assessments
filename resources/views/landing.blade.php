@extends('layouts.app')

@section('content')
  <p class="subtitle">
    Below is the list of all Engineering assessments.  If you want to get a personalised
    view or report feedback as being late, please <a href="/login">log in</a>.
  </p>
        <p class="control is-pulled-left">
          <span class="select">
            <select id="year-selector" name="year">
              <option value="all" @if (Request::get('year') == 'all') selected @endif>All Years</option>
              <option value="1" @if (Request::get('year') == '1') selected @endif>1st</option>
              <option value="2" @if (Request::get('year') == '2') selected @endif>2nd</option>
              <option value="3" @if (Request::get('year') == '3') selected @endif>3rd</option>
              <option value="4" @if (Request::get('year') == '4') selected @endif>4th</option>
              <option value="5" @if (Request::get('year') == '5') selected @endif>5th</option>
            </select>
          </span>
          &nbsp;
        </p>
    @include('partials.calendar')
@endsection
