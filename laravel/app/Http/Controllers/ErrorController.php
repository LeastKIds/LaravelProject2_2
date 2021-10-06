<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ErrorController extends Controller
{
    //
    public function emailVerifiedFailed(){
        return ['success' => 0, 'message' => '이메일 인증이 안 되어있습니다.'];
    }

    public function isNotLogined(){
        return ['success' => 0, 'message' => '로그인이 되어 있지 않습니다.'];
    }
}
