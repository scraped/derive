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
            // In your JavaScript
            var auth_response_change_callback = function(response) {
                console.log("auth_response_change_callback");
                console.log(response);
            }

            var auth_status_change_callback = function(response) {
                console.log("auth_status_change_callback: " + response.status);
            }
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

                FB.Event.subscribe('auth.authResponseChange', auth_response_change_callback);
                FB.Event.subscribe('auth.statusChange', auth_status_change_callback);

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
                    console.log(response);
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
                <div class="fb-login-button" data-max-rows="1" data-size="large" data-button-type="login_with"
                     data-show-faces="false" data-auto-logout-link="true" data-use-continue-as="false"></div>
            </div>
        </div>
        <script src="/js/app.js"></script>
    </body>
</html>
