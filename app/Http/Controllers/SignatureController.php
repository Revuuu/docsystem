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
    Log::info($request->all());

    $request->validate([
        'signature' => 'required|image|mimes:png,jpg,jpeg|max:2048',
        'password' => 'required|string',
    ]);

    if (!Hash::check($request->password, Auth::user()->password)) {

        return redirect()
            ->route('dashboard', ['section' => 'profile'])
            ->with(
                'password_modal_error',
                'Incorrect account password.'
            )
            ->with(
                'password_modal_form',
                'uploadSignatureForm'
            );
    }

    $user = Auth::user();

    if ($user->signature_path) {
        Storage::disk('private')
            ->delete($user->signature_path);
    }

    /*
    |--------------------------------------------------------------------------
    | Convert Uploaded Image To Base64
    |--------------------------------------------------------------------------
    */

    $file = $request->file('signature');

    $imageContent = file_get_contents(
        $file->getRealPath()
    );

    $mime = $file->getMimeType();

    $base64 =
        'data:' .
        $mime .
        ';base64,' .
        base64_encode($imageContent);

    /*
    |--------------------------------------------------------------------------
    | Browser Console Debug
    |--------------------------------------------------------------------------
    */

    Log::info('UPLOAD BASE64:');
    Log::info($base64);

    session()->flash(
    'uploaded_signature_base64',
    $base64
);

    /*
    |--------------------------------------------------------------------------
    | Decode Same As Draw Signature
    |--------------------------------------------------------------------------
    */

    $image = preg_replace(
        '#^data:image/\w+;base64,#i',
        '',
        $base64
    );

    $image = str_replace(' ', '+', $image);

    $filename =
        'signatures/signature_' .
        $user->id .
        '_' .
        time() .
        '.png';

    Storage::disk('private')->put(
        $filename,
        base64_decode($image)
    );

    /*
    |--------------------------------------------------------------------------
    | Save Path
    |--------------------------------------------------------------------------
    */

    $user->signature_path = $filename;
    $user->save();

    session()->flash(
    'uploaded_signature_base64',
     $base64
);
    return back()->with(
        'success',
        'Signature uploaded successfully.'
    );
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
                ->route('dashboard', ['section' => 'profile'])
                ->with('password_modal_error', 'Incorrect account password.')
                ->with('password_modal_form', 'drawSignatureForm');
        }

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

        session()->flash(
            'drawn_signature_base64',
            $request->signature_data
        );
        return back()->with('success', 'Signature saved successfully.');
    }
    public function remove(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, Auth::user()->password)) {

            return back()
                ->route('dashboard', ['section' => 'profile'])
                ->with('password_modal_error', 'Incorrect account password.')
                ->with('password_modal_form', 'removeSignatureForm');
        }
        $user = auth()->user();

        if ($user->signature_path)
        {
            \Illuminate\Support\Facades\Storage::disk('private')
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