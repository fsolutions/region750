<?php

namespace App\Http\Controllers\API;

use App\Models\Role;
use App\Models\User;
use App\Models\History;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Validator;

class PassportAuthController extends Controller
{
    /**
     * Registration Request
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function register(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'name'  => 'required|string|min:2',
            // 'email' => 'email|users',
            'phone' => 'required|string|max:50|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password'])
        ]);

        if (!isset($data['role'])) {
            $role = Role::where('slug', 'client')->first();
            $user->roles()->attach($role);
        } else {
            $role = Role::where('slug', $data['role'])->first();
            $user->roles()->attach($role);
        }

        $token = $user->createToken('Region750')->accessToken;

        History::addNew([
            'operation_name' => "Успешная регистрация, ваш ID в системе " . $user->id,
            'model_name' => get_class(new User()),
            'model_id' => $user->id,
        ]);

        $this->sendNotificationManager($user);

        return response()->json([
            'token' => $token,
            'user' => $user
        ], 200);
    }

    /**
     * Login Request
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $data = [
            'phone' => $request->phone,
            'password' => $request->password
        ];

        if (auth()->attempt($data)) {
            $token = auth()->user()->createToken('Region750')->accessToken;

            History::addNew([
                'operation_name' => "Успешный вход в систему",
                'model_name' => get_class(new User()),
                'model_id' => auth()->user()->id,
                'user_id' => auth()->user()->id
            ]);

            return response()->json([
                'token' => $token,
                'user' => auth()->user()->append('role'),
            ], 200);
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function logout()
    {
        $accessToken = auth()->user()->token();

        $refreshToken = DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $accessToken->id)
            ->update([
                'revoked' => true
            ]);

        $accessToken->revoke();

        return response()->json(['status' => 200]);
    }

    /**
     * Get auth user info
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function getUser()
    {
        return auth()->user()->append('role');
    }

    /**
     * Sent to phone link reset password
     * @param Request $request
     * @return string
     */
    function forgotPassword(Request $request)
    {
        $request->validate(['phone' => 'required|phone']);

        $status = Password::sendResetLink(
            $request->only('phone')
        );

        return $status;
    }

    /**
     * Reset password
     * @param Request $request
     * @return mixed
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'phone' => 'required|phone',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('phone', 'password', 'password_confirmation', 'token'),
            function ($user, $password) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();

                $user->setRememberToken(Str::random(60));

                event(new PasswordReset($user));
            }
        );

        return $status;
    }

    /**
     * Send notification for manager.
     *
     * @param $user
     */
    private function sendNotificationManager($user)
    {
        $users = User::whereHas('roles', function ($query) {
            $query->whereIn('slug', ['administrator', 'manager']);
        })->get();

        $parameters = [
            'place' => ['bell'],
            'link' => "/users",
            'linkText' => 'Открыть клиентов',
            'user_id' => $user->id
        ];

        Notification::locale('ru')->send($users, new NotificationsUsers("Зарегистрирован новый пользователь ID: $user->id, $user->name, $user->phone", $parameters));
    }
}
