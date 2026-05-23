<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\AuditService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class SignatureController extends Controller
{
    public function index()
    {
        return view('signature.index');
    }

    public function upload(Request $request)
    {
        Log::info($request);

        $request->validate([
            'signature' => 'required|image|mimes:png,jpg,jpeg|max:2048',
            'password' => 'required|string',
        ]);

        // CHECK PASSWORD
        if (!Hash::check($request->password, Auth::user()->password)) {

            return back()
                ->withInput()
                ->with('password_modal_error', 'Incorrect account password.')
                ->with('password_modal_form', 'uploadSignatureForm');
        }

        $user = Auth::user();

        // DELETE OLD SIGNATURE
        if ($user->signature_path) {
            Storage::disk('private')->delete($user->signature_path);
        }

        // GET IMAGE FILE CONTENT
        $imageFile = $request->file('signature');

        // CONVERT IMAGE TO BASE64
        $base64 = base64_encode(file_get_contents($imageFile->getRealPath()));
        Log::info('Uploaded Signature Full Base64:', [
            'base64' => $base64
        ]);

        // GENERATE FILE NAME
        $filename = 'signatures/signature_' . $user->id . '_' . time() . '.png';

        // STORE DECODED BASE64 IMAGE
        Storage::disk('private')->put($filename, base64_decode($base64));

        // SAVE PATH
        $user->update([
            'signature_path' => $filename,
        ]);

        return back()->with('success', 'Signature uploaded successfully.');
    }

    public function draw(Request $request)
    {
        Log::info($request);
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
            Storage::disk('private')->delete($user->signature_path);
        }

        $image = preg_replace('#^data:image/\w+;base64,#i', '', $request->signature_data);
        $image = str_replace(' ', '+', $image);

        Log::info('Uploaded Signature Full Base64:', [
            'base64' => $image
        ]);

        $filename = 'signatures/signature_' . $user->id . '_' . time() . '.png';

        Storage::disk('private')->put($filename, base64_decode($image));

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
            Storage::disk('private')
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