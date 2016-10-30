<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

	/**
	 * Get the needed authorization credentials from the request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array
	 */
	protected function credentials(Request $request)
	{
		$credentials = $request->only($this->username(), 'password');
		$credentials = array_add($credentials, 'verified', true);
		return $credentials;
	}

	/**
	 * Get the failed login response instance.
	 *
	 * @param \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	protected function sendFailedLoginResponse(Request $request)
	{
		return redirect()->back()
		                 ->withInput($request->only($this->username(), 'remember'))
		                 ->withErrors([
			                 $this->username() => $this->getFailedLoginMessage($request),
		                 ]);
	}

	/**
	 * Get the failed login message.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	protected function getFailedLoginMessage(Request $request)
	{
		$current_user = User::where($this->username(), '=', $request->only($this->username()))->first();
		if ($current_user === NULL || $current_user->verified)
		{
			$message = Lang::has('auth.failed')
				? Lang::get('auth.failed')
				: 'These credentials do not match our records.';
		}
		else
		{
			$message = Lang::has('auth.failedEmail')
				? Lang::get('auth.failedEmail')
				: 'The email address is not verified, please check your mailbox.';
		}

		return $message;
	}
}
