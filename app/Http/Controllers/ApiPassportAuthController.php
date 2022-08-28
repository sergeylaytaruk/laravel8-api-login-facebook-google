<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\LinkedSocialAccount;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Two\User as ProviderUser;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;

class ApiPassportAuthController extends Controller
{
    private const API_AUTH_TOKEN = 'ffsauth';
    /**
     * Registration Req
     */
    public function register(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|min:4',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);
        $routeAction = $request->route()->getAction();
        $idRole = 1;
        if (isset($routeAction['role'])) {
            switch ($routeAction['role']) {
                case 'seller':
                    $idRole = 1;
                    break;
                case 'client':
                    $idRole = 2;
                    break;
                default:
                    $idRole = 1;
            }
        }
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'id_role' => $idRole,
        ]);

        $token = $user->createToken($this::API_AUTH_TOKEN)->accessToken;

        return response()->json([
            'token' => $token,
            'message' => 'Ви успішно зареєструвались. Зараз Ви можете увійти у додаток.'
        ], 200);
    }

    /**
     * Login Req
     */
    public function login(Request $request)
    {
        $data = [
            'email' => $request->email,
            'password' => $request->password,
            //'active' => 1,
        ];
        if (auth()->attempt($data)) {
            if (auth()->user()->active == 0) {
                return response()->json(['message' => 'Ваш аккаунт деактивовано.'], 401);
            }
            $token = auth()->user()->createToken($this::API_AUTH_TOKEN)->accessToken;
            return response()->json([
                'token' => $token,
                'id_user' => auth()->user()->id,
            ], 200)->withHeaders([
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Headers' => 'Access-Control-Allow-Origin, Accept',
            ]);
        } else {
            return response()->json(['message' => 'Помилка авторизації.'], 401);
        }
    }

    public function logout(Request $request)
    {
        $accessToken = $request->user()->token();
        $accessToken->revoke();
        return response()->json(['message' => 'Ви успішно вийшли.', 'id' => $accessToken->id], 200);
    }

    public function userInfo()
    {
        $user = auth()->user();
        return response()->json(['user' => $user], 200);
    }

    public function forgotPassword(Request $request)
    {
        $email = $request->input('email');
        $user = User::where([
            ['email', '=', $email]
        ])->first();
        if (!empty($user)) {
            //Uuid::generate(5,'test', Uuid::NS_DNS);
            $user->forgotten_password_code = mb_strtoupper(mb_substr(Str::uuid()->toString(), 0, 6));
            $user->forgotten_password_created_at = Carbon::now();
        }
        if ($user->save()) {
            $data = [
                'code' => $user->forgotten_password_code
            ];
            Mail::send('mail-forgot-password', $data, function($message) use ($user) {
                $message->to($user['email']);
                $message->subject('Код для відновлення паролю.');
            });

            return response()->json(['message' => 'На ваш email було надіслано код для збросу пароля.'], 200);
        } else {
            return response()->json(['message' => 'Помилка виконання запиту.'], 200);
        }
    }

    public function confirmCodeForgotPassword(Request $request)
    {
        $email = $request->input('email');
        $code = mb_strtoupper($request->input('code'));
        $user = User::where([
            ['email', '=', $email],
            ['forgotten_password_code', '=', $code],
            ['forgotten_password_code_confirmed', '=', 0],
        ])->first();
        if (!empty($user)) {
            $user->forgotten_password_code_confirmed = 1;
        }
        if ($user->save()) {
            return response()->json(['message' => 'Код перевірено. Ви можете змінити ваш пароль.'], 200);
        } else {
            return response()->json(['message' => 'Помилка виконання запиту.'], 200);
        }
    }

    public function changeForgotPassword(Request $request)
    {
        $email = $request->input('email');
        $code = mb_strtoupper($request->input('code'));
        $password = $request->input('password');
        $user = User::where([
            ['email', '=', $email],
            ['forgotten_password_code', '=', $code],
            ['forgotten_password_code_confirmed', '=', 1],
        ])->first();
        if (!empty($user)) {
            $user->forgotten_password_code_confirmed = 0;
            $user->forgotten_password_code = null;
            $user->forgotten_password_created_at = null;
            $user->password = bcrypt($password);
        }
        if ($user->save()) {
            return response()->json(['message' => 'Ваш пароль було змінено.'], 200);
        } else {
            return response()->json(['message' => 'Помилка виконання запиту.'], 200);
        }
    }

    public function socialLogin(Request $request)
    {
        $providerUser = null;

        $accessToken = request()->get('access_token');
        $provider = request()->get('provider'); // google | facebook
        //Log::debug("socialLogin request data=", [$accessToken, $provider]);
        $token = null;
        try {
            $providerUser = Socialite::driver($provider)->userFromToken($accessToken);
        } catch (\Exception $ex) {
            //Log::debug("socialLogin ERROR =", [$ex->getMessage()]);
            return response()->json(['token' => $token], 500);
        }
        //Log::debug("socialLogin providerUser =", [$providerUser]);

        if ($providerUser) {
            $user = $this->findOrCreate($providerUser, $provider);
            Log::debug("socialLogin userd =", [$user]);
            $token = $user->createToken($this::API_AUTH_TOKEN)->accessToken;
        }
        return response()->json(['token' => $token], 200);
    }

    protected function findOrCreate(ProviderUser $providerUser, string $provider): User
    {
        //Log::debug("findOrCreate=", [$provider, $providerUser->getId()]);
        $linkedSocialAccount = LinkedSocialAccount::where('provider_name', $provider)
            ->where('provider_id', $providerUser->getId())
            ->first();

        if ($linkedSocialAccount) {
            return $linkedSocialAccount->user;
        } else {
            $user = null;
            if ($email = $providerUser->getEmail()) {
                $user = User::where('email', $email)->first();
            }

            if (!$user) {
                $user = User::create([
                    'name' => $providerUser->getName(),
                    'email' => $providerUser->getEmail(),
                    'password' => bcrypt('123456789'),
                    'id_role' => 2,
                ]);
            }
            //Log::debug("findOrCreate USER=", [$user->id, $user]);

            $user->linkedSocialAccounts()->create([
                'provider_id' => $providerUser->getId(),
                'provider_name' => $provider,
                'id_user' => $user->id,
            ]);

            return $user;
        }
    }
}

