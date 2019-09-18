@extends('layouts.app')

@section('content')
<h2 class="title is-2">
    Upload Coursework Spreadsheet
</h2>
<form method="POST" action="{!! route('coursework.update') !!}" enctype="multipart/form-data">
    {!! csrf_field() !!}
    <label class="label">Spreadsheet</label>
    <div class="box" style="overflow: scroll;">
        Format (fields with '*' are needed - others are ignored):
        <pre>course code* | level | assessment type* | feedback type* | staff | staff email* | submission deadline* | feedback deadline | given | student complaints | comments*</pre>
        Eg :
        <pre>ENG4037 | 4 | Moodle Quiz | Moodle - Graded | Angela Busse | Angela.Busse@glasgow.ac.uk | {{ now()->format('d/m/Y H:i') }} | {{ now()->addWeeks(2)->format('d/m/Y H:i') }} | No | 0 | My moodle quiz is great</pre>
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