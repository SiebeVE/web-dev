<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckIfPermissionForBattle
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
	    // Fetch the battle
	    $battle = $request->route()->parameter("battle");

	    // Check if user is playing this battle
	    if( $battle->decodedId() != Auth::user()->battle_id)
	    {
		    // User is not playing it, so abort
		    abort("401", "This user doesn't have the permission to pick on this battle.");
	    }
	    // User is playing it, move on
	    return $next($request);
    }
}
