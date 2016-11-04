<li>Je hebt een gevecht tegen
	@foreach($notification->data['opponents'] as $opponent)
		{{ $opponent["name"] }}{{ $loop->last ? "" : ($loop->remaining == 1 ? " en " : ", ") }}
	@endforeach
	gespeeld en {{ $notification->data['pick'] }} gekozen.
</li>