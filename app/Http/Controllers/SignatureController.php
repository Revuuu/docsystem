<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\AuditService;
use Illuminate\Support\Facades\Hash;

class SignatureController extends Controller
{
    public function index()
    {
        return view('signature.index');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'signature' => 'required|image|mimes:png,jpg,jpeg|max:2048',
            'password' => 'required|string',
        ]);

       

        $user = Auth::user();

        if ($user->signature_path) {
            Storage::disk('public')->delete($user->signature_path);
        }

        $path = $request->file('signature')->store('signatures', 'public');

        $user->update([
            'signature_path' => $path,
        ]);

        return back()->with('success', 'Signature uploaded successfully.');
    }

    public function draw(Request $request)
    {
        $request->validate([
            'signature_data' => 'required|string',
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, Auth::user()->password)) {

            return back()
    ->withInput()
    ->with('password_modal_error', 'Incorrect account password.')
->with('password_modal_form', 'drawSignatureForm');

        }

        $user = Auth::user();

        if ($user->signature_path) {
            Storage::disk('public')->delete($user->signature_path);
        }

        $image = $request->signature_data;
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);

        $filename = 'signatures/signature_' . $user->id . '_' . time() . '.png';

        Storage::disk('public')->put($filename, base64_decode($image));

        $user->update([
            'signature_path' => $filename,
        ]);

        return back()->with('success', 'Signature saved successfully.');
    }
    public function remove(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, Auth::user()->password)) {

            return back()
    ->withInput()
    ->with('password_modal_error', 'Incorrect account password.')
->with('password_modal_form', 'removeSignatureForm');

        }
        $user = auth()->user();

        if ($user->signature_path)
        {
            \Illuminate\Support\Facades\Storage::disk('public')
                ->delete($user->signature_path);

            $user->signature_path = null;

            $user->save();
        }

        return back()->with(
            'success',
            'Signature removed successfully.'
        );
    }
}