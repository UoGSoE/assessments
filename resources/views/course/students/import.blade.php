@extends('layouts.app')

@section('content')
<h2 class="title is-2">
    Import Student Course Allocations
</h2>
<form method="POST" action="{!! route('course.students.import.save') !!}" enctype="multipart/form-data">
    {!! csrf_field() !!}
    <label class="label">Spreadsheet</label>
    <div class="box" style="overflow: scroll;">
        <span class="has-text-danger">Please ensure all courses are uploaded to the database first.</span><br>
        Format (All fields are required)
        <pre>Forenames | Surname | GUID | Course Code</pre>
        <br>For example:
        <pre>Jane | Smith | 123456789S | ENG1000</pre>
    </div>
    <p>
        <input name="sheet" type="file">
    </p>
    <br />
    <p class="control">
        <button type="submit" class="button is-primary is-outlined">Upload student course allocations</button>
    </p>
</form>
@endsection
