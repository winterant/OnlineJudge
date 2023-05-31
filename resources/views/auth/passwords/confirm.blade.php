@extends('layouts.client')

@section('content')
  <div class="container justify-content-center">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header">{{ __('Confirm Password') }}</div>

        <div class="card-body">
          {{ __('Please confirm your password before continuing.') }}

          <form method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <div class="form-group row">
              <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

              <div class="col-md-6">
                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                  name="password" required autocomplete="current-password"
                  oninput="this.value=this.value.replace(/\s+/g,'')">

                <span class="position-absolute" style="top:0.5rem;right:2rem; cursor: pointer;"
                  onclick="toggle_password()">
                  <i id="eye-show-password" class="fa fa-lg fa-eye-slash" aria-hidden="true"></i>
                </span>

                <script>
                  function toggle_password() {
                    if ($("#eye-show-password").is('.fa-eye-slash')) {
                      $("#eye-show-password").removeClass('fa-eye-slash')
                      $("#eye-show-password").addClass('fa-eye')
                      $("#password").attr("type", "")
                    } else {
                      $("#eye-show-password").removeClass('fa-eye')
                      $("#eye-show-password").addClass('fa-eye-slash')
                      $("#password").attr("type", "password")
                    }
                  }
                </script>

                @error('password')
                  <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                  </span>
                @enderror
              </div>
            </div>

            <div class="form-group row mb-0">
              <div class="col-md-8 offset-md-4">
                <button type="submit" class="btn btn-primary">
                  {{ __('Confirm Password') }}
                </button>

                @if (Route::has('password.request'))
                  <a class="btn btn-link" href="{{ route('password.request') }}">
                    {{ __('Forgot Your Password?') }}
                  </a>
                @endif
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection
