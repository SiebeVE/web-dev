<div class="panel panel-default">
	<div class="panel-heading">Game settings</div>
	
	<div class="panel-body">
		<form method="post">
			{{ csrf_field() }}
			@foreach($gameSettings as $setting)
				<div class="form-group">
					<label for="{{ $setting->name }}">{{ $setting->name }} {{ $setting->type != NULL ? "(".$setting->type.")" : "" }}</label>
					<input type="text" class="form-control" id="{{ $setting->name }}" name="{{ $setting->name }}"
						   value="{{ $setting->data }}">
				</div>
			@endforeach
			<input type="submit" class="btn btn-success" value="Update">
		</form>
	</div>
</div>
<div class="panel panel-default">
	<div class="panel-heading">Competities</div>
	
	<div class="panel-body">
		@foreach($competitions as $competition)
			<div class="competitie">
				<p>Competitie {{ $competition->id }}</p>
				<p>Winnaar: {{ $competition->winner != null ? ($competition->winner->name . " (".$competition->winner->email.")") : "Geen winnaar"}}</p>
			</div>
		@endforeach
	</div>
</div>
<div class="panel panel-default">
	<div class="panel-heading">Gebruikers</div>
	
	<div class="panel-body">
		<table class="table table-striped">
			<tr>
				<th>Naam</th>
				<th>Email</th>
				<th>Admin</th>
			</tr>
		@foreach($users as $user)
			<tr>
				<td>{{$user->name}}</td>
				<td>{{$user->email}}</td>
				<td>{{$user->is_admin ? "Ja" : "Neen"}}</td>
			</tr>
		@endforeach
		</table>
	</div>
</div>