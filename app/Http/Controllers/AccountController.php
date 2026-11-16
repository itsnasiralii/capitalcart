<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AccountController extends Controller
{
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password:web'],
            'password' => ['required', 'string', 'min:12', 'max:72', 'confirmed', 'different:current_password'],
        ]);

        $user = $request->user();
        $user->forceFill(['password' => $validated['password'], 'remember_token' => Str::random(60)])->save();
        $request->session()->regenerate(true);
        Log::notice('Account password changed', ['user_id' => $user->id]);
        return back()->with('account_message', 'Your password has been changed.');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password:web'],
            'confirm_delete' => ['accepted'],
        ]);
        $id = $request->user()->getKey();
        DB::transaction(function () use ($id) {
            $user = User::lockForUpdate()->findOrFail($id);
            abort_if($user->is_admin, 403);
            Review::where('user_id', $id)->delete();
            $user->delete();
        });
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('privacy')->with('account_message', 'Your customer account has been deleted.');
    }
}
