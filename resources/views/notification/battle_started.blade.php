<li>Een <a href="{{ url('/battle', $notification->data['battle']['id']) }}">nieuw gevecht</a> is gestart tegen:
	@foreach($notification->data['opponents'] as $opponent)
		{{ $opponent['name'] }}{{ $loop->last ? "" : ($loop->remaining == 1 ? " en " : ", ") }}
	@endforeach
</li>