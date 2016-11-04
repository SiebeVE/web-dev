<?php

namespace App\Http\Controllers\Auth;

use App\Notifications\EmailConfirm;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after login / registration.
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
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
	    // Make the first the admin
	    $is_admin = false;
	    if(count(User::get()) == 0)
	    {
		    $is_admin = true;
	    }
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
	        'is_admin' => $is_admin
        ]);
    }

	/**
	 * Handle a registration request for the application.
	 *
	 * @param Request $request
	 * @param AppMailer $mailer
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 * @throws \Illuminate\Foundation\Validation\ValidationException
	 */
	public function register(Request $request)
	{
		$validator = $this->validator($request->all());

		if ($validator->fails())
		{
			$this->throwValidationException(
				$request, $validator
			);
		}

		//Auth::login($this->create($request->all()));
		$user = $this->create($request->all());

		$user->notify(new EmailConfirm($user));

		flashToastr("info", "Check Email", "Check your mailbox for the confirmation email.");


		//return view('auth.register');
		return redirect("login");
		//return redirect($this->redirectPath());
	}

	/**
	 * Send confirmation mail registration
	 *
	 * @param $token
	 *
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function confirmEmail($token)
	{
		$user = User::where('token_mail', $token)->firstOrFail()->confirmEmail();

		flashToastr("success", "Email confirmed", "Your email is confirmed, you can now login.");

		return redirect("login");
	}

}
