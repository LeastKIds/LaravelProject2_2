<?php

namespace App\Http\Controllers;

use App\Mail\CheckEmail;
use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Mailgun\Mailgun;

class AuthController extends Controller
{
    //
    // 맨 처음 기본적인 인증 시스템
    public function __construct() {
        $this -> middleware(['checkEmail'])
            -> except(['register','logout', 'confirmEmail','reConfirmEmail']);
    }

//    회원가입
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

        $this -> emailSession($user -> id);


        return ['success' => 1, 'message' => 'register successful and check your email'];


    }

//    로그인
    public function login(Request $request) {


        if(!auth() -> attempt($request -> only('email','password'))) {
            return ['error' => '이메일 또는 비밀번호가 맞지 않습니다.', 'success' => 0, 'user' => null];
        }

        $request -> session() -> regenerate();



        return ['user' => auth()->user(), 'success' => 1, 'message' => '로그인 성공'];


    }

//    로그아웃
    public function logout() {
        auth() -> logout();

        return ['user' => auth() -> user(), 'success' => 1, 'message' => '로그아웃 성공'];
    }

//    회원탈퇴
    public function leave() {
        $user = User::findOrFail(auth() -> user() -> id);
        $user -> delete();

        return ['success' => 1, 'message' => '회원 탈퇴'];
    }

//    로그인 체크
    public function loginCheck() {
        $user = auth() -> user();
        if($user !=null)
            return ['success' => 1, 'user' => $user, 'message' => '로그인 중'];
        else
            return ['success' => 0, 'user' => null, 'message' => '로그아웃 중'];
    }

//    비밀번호 변경
    public function updatePassword(Request $request) {
        $user = User::findorFail(auth()->user()->id);

        if(!Hash::check($request -> password, $user -> password))
            return ['success' => 0, 'message' => '비밀번호가 틀립니다.'];
        $user -> password = Hash::make($request -> newPassword);
        $user -> save();

        auth() -> logout();
        return ['success' => 1, 'message' => '비밀번호 변경'];

    }

//    이메일 인증
    public function confirmEmail(Request $request) {
        $checkEmail = EmailVerification::where('email_verifications', $request -> userURL) -> exists();
        if($checkEmail == false)
            return ['success' => 0, 'message' => '인증 만료. 다시 이메일을 받아 주세요.'];
        $checkEmail = EmailVerification::where('email_verifications', $request -> userURL) -> first();
//        dd($checkEmail -> id);
        $user = User::find($checkEmail -> user_id);
        if($user -> email_verified == 0)
            return ['success' => 0, 'message' => '이미 이메일 인증을 하셨습니다.'];
        $user -> email_verified = 1;
        $user -> save();
        return ['success' => 1, '이메일 인증이 완료되었습니다.'];
    }

//    이메일 인증 메일 다시 보내기
    public function reConfirmEmail() {
        $id = auth() -> user() -> id;
        if(EmailVerification::where('user_id', $id) -> exists())
        {
            $checkEmail = EmailVerification::where('user_id', $id) -> first();
            $sql = "drop event ".$checkEmail -> event_name.";";
            DB::statement($sql);
            $checkEmail -> delete();

        }

        $this -> emailSession($id);

        return ['success' => 1, 'message' => '이메일 인증 재발급 됨' ];
    }

//    인증 이메일 보내기
    public function emailSession($id) {
        $random = Str::random(64);
        $event = Str::random(9);
        $email = EmailVerification::create([
            'user_id' => $id,
            'email_verifications' => $random,
            'event_name' => $event.$id,
        ]);

        $timestamp = strtotime("+10 minutes");

        $sql = "CREATE EVENT ".$email -> event_name." ON SCHEDULE AT '"
            .date("Y-m-d H:i:s", $timestamp)
            ."' DO DELETE FROM email_verifications WHERE user_id = "
            .$id;

        DB::statement($sql);

        $user = User::find($id);
        $email = $user -> email;

        Mail::to($email) -> send(new CheckEmail($user, $random));

    }

}
