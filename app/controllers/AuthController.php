<?php

class AuthController extends \BaseController
{

    public function showLogin() {
        return View::make('authLogin');
    }

    public function doLogin() {
        $rules = array(
            'username' => 'required',
            'password' => 'required'
        );

        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()) {
            return Redirect::route('auth-login-path')
                ->withError('Invalid username or password, try again')
                ->withInput(Input::except('password'));
        }

        $userData = array(
            'username' => Input::get('username'),
            'password' => Input::get('password')
        );
        return Redirect::route(Auth::attempt($userData) ? 'home' : 'auth-login-path');
    }

    public function doLogout() {
        Auth::logout();
        return Redirect::to('login');
    }
}
