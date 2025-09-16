<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserToken;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        
        $token = JWTAuth::fromUser($user);
        
        UserToken::create([
            'user_id' => $user->id,
            'token'     => $token,
        ]);
        
        return response()->json(['user' => $user, 'token' => $token], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Email atau password salah'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function ulogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Cari user berdasarkan email
        $user = User::where('email', $request->email)->first();

        // Jika user tidak ditemukan atau password salah
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Email atau password salah'], 401);
        }

        // Ambil token dari tabel user_tokens
        $userToken = UserToken::where('user_id', $user->id)->first();

        // Jika belum ada token untuk user ini
        if (!$userToken) {
            return response()->json(['error' => 'Token belum dibuat untuk user ini'], 404);
        }

        // Kirim token yang sudah ada
        return response()->json([
            'token' => $userToken->token,
        ]);
    }

    public function cekToken()
    {
        $expires = auth()->factory()->getTTL() * 60;

        return response()->json([
            'user'      => auth()->user(),
            'expired_in' => $expires,
        ]);
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Berhasil logout']);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() * 60 // detik
        ]);
    }
}