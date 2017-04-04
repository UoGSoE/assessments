@extends('layouts.app')

@section('content')
    <h2 class="title is-2">
        Upload Coursework Spreadsheet
    </h2>
    <form method="POST" action="{!! route('coursework.update') !!}" enctype="multipart/form-data">
        {!! csrf_field() !!}
        <label class="label">Spreadsheet</label>
        <div class="box">
            Format : <pre>Date | Time (HH:MM) | Course Code | Course Title | Type | Staffname | GUID | Feedback Type</pre>
            Eg : <pre>Wednesday, October 02, 2019 | 09:00 | ENG1234 | Lasers | Homework | Jenny Smith | js1x | In Person</pre>
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