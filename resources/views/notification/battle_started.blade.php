<li>Een nieuw gevecht is gestart tegen:
	@foreach($notification->data['opponents'] as $opponent)
		{{ $opponent['name'] }}{{ $loop->last ? "" : ($loop->remaining == 1 ? " en " : ", ") }}
	@endforeach
</li>