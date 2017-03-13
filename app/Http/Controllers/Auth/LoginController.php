<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Auth\Ldap;
use Auth;
use App\User;
use Illuminate\Http\Request;

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
    protected $redirectTo = '/';

    protected $ldap;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Ldap $ldap)
    {
        $this->middleware('guest', ['except' => 'logout']);
        $this->ldap = $ldap;
    }

    protected function attemptLogin(Request $request)
    {
        $username = trim(strtolower($request->username));
        $password = $request->password;
        $ldapUser = $this->ldap->authenticate($username, $password);
        if (!$ldapUser) {
            return $this->sendFailedLoginResponse($request);
        }
        $user = User::where('username', $username)->first();
        if (!$user) {
            $user = new User(['username' => $username, 'surname' => $ldapUser['surname'], 'forenames' => $ldapUser['forenames'], 'email' => $ldapUser['email'], 'password' => bcrypt(str_random(64))]);
            if ($this->isAStudent($username)) {
                $user->is_student = true;
            }
            $user->save();
        }
        Auth::login($user);
        return $this->sendLoginResponse($request);
    }

    protected function isAStudent($username)
    {
        if (preg_match('/^[0-9]{7}[a-z]$/', $username)) {
            return true;
        }
        return false;
    }
}
