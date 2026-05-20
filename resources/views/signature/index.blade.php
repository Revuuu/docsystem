@extends('layouts.app')

@section('title', 'My Signature')

@section('content')

<div class="main-container">

    <div class="sidebar">

        <div class="sidebar-logo">
            DOCSYSTEM
        </div>

        <div class="sidebar-user">
            <div class="user-avatar">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>

            <h2>{{ auth()->user()->name }}</h2>

            <span class="user-role-badge">
                {{ ucfirst(auth()->user()->role) }}
            </span>
        </div>

        <nav class="sidebar-nav">
            <a class="nav-btn" href="{{ route('dashboard') }}">
                Dashboard
            </a>

            <a class="nav-btn" href="{{ route('signature.index') }}">
                My Signature
            </a>
        </nav>

        <div class="logout-wrap">
            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit" class="btn-logout">
                    Logout
                </button>
            </form>
        </div>

    </div>

    <div class="content">

        <h1 class="section-title">My Signature</h1>

        @if($errors->signature->any())

    <div class="alert alert-error">

        {{ $errors->signature->first('password') }}

    </div>

@endif

        @if(auth()->user()->signature_path)
            <div class="signature-preview-card">

                <h3>Current Signature</h3>

                <img src="{{ asset('storage/' . auth()->user()->signature_path) }}"
                    class="signature-preview"
                    alt="Signature">

                <form id="removeSignatureForm"
      method="POST"
                    action="{{ route('signature.remove') }}"
                      onsubmit="return openPasswordModal(event, this)">

                    @csrf
                    @method('DELETE')
               <input type="hidden"
       name="password"
       class="password-hidden-input">
                      <button type="button"
            class="btn-remove-signature"
          >

                        Remove Signature
                    </button>

                </form>

            </div>
        @endif

        <div class="signature-grid">

            <div class="form-card">
                <h3>Upload Signature Image</h3>

            <form id="uploadSignatureForm"
      method="POST"
      action="{{ route('signature.upload') }}"
      enctype="multipart/form-data"
      class="user-form"
      onsubmit="return openPasswordModal(event, this)"
      >
      

    @csrf

                    <div class="form-group">
                        <label>Signature Image</label>
                        <input type="file" name="signature" accept="image/*" required>
                    </div>
             <input type="hidden"
       name="password"
       class="password-hidden-input">
                <button type="submit"
            class="btn-submit"
         >


                        Upload Signature
                    </button>
                </form>
            </div>

            <div class="form-card">
                <h3>Draw Signature</h3>

                <form id="drawSignatureForm" method="POST"
                        action="{{ route('signature.draw') }}"
                       
                        class="user-form"  onsubmit="
        saveDrawnSignature();
        return openPasswordModal(event, this);
    ">

                    @csrf

                    <canvas id="signatureCanvas"
                            width="500"
                            height="200"
                            class="signature-canvas"></canvas>

                    <input type="hidden"
                           name="signature_data"
                           id="signatureData">

                    <button type="button"
                            class="btn-clear-signature"
                            onclick="clearSignatureCanvas()">
                        Clear
                    </button>
<input type="hidden"
       name="password"
       class="password-hidden-input">
                 <button type="submit"
            class="btn-submit"
            onclick="
                saveDrawnSignature();
                openPasswordModal(
                    document.getElementById('drawSignatureForm')
                );
            ">
                        Save Drawn Signature
                    </button>
                </form>
            </div>

        </div>

    </div>

</div>

@include('partials.password-modal')
@endsection