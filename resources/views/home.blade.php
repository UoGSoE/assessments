@extends('layouts.app')

@section('content')
    <h2 class="title is-2">
        Your Assessments
<!--         <a href="{{ Auth::user()->icsUrl() }}" title="Link to your assessments calendar (for iCal/Outlook)">
          <span class="icon">
            <i class="fa fa-calendar"></i>
          </span>
        </a>
 -->    </h2>
    @if (Auth::user()->isStaff())
        <p class="control is-pulled-left">
          <span class="select">
            <select id="year-selector" name="year">
              <option value="all">All Years</option>
              <option value="1">1st</option>
              <option value="2">2nd</option>
              <option value="3">3rd</option>
              <option value="4">4th</option>
              <option value="5">5th</option>
            </select>
          </span>
          &nbsp;
        </p>
    @endif
    @include('partials.calendar')
@endsection
