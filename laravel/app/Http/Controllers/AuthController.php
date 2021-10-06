<?php

namespace App\Http\Controllers;

use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;


class AuthController extends Controller
{
    //

    public function __construct() {
        $this -> middleware(['checkEmail']) -> except(['register','logout', 'confirmEmail']);
    }

    public function register(Request $request) {
        if(isset($request['name']))
            $name = $request['name'];
        else return ['success' => 0, 'message' => 'name is empty'];
        if(isset($request['email']))
            $email = $request['email'];
        else return ['success' => 0, 'message' => 'email is empty'];
        if(isset($request['password']))
            $password = $request['password'];
        else return ['success' => 0, 'message' => 'password is empty'];

        $validator = Validator::make(
            array(
                'name' => $name,
                'email' => $email,
                'password' => $password
            ),
            array(
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => ['required', Rules\Password::defaults()],
            )
        );

        if($validator->fails()) {
            $message = $validator->messages();
            return response($message,422);
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $random = Str::random(64);
        EmailVerification::create([
            'user_id' => $user->id,
            'email_verifications' => $random,
        ]);
        $timestamp = strtotime("+10 minutes");

        $sql = "CREATE EVENT checkEmail ON SCHEDULE AT '"
            .date("Y-m-d H:i:s", $timestamp)
            ."' DO DELETE FROM email_verifications WHERE user_id = "
            .$user -> id;

        DB::statement($sql);

        return ['success' => 1, 'message' => 'register successful and check your email'];


    }

    public function login(Request $request) {


        if(!auth() -> attempt($request -> only('email','password'))) {
            return ['error' => '이메일 또는 비밀번호가 맞지 않습니다.', 'success' => 0, 'user' => null];
        }

        $request -> session() -> regenerate();



        return ['user' => auth()->user(), 'success' => 1, 'message' => '로그인 성공'];


    }

    public function logout() {
        auth() -> logout();

        return ['user' => auth() -> user(), 'success' => 1, 'message' => '로그아웃 성공'];
    }

    public function leave() {
        $user = User::findOrFail(auth() -> user() -> id);
        $user -> delete();

        return ['success' => 1, 'message' => '회원 탈퇴'];
    }

    public function loginCheck() {
        $user = auth() -> user();
        if($user !=null)
            return ['login' => 1, 'user' => $user, 'message' => '로그인 중'];
        else
            return ['login' => 0, 'user' => null, 'message' => '로그아웃 중'];
    }

    public function updatePassword(Request $request) {
        $user = User::findorFail(auth()->user()->id);

        if(!Hash::check($request -> password, $user -> password))
            return ['success' => 0, 'message' => '비밀번호가 틀립니다.'];
        $user -> password = Hash::make($request -> newPassword);
        $user -> save();

        auth() -> logout();
        return ['success' => 1, 'message' => '비밀번호 변경'];

    }

    public function confirmEmail($userURL) {
        $checkEmail = EmailVerification::where('email_verifications', $userURL) -> exists();
        if($checkEmail == false)
            return ['success' => 0, 'message' => '인증 만료. 다시 이메일을 받아 주세요.'];
        
    }

}
