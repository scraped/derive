<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Dérive - Find a random event near you.</title>

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
            var statusChangeCallback = function(response) {
                console.log(response);
                switch (response.status) {
                    case 'unknown':
                        window.fbToken = null;
                        break;
                    case 'connected':
                        window.fbToken = response.authResponse.accessToken;
                        break;
                    default:
                        break;
                }
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
                    statusChangeCallback(response);
                });

                FB.Event.subscribe('auth.statusChange', statusChangeCallback);
            };

            (function(d, s, id){
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) {return;}
                js = d.createElement(s); js.id = id;
                js.src = "//connect.facebook.net/en_US/sdk.js";
                fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));
        </script>
        <div id="app">
            <div class="container content mt-lg mc">
                <div class="row">
                    <div class="col-md-8 col-md-offset-2">
                        <div class="title m-b-md">
                            Dérive
                            <h2>Random events near you.</h2>
                        </div>
                        <div style="height:420px">
                            <events-search></events-search>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8 col-md-offset-2">
                        <div class="fb-login">
                            <div class="fb-login-button" data-max-rows="1" data-size="large" data-button-type="login_with"
                                 data-show-faces="false" data-auto-logout-link="true" data-use-continue-as="false"
                                data-scope="user_events"></div>
                            <p>
                                <em>Log in with Facebook to include non-public events.</em>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="/js/app.js"></script>
    </body>
</html>
