@extends('layouts.app')

@section('content')
	<div class="container">
		<div class="row">
			<div class="col-md-8 col-md-offset-2">
				@if(Auth::user()->is_admin)
					@include('home.admin')
				@else
					@include('home.user')
				@endif
			</div>
		</div>
	</div>
@endsection
