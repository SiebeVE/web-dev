@extends('layouts.app')

@section('content')
	<div class="container">
		<div class="row">
			<div class="col-md-8 col-md-offset-2">
				<div class="panel panel-default">
					<div class="panel-heading">Gevecht tussen {{ $userNames }}</div>
					<div class="panel-body prev-battle">
						<div class="round">
							@foreach($outcome as $battle)
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
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection