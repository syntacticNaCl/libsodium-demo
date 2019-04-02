<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\DB;

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
        $this->middleware('guest')->except('logout');
    }

    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->get();
        $verified = $this->verifyOrUpgrade($user, $request);

        if ($verified) {
            echo "<p>Access granted</p>";
        } else {
            echo "<p>Uh uh uh... You didn't say the magic word</p>";
        }
    }

    public function verifyOrUpgrade($user, $request): bool
    {
        // Get stored user password hash
        $row = DB::table('users')->where('email', $request->email)->select('password')->get();
        $storedPasswordHash = $row[0]->password;

        // Hash password from user login form
        $possibleUserPasswordHash = password_hash($request->password, PASSWORD_BCRYPT, [
            'cost' => 10,
        ]);

        // Check to see if password needs to be rehashed
        $needsRehash = password_needs_rehash($possibleUserPasswordHash, PASSWORD_ARGON2ID, [
            'memory' => 1024,
            'threads' => 2,
            'time' => 2,
        ]);
        var_dump($needsRehash);

        return true;

        if ($needsRehash) {
            $bcrypt = password_hash($request->password, PASSWORD_BCRYPT, [
                'cost' => 10,
            ]);

            // Verify current hash
            $verified = password_verify($request->password, $storedPasswordHash);

            if (!$oldPasswordVerify) {
                return false;
            }

            // Update to new hash

            // Using Laravel defaults
            // Remember these defaults should be dependent on your needs. Go as high as you can comfortably
            $passwordHash = password_hash($request->password, PASSWORD_ARGON2ID, [
                'memory' => 1024,
                'threads' => 2,
                'time' => 2,
            ]);

            // $user->password = $passwordHash;
            // $user->save();

            // Set new password hash to stored so we can verify again
            $storedPasswordHash = $passwordHash;
        }

        return password_verify($request->password, $storedPasswordHash);
    }
}
