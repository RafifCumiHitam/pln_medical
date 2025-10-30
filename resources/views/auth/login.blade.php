<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login - Medical Clinic</title>
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">

    <style>
        body.bg-gradient-primary {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(90deg, #4c71de,  #2a51c4); /* optional gradient */
        }

        .card-login {
            max-width: 900px;
            width: 100%;
            display: flex;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
            background-color: #fff; /* pastikan card putih */
        }

        .card-login .card-left {
            flex: 1;
            /* background: url('{{ asset("img/pln.gif") }}') no-repeat center center; */
            background-size: cover;
        }

        .card-login .card-right {
            flex: 1;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .card-login .card-right .text-center img {
            max-width: 120px;
        }

        @media (max-width: 768px) {
            .card-login {
                flex-direction: column;
            }
            .card-login .card-left {
                height: 200px;
            }
        }
    </style>
</head>
<body class="bg-gradient-primary">
    <div class="card-login">
        <div class="card-left d-none d-md-block"></div>
        <div class="card-right">
            <div class="text-center mb-4">
                <img src="{{ asset('img/pln.png') }}" alt="PLN Medical Clinic" class="img-fluid mb-3">
                <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
            </div>
            <form class="user" method="POST" action="{{ route('login') }}">
                @csrf
                <div class="form-group">
                    <input type="text" class="form-control form-control-user" name="nid" placeholder="Enter NID..." required>
                </div>
                <div class="form-group">
                    <input type="password" class="form-control form-control-user" name="password" placeholder="Password" required>
                </div>
                @if ($errors->has('nid'))
                    <div class="text-danger mb-2">{{ $errors->first('nid') }}</div>
                @endif
                <button type="submit" class="btn btn-primary btn-user btn-block">
                    Login
                </button>
            </form>
        </div>
    </div>

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>
</body>
</html>
