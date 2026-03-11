<x-app-layout>

<h2>Change Password</h2>

@if (session('status'))
    <div style="color:green;">
        {{ session('status') }}
    </div>
@endif

{{-- Show Validation Errors --}}
@if ($errors->any())
    <div style="color:red;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('password.update') }}">
    @csrf
    @method('PUT')

    <input type="password" name="current_password" placeholder="Current Password" autocomplete="current-password" required>
    <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
    <br><br>

    <input type="password" name="password" placeholder="New Password" autocomplete="new-password" required>
    <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
    <br><br>

    <input type="password" name="password_confirmation" placeholder="Confirm Password" autocomplete="new-password" required>
    <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
    <br><br>

    <button type="submit">Update Password</button>

</form>

</x-app-layout>
