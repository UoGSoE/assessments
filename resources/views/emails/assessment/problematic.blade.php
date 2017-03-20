@component('mail::message')
# Problematic Assessment

The following assessment has been reported as having late feedback by >30% of students on the course.

@component('mail::button', ['url' => route('assessment.show', $assessment->id)])
{{ $assessment->title }} (feedback due {{ $assessment->feedback_due->format('d/m/Y') }})
@endcomponent

@endcomponent
