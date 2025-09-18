<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserToken;
use App\Models\Mtoken;
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

        // Set masa berlaku token 30 hari (43200 menit)
        JWTAuth::factory()->setTTL(43200);
        $token = JWTAuth::fromUser($user);

        UserToken::create([
            'token_id' => 5,
            'user_id' => $user->id,
            'token'   => $token,
        ]);

        return response()->json([
            'user' => $user,
            'token' => $token
        ], 201);
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
        $userToken = UserToken::where('user_id', $user->id)->where('token_id', $user->status)->first();

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
        $user = auth()->user();
        $payload = auth()->payload();
        
        // Ambil waktu expired dari payload (dalam timestamp UNIX)
        $exp = $payload->get('exp');
        $now = now()->timestamp;

        // Hitung sisa waktu dalam detik
        $remainingSeconds = $exp - $now;

        // Konversi ke menit, jam, hari
        $remainingMinutes = round($remainingSeconds / 60, 2);
        $remainingHours   = round($remainingSeconds / 3600, 2);
        $remainingDays    = round($remainingSeconds / 86400, 2);

        return response()->json([
            'user' => $user,
            'expired_in' => [
                'seconds' => $remainingSeconds,
                'minutes' => $remainingMinutes,
                'hours'   => $remainingHours,
                'days'    => $remainingDays
            ]
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

    public function extendedToken()
    {
        $user = auth()->user();
        // Simpan TTL saat ini
        $defaultTTL = auth()->factory()->getTTL();

        // Set TTL ke 30 hari (43200 menit)
        auth()->factory()->setTTL(43200);

        // Buat token baru dari user yang sedang login
        $token = auth()->login(auth()->user());

        // Kembalikan TTL ke nilai semula agar tidak mempengaruhi token lain
        auth()->factory()->setTTL($defaultTTL);

        // Update atau buat token baru di tabel user_tokens
        UserToken::updateOrCreate(
            [
                'token_id' => 5,
                'user_id' => $user->id
            ],
            ['token' => $token]
        );
        
        return response()->json([
            // 'user' => $user,
            'token' => $token
        ], 200);
        // return $this->respondWithToken($token);
    }

    public function generateToken(Request $request)
    {
        $user = auth()->user();
        // Simpan TTL saat ini
        $defaultTTL = auth()->factory()->getTTL();

        $ttl = Mtoken::where('id', $request->level)->first();
        // Set TTL ke 30 hari (43200 menit)
        auth()->factory()->setTTL($ttl->duration);

        // Buat token baru dari user yang sedang login
        $token = auth()->login(auth()->user());

        // Kembalikan TTL ke nilai semula agar tidak mempengaruhi token lain
        auth()->factory()->setTTL($defaultTTL);

        // dd($request->level);

        // Update atau buat token baru di tabel user_tokens
        UserToken::updateOrCreate(
            [
                'token_id' => (int)$request->level,
                'user_id' => (int)$user->id
            ],
            ['token' => $token]
        );

        User::where('id', $user->id)
                ->update(['status' => (int)$request->level]);

        
        return response()->json([
            // 'user' => $user,
            'token' => $token
        ], 200);
        // return $this->respondWithToken($token);
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