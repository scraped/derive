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
        <script>
            window.fbAsyncInit = function() {
                FB.init({
                    appId      : '1915610145354409',
                    cookie     : true,
                    xfbml      : true,
                    version    : 'v2.8'
                });
                FB.AppEvents.logPageView();
                FB.getLoginStatus(function(response) {
                    console.log(response);
                    statusChangeCallback(response);
                });
            };

            (function(d, s, id){
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) {return;}
                js = d.createElement(s); js.id = id;
                js.src = "//connect.facebook.net/en_US/sdk.js";
                fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));

            function checkLoginState() {
                FB.getLoginStatus(function(response) {
                    statusChangeCallback(response);
                });
            }
        </script>
        <div id="app">
            <div class="content mt-lg mc">
                <div class="title m-b-md">
                    Dérive
                    <h2>Random events near you.</h2>
                </div>
                <div style="height:420px">
                    <events-search></events-search>
                </div>
                <fb:login-button
                    scope="public_profile,email"
                    onlogin="checkLoginState();">
                </fb:login-button>
            </div>
        </div>
        <script src="/js/app.js"></script>
    </body>
</html>
