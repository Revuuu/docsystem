<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SignatureController extends Controller
{
    public function index()
    {
        return view('signature.index');
    }

    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'signature' => 'required|image|mimes:png,jpg,jpeg|max:2048',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('dashboard')
                ->withErrors($validator, 'signature')
                ->with('signature_error', 'Signature upload failed. Please check the form.')
                ->with('section', 'profile');
        }

        if (!Hash::check($request->password, Auth::user()->password)) {
            return redirect()
                ->route('dashboard')
                ->with('password_modal_error', 'Incorrect account password.')
                ->with('password_modal_form', 'uploadSignatureForm')
                ->with('signature_error', 'Incorrect account password.')
                ->with('section', 'profile');
        }

        try {
            $user = Auth::user();

            if ($user->signature_path) {
                Storage::disk('private')->delete($user->signature_path);
            }

            $file = $request->file('signature');

            $imageContent = file_get_contents($file->getRealPath());
            $mime = $file->getMimeType();

            $base64 = 'data:' . $mime . ';base64,' . base64_encode($imageContent);

            Log::info('UPLOAD BASE64:');
            Log::info($base64);

            session()->flash('uploaded_signature_base64', $base64);

            $image = preg_replace('#^data:image/\w+;base64,#i', '', $base64);
            $image = str_replace(' ', '+', $image);

            $filename = 'signatures/signature_' . $user->id . '_' . time() . '.png';

            Storage::disk('private')->put($filename, base64_decode($image));

            $user->signature_path = $filename;
            $user->save();

            return redirect()
                ->route('dashboard')
                ->with('signature_success', 'Signature uploaded successfully.')
                ->with('section', 'profile');

        } catch (\Exception $e) {
            return redirect()
                ->route('dashboard')
                ->with('signature_error', 'Something went wrong while uploading your signature.')
                ->with('section', 'profile');
        }
    }

    public function draw(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'signature_data' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('dashboard')
                ->withErrors($validator, 'signature')
                ->with('signature_error', 'Signature drawing failed. Please draw your signature first.')
                ->with('section', 'profile');
        }

        if (!Hash::check($request->password, Auth::user()->password)) {
            return redirect()
                ->route('dashboard')
                ->with('password_modal_error', 'Incorrect account password.')
                ->with('password_modal_form', 'drawSignatureForm')
                ->with('signature_error', 'Incorrect account password.')
                ->with('section', 'profile');
        }

        try {
            $user = Auth::user();

            if ($user->signature_path) {
                Storage::disk('private')->delete($user->signature_path);
            }

            $image = $request->signature_data;
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);

            $filename = 'signatures/signature_' . $user->id . '_' . time() . '.png';

            Storage::disk('private')->put($filename, base64_decode($image));

            $user->update([
                'signature_path' => $filename,
            ]);

            session()->flash('drawn_signature_base64', $request->signature_data);

            return redirect()
                ->route('dashboard')
                ->with('signature_success', 'Signature saved successfully.')
                ->with('section', 'profile');

        } catch (\Exception $e) {
            return redirect()
                ->route('dashboard')
                ->with('signature_error', 'Something went wrong while saving your drawn signature.')
                ->with('section', 'profile');
        }
    }

    public function remove(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('dashboard')
                ->withErrors($validator, 'signature')
                ->with('signature_error', 'Password is required to remove your signature.')
                ->with('section', 'profile');
        }

        if (!Hash::check($request->password, Auth::user()->password)) {
            return redirect()
                ->route('dashboard')
                ->with('password_modal_error', 'Incorrect account password.')
                ->with('password_modal_form', 'removeSignatureForm')
                ->with('signature_error', 'Incorrect account password.')
                ->with('section', 'profile');
        }

        try {
            $user = Auth::user();

            if ($user->signature_path) {
                Storage::disk('private')->delete($user->signature_path);

                $user->signature_path = null;
                $user->save();
            }

            return redirect()
                ->route('dashboard')
                ->with('signature_success', 'Signature removed successfully.')
                ->with('section', 'profile');

        } catch (\Exception $e) {
            return redirect()
                ->route('dashboard')
                ->with('signature_error', 'Something went wrong while removing your signature.')
                ->with('section', 'profile');
        }
    }
}