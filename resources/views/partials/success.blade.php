@if (session()->has('success_message'))
    <div class="notification">
        {{ session('success_message') }}
    </div>
@endif