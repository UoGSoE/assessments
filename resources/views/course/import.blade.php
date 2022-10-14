@extends('layouts.app')

@section('content')
<h2 class="title is-2">
    Import Courses
</h2>
<form method="POST" action="{!! route('course.import.save') !!}" enctype="multipart/form-data">
    {!! csrf_field() !!}
    <label class="label">Spreadsheet</label>
    <div class="box" style="overflow: scroll;">
        Format (All fields are required)
        <pre>Course Title | Code | Discipline | Active (Yes/No)</pre>
        <br>For example:
        <pre>Aero Engineering | ENG4037 | Aero | Yes</pre>
    </div>
    <p>
        <input name="sheet" type="file">
    </p>
    <br />
    <p class="control">
        <button type="submit" class="button is-primary is-outlined">Upload</button>
    </p>
</form>
@endsection
