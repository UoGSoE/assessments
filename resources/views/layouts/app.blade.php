<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="/css/bulma.css" rel="stylesheet">
    <link href="/css/fa/css/font-awesome.min.css" rel="stylesheet">
    <link href="/css/animate.css" rel="stylesheet">
    <link href="/css/pikaday.css" rel="stylesheet">
    <link href="/js/fullcalendar/fullcalendar.min.css" rel="stylesheet">
    <script src="/js/all.js"></script>
    <script src="/js/jquery-3.1.1.min.js"></script>
    <script src="/js/moment.js"></script>
    <script src="/js/pikaday.js"></script>
    <script src="/js/fullcalendar/fullcalendar.min.js"></script>
    <link rel="shortcut icon" href="/favicon.ico">
    <script src="/js/dropit/dropit.js"></script>
    <link href="/js/dropit/dropit.css" rel="stylesheet">
    <!-- Scripts -->
    <script>
        window.Laravel = <?php echo json_encode([
            'csrfToken' => csrf_token(),
        ]); ?>
    </script>
    <style>
        .fc-month-view .fc-time{
            display : none;
        }
    </style>
</head>
<body>
    <div id="app">
        @include('partials.navbar')
        <div class="section">
            <div class="container">
                <noscript>
                    <div class="notification is-danger is-outlined">
                        This site will not work without javascript enabled
                    </div>
                </noscript>
                @include('partials.errors')
                @include('partials.success')
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
