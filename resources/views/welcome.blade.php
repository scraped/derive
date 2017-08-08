<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        <style>
        </style>

        <script>
            window.Laravel = {!! json_encode(['csrfToken' => csrf_token()]) !!}
        </script>
    </head>
    <body>
        <div id="app">
            <div class="content mt-lg mc">
                <div class="title m-b-md">
                    Dérive
                    <h2>Random events near you.</h2>
                </div>
                <div style="height:420px">
                    <events-search></events-search>
                </div>
            </div>
        </div>
        <script src="/js/app.js"></script>
    </body>
</html>
