<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            @if (Route::has('login'))
                <div class="top-right links">
                    @if (Auth::check())
                        <a href="{{ url('/home') }}">Home</a>
                    @else
                        <a href="{{ url('/login') }}">Login</a>
                        <a href="{{ url('/register') }}">Registreer</a>
                    @endif
                </div>
            @endif

            <div class="content">
                <div>
                    <p>Neem deel aan de grootste schaar-steen-papier competitie ter wereld!</p>
                    <p>Je hebt 24 uur de tijd om een gevecht (een heuse schaar-steen-papier) te volbrengen tegen een random gekozen speler!</p>
                    <p>Na het inschrijven moet je wachten tot er een nieuwe competitie is gestart. Deze start van zodra de vorige competitie is volbracht en je wordt automatisch ingeschreven!</p>
                </div>
                <div class="title m-b-md">
                    <a href="{{ url('/home') }}">Neem nu deel!</a>
                </div>
                <marquee scrollamount="2" direction="up" loop="true" width="100%" height="24px"><center>
                        @foreach($competitions as $competition)
                            Competitie {{$competition->id}} is gewonnen door {{ $competition->winner != null ? $competition->winner->name : "Geen winnaar"}}<br>
                            @endforeach
                    </center></marquee>
            </div>
        </div>
    </body>
</html>
