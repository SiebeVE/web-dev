@extends('layouts.app')

@section('content')
	<div class="container">
		<div class="row">
			<div class="col-md-8 col-md-offset-2">
				<div class="panel panel-default">
					<div class="panel-heading">Resultaat vorige gevechten</div>
					
					<div class="panel-body">
						@foreach($ranks as $date=>$period)
							<div class="periode">
								<h4>Periode: {{ $date }}</h4>
								@foreach($period as $rank)
									<p>Winnaar van competitie {{ $rank->competition->id }}: {{ $rank->user->name }}</p>
								@endforeach
							</div>
						@endforeach
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection