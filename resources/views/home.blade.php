@extends('layouts.app')

@section('content')
	<div class="container">
		<div class="row">
			<div class="col-md-8 col-md-offset-2">
				<div class="panel panel-default">
					<div class="panel-heading">Notificaties</div>
					
					<div class="panel-body">
						<ul>
							@foreach(Auth::user()->unreadNotifications as $notification)
								@include('notification.'.snake_case(class_basename($notification->type)))
							@endforeach
						</ul>
					</div>
				</div>
				<div class="panel panel-default">
					<div class="panel-heading">Uw huidige gevechten</div>
					
					<div class="panel-body">
						@if(count($battle) > 0)
							<div class="col-md-4">
								<a href="{{ url('/battle', $battle->id) }}">
									@foreach($opponents as $opponent)
										{{ $opponent->name }}{{ $loop->last ? "" : ($loop->remaining == 1 ? " en " : ", ") }}
									@endforeach
								</a>
							</div>
							<div class="col-md-4">{{ $battle->opponent }}</div>
						@else
							<div class="col-md-12">U heeft al uw gevechten al gespeeld.</div>
						@endif
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
