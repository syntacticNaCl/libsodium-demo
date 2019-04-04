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

    // public function login(Request $request)
    // {
    //     $user = User::where('email', $request->email)->first();
    //     // $this->resetBcrypt($user, $request);
    //     $verified = $this->verifyOrUpgrade($user, $request);

    //     if ($verified) {
    //         echo "<p>Access granted</p>";
    //     } else {
    //         echo "<p>Uh uh uh... You didn't say the magic word</p>";
    //     }
    // }

    public function verifyOrUpgrade(User $user, Request $request): bool
    {
        // Get stored user password hash
        $row = DB::table('users')->where('email', $request->email)->select('password')->get();
        $storedPasswordHash = $row[0]->password;

        // Check to see if stored password needs to be rehashed
        $needsRehash = password_needs_rehash($storedPasswordHash, PASSWORD_ARGON2ID, [
            'memory' => 1024,
            'threads' => 2,
            'time' => 2,
        ]);

        if ($needsRehash) {
            $bcrypt = password_hash($request->password, PASSWORD_BCRYPT, [
                'cost' => 10,
            ]);

            // Verify current hash
            $verified = password_verify($request->password, $bcrypt);

            // If verification fails then access denied
            if (!$verified) {
                return false;
            }

            // Update to new hash using Laravel defaults
            // Remember these defaults should be dependent on your needs. Go as high as you can comfortably
            $passwordHash = password_hash($request->password, PASSWORD_ARGON2ID, [
                'memory' => 1024,
                'threads' => 2,
                'time' => 2,
            ]);

            $user->password = $passwordHash;
            $user->save();

            // Set new password hash to stored so we can verify again
            $storedPasswordHash = $passwordHash;

            echo "brypt upgraded to argon2id";
        }

        return password_verify($request->password, $storedPasswordHash);
    }

    public function resetBcrypt(User $user, Request $request): bool
    {
        // Hash password from user login form
        $bcryptHash = password_hash($request->password, PASSWORD_BCRYPT, [
            'cost' => 10,
        ]);

        $user->password = $bcryptHash;
        $user->save();

        return true;
    }
}
