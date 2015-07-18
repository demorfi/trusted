<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" href="/img/favicon.png" type="image/png" />
	<title>trusted2 - SSL certificates manager</title>

	{{ HTML::style('css/bootstrap.min.css') }}
	{{ HTML::style('css/font-awesome.min.css') }}
	{{ HTML::style('css/init.css') }}
</head>
<body>
	<div class="navbar navbar-default navbar-fixed-top" role="navigation">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
					<span class="sr-only">Toggle navigation</span>
					<i class="fa fa-bars"></i>
				</button>
				<div class="navbar-brand" href="#">trusted2</div>
			</div>
			<div class="collapse navbar-collapse">
				@if(Auth::check())
					<ul class="nav navbar-nav">
						<li @if(Route::currentRouteName() == 'certs-path') class="active" @endif>
							<a href="{{ route('certs-path') }}"><i class="fa fa-certificate"></i> Certs</a>
						</li>
						@if(Auth::user()->isAdmin())
							<li @if(Route::currentRouteName() == 'users-path') class="active" @endif>
								<a href="{{ route('users-path') }}"><i class="fa fa-users"></i> Users</a>
							</li>
							<li @if(Route::currentRouteName() == 'root-ca-path') class="active" @endif>
								<a href="{{ route('root-ca-path') }}"><i class="fa fa-university"></i> Root CA</a>
							</li>
						@endif
					</ul>
				@endif
				<ul class="nav navbar-nav navbar-right">
					<li><a href="{{ route('root-ca-install-path') }}"><i class="fa fa-download"></i> Install Root CA</a></li>

					@if(Auth::check())
						<li><a href="{{ route('auth-logout-path') }}"><i class="fa fa-sign-out"></i> Log out</a></li>
					@endif
				</ul>
			</div>
		</div>
	</div>

	<div class="container">
		@include('alerts')

		@yield('content')
	</div>

	<div class="footer">
		<div class="container">
			<p class="text-muted">Built with <i class="fa fa-heart"></i> in Berlin &amp; Munich.</p>
		</div>
	</div>

	@yield('js')
</body>
</html>
