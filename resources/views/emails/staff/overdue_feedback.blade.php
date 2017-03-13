@component('mail::message')
# Assessment Feedback

There are {{ $feedbacks->count() }} reports from students about assessment feedback
being overdue.  The details are :

@component('mail::table')
| Course        | Feedback Due  | Student |
| ------------- | ------------- | ------- |
@foreach ($feedbacks as $feedback)
| {{ $feedback->course->code }}       | {{ $feedback->assessment->feedback_due->format('d/m/Y') }}    | {{ $feedback->student->email }} |
@endforeach
@endcomponent

If you believe these to be inaccurate, please email someone or other.

@endcomponent
