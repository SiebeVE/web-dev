@extends('layouts.app')

@section('content')
	<div class="container">
		<div class="row">
			<div class="col-md-8 col-md-offset-2">
				<h1>Kies uw wapen</h1>
				<div class="col-sm-4">
					<a href="{{ url(Request::path(), "scissors") }}"><img alt="" src="{{ url('/img/scissors.svg') }}"></a>
				</div>
				<div class="col-sm-4">
					<a href="{{ url(Request::path(), "rock") }}"><img alt="" src="{{ url('/img/rock.svg') }}"></a>
				</div>
				<div class="col-sm-4">
					<a href="{{ url(Request::path(), "paper") }}"><img alt="" src="{{ url('/img/paper.svg') }}"></a>
				</div>
			</div>
		</div>
	</div>
@endsection