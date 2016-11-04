<li>Hoera, je hebt gewonnen! <a href="{{url('/competition/battle', hashId($notification->data["battleId"])) }}">Klik hier om het gevecht opnieuw te bekijken</a>.
	@if($notification->data["retake"] != null)
		Er zijn meerdere winnaars, dus je hebt een <a href="{{url('/battle', hashId($notification->data["battleId"])) }}">nieuw gevecht</a>!
	@endif
</li>