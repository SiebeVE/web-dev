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
	<div class="panel-heading">Uw vorige gevechten</div>
	<div class="panel-body prev-battle">
		@foreach($previousBattle as $competition)
			<div class="competition">
				@foreach($competition as $round)
					<div class="round">
						@foreach($round as $battle)
							<div class="battle">
								@foreach($battle as $pick)
									<div class="pick">
										<p class="{{ $pick["pick"]["has_won"] ? "won" : "" }}">{{ $pick["user"]["name"] }}
											<i class="fa fa-hand-{{$pick["pick"]["pick"]}}-o"></i></p>
									</div>
								@endforeach
							</div>
						@endforeach
					</div>
				@endforeach
			</div>
		@endforeach
	</div>
</div>