@extends('layouts.admin')

@section('title', 'Manage Profile')

@php
    $roleName = optional($user->roles->first())->name ?? ucwords(str_replace('-', ' ', $user->role ?? 'User'));
    $profileNameSource = trim((string) $user->name);
    $profileInitials = collect(preg_split('/\s+/', $profileNameSource))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->implode('');
    $profileInitials = $profileInitials !== '' ? $profileInitials : 'U';
    $profileHue = abs(crc32($profileNameSource ?: 'user')) % 360;
    $profileBg = "hsl({$profileHue}, 65%, 45%)";

    $companyName = optional($user->tenant)->company_name ?? 'No Company';
    $companyInitials = collect(preg_split('/\s+/', trim($companyName)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->implode('');
    $companyInitials = $companyInitials !== '' ? $companyInitials : 'NC';
    $companyHue = abs(crc32($companyName ?: 'company')) % 360;
    $companyBg = "hsl({$companyHue}, 60%, 42%)";
@endphp

@section('content')
    <div class="top-header">
        <h1>Manage Profile</h1>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px;">
        <div class="card">
            <h3>Profile Picture</h3>
            <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 14px;">
                <div style="width: 74px; height: 74px; border-radius: 50%; overflow: hidden; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 24px; background: {{ $profileBg }};">
                    @if($user->profile_photo_path)
                        <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="Profile Picture" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        {{ $profileInitials }}
                    @endif
                </div>

                <form action="{{ route('profile.avatar.update') }}" method="POST" enctype="multipart/form-data" style="display: flex; gap: 8px; align-items: center;">
                    @csrf
                    <label for="profile_photo" style="width: 34px; height: 34px; border-radius: 50%; background: #0EA5E9; color: white; display: inline-flex; align-items: center; justify-content: center; cursor: pointer;">
                        <i class="fas fa-camera"></i>
                    </label>
                    <input type="file" id="profile_photo" name="profile_photo" accept="image/*" style="display: none;" onchange="this.form.submit()">
                </form>

                <form action="{{ route('profile.avatar.delete') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="padding: 8px 12px; border: none; border-radius: 6px; background: #DC2626; color: white; cursor: pointer; font-weight: 600;">
                        Delete
                    </button>
                </form>
            </div>
            @error('profile_photo')
                <span style="color: red; font-size: 12px;">{{ $message }}</span>
            @enderror

            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div style="margin-bottom: 14px;">
                    <label for="name" style="display:block;margin-bottom:6px;font-weight:700;">Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                        style="width:100%;padding:10px;border:1px solid #DBEAFE;border-radius:6px;">
                </div>

                <div style="margin-bottom: 14px;">
                    <label for="email" style="display:block;margin-bottom:6px;font-weight:700;">Email (Read Only)</label>
                    <input type="email" id="email" value="{{ $user->email }}" readonly
                        style="width:100%;padding:10px;border:1px solid #E2E8F0;border-radius:6px;background:#F8FAFC;">
                </div>

                <div style="margin-bottom: 14px;">
                    <label for="phone" style="display:block;margin-bottom:6px;font-weight:700;">Phone</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" pattern="^09\d{9}$" maxlength="11" minlength="11" inputmode="numeric"
                        placeholder="09XXXXXXXXX"
                        style="width:100%;padding:10px;border:1px solid #DBEAFE;border-radius:6px;">
                    <p style="margin-top: 6px; color: #475569; font-size: 12px; font-weight: 600;">
                        Enter an 11-digit Philippine number starting with 09 (numbers only).
                    </p>
                </div>

                <div style="margin-bottom: 14px;">
                    <label for="secondary_phone" style="display:block;margin-bottom:6px;font-weight:700;">Secondary Phone</label>
                    <input type="text" id="secondary_phone" name="secondary_phone" value="{{ old('secondary_phone', $user->secondary_phone) }}" pattern="^09\d{9}$" maxlength="11" minlength="11" inputmode="numeric"
                        placeholder="{{ $user->secondary_phone ?: '09XXXXXXXXX' }}"
                        style="width:100%;padding:10px;border:1px solid #DBEAFE;border-radius:6px;">
                    <p style="margin-top: 6px; color: #475569; font-size: 12px; font-weight: 600;">
                        Enter an 11-digit Philippine number starting with 09 (numbers only).
                    </p>
                    <label style="margin-top:6px;display:flex;align-items:center;gap:8px;font-size:12px;font-weight:600;color:#475569;">
                        <input type="checkbox" name="remove_secondary_phone" value="1"> Delete secondary phone
                    </label>
                </div>

                <button type="submit" style="padding:10px 16px;border:none;border-radius:6px;background:#2563EB;color:#fff;cursor:pointer;font-weight:600;">
                    Save Profile
                </button>
            </form>
        </div>

        <div class="card">
            <h3>Account Details</h3>
            <div style="margin-bottom: 12px; font-weight: 700; color: #334155;">Role (Read Only): {{ $roleName }}</div>
            <div style="margin-bottom: 12px; font-weight: 700; color: #334155;">Last Login: {{ optional($user->last_login_at)->format('Y-m-d H:i') ?? 'N/A' }}</div>
            <div style="margin-bottom: 16px; font-weight: 700; color: #334155;">Account Created Date: {{ $user->created_at->format('Y-m-d H:i') }}</div>

            <div style="margin-bottom: 16px; padding: 12px; border: 1px solid #E2E8F0; border-radius: 8px;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-weight: 700;">Notification Toggle (Static)</span>
                    <label style="display:inline-block;position:relative;width:44px;height:24px;">
                        <input type="checkbox" checked disabled style="opacity:0;width:0;height:0;">
                        <span style="position:absolute;inset:0;background:#2563EB;border-radius:999px;"></span>
                        <span style="position:absolute;top:3px;left:22px;width:18px;height:18px;background:#fff;border-radius:50%;"></span>
                    </label>
                </div>
            </div>

            <button type="button" id="openPasswordModal" style="padding:10px 16px;border:none;border-radius:6px;background:#0EA5E9;color:#fff;cursor:pointer;margin-bottom:12px;font-weight:600;">
                Change Password
            </button>
        </div>
    </div>

    @if($user->tenant)
        <div class="card" style="margin-top: 18px;">
            <h3>Company</h3>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
                <div style="width:58px;height:58px;border-radius:50%;overflow:hidden;display:flex;align-items:center;justify-content:center;color:#fff;font-size:20px;font-weight:700;background:{{ $companyBg }};">
                    @if($user->tenant->logo_path)
                        <img src="{{ asset('storage/' . $user->tenant->logo_path) }}" alt="Company Logo" style="width:100%;height:100%;object-fit:cover;">
                    @else
                        {{ $companyInitials }}
                    @endif
                </div>
                <div style="font-weight:700;">{{ $companyName }}</div>
            </div>

            @if($user->hasRole('account-owner'))
                <div style="display:flex; gap:8px; align-items:center;">
                    <form action="{{ route('profile.company-logo.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <label for="company_logo" style="display:inline-block;padding:8px 12px;border:none;border-radius:6px;background:#0EA5E9;color:#fff;cursor:pointer;font-weight:600;font-size:14px;height:38px;box-sizing:border-box;line-height:22px;vertical-align:middle;">
                            <i class="fas fa-camera"></i> Upload Company Logo
                        </label>
                        <input id="company_logo" type="file" name="company_logo" accept="image/*" style="display:none;" onchange="this.form.submit()">
                    </form>
                    <form action="{{ route('profile.company-logo.delete') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="padding:8px 12px;border:none;border-radius:6px;background:#DC2626;color:#fff;cursor:pointer;font-weight:600;font-size:14px;height:38px;box-sizing:border-box;line-height:22px;">
                            Delete Company Logo
                        </button>
                    </form>
                </div>
            @endif
        </div>
    @endif

    {{-- Modal: Change Password --}}
    <div id="passwordModal" class="modal-overlay" style="display: none;">
        <div class="modal-box password-modal-box">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="margin: 0;">Change Password</h3>
                <button type="button" id="closePasswordModal" class="modal-close-btn">&times;</button>
            </div>
            <form id="passwordModalForm" action="{{ route('profile.password.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div style="margin-bottom: 16px;">
                    <label for="old_password" style="display: block; margin-bottom: 6px; font-weight: 600;">Old Password</label>
                    <input type="password" id="old_password" name="old_password" required
                        style="width: 100%; padding: 10px; border: 1px solid #E2E8F0; border-radius: 6px;">
                </div>
                <div style="margin-bottom: 16px;">
                    <label for="new_password" style="display: block; margin-bottom: 6px; font-weight: 600;">New Password</label>
                    <input type="password" id="new_password" name="new_password" required
                        style="width: 100%; padding: 10px; border: 1px solid #E2E8F0; border-radius: 6px;">
                </div>
                <div style="margin-bottom: 16px;">
                    <label for="new_password_confirmation" style="display: block; margin-bottom: 6px; font-weight: 600;">Confirm New Password</label>
                    <input type="password" id="new_password_confirmation" name="new_password_confirmation" required
                        style="width: 100%; padding: 10px; border: 1px solid #E2E8F0; border-radius: 6px;">
                </div>
                <p style="margin-bottom: 16px; color: #475569; font-size: 12px; font-weight: 600;">
                    12-14 chars, uppercase, lowercase, number, and special character.
                </p>
                <div style="display: flex; gap: 8px;">
                    <button type="submit" style="padding: 8px 16px; background-color: #2563EB; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                        Confirm Password Change
                    </button>
                    <button type="button" id="cancelPasswordModal" style="padding: 8px 16px; background-color: #E2E8F0; color: #475569; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 20px; }
        .modal-box { background: #fff; border-radius: 8px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); }
        .password-modal-box { width: 100%; max-width: 480px; }
        .modal-close-btn { background: none; border: none; font-size: 28px; cursor: pointer; color: #64748B; line-height: 1; padding: 0 4px; }
        .modal-close-btn:hover { color: #1E293B; }
    </style>
@endsection

@section('scripts')
    <script>
        // Password Modal
        var passwordModal = document.getElementById('passwordModal');
        var openPasswordModal = document.getElementById('openPasswordModal');
        var closePasswordModal = document.getElementById('closePasswordModal');
        var cancelPasswordModal = document.getElementById('cancelPasswordModal');
        var passwordModalForm = document.getElementById('passwordModalForm');

        if (openPasswordModal && passwordModal) {
            openPasswordModal.addEventListener('click', function() {
                passwordModal.style.display = 'flex';
                if (passwordModalForm) {
                    passwordModalForm.reset();
                }
            });
        }

        function closePasswordModalFunc() {
            if (passwordModal) passwordModal.style.display = 'none';
            if (passwordModalForm) passwordModalForm.reset();
        }

        if (closePasswordModal) closePasswordModal.addEventListener('click', closePasswordModalFunc);
        if (cancelPasswordModal) cancelPasswordModal.addEventListener('click', closePasswordModalFunc);
        if (passwordModal) {
            passwordModal.addEventListener('click', function(e) {
                if (e.target === passwordModal) closePasswordModalFunc();
            });
        }

        // Phone fields - numbers only
        var phone = document.getElementById('phone');
        var secondaryPhone = document.getElementById('secondary_phone');

        function restrictToNumbers(input) {
            if (!input) return;
            input.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '');
            });
            input.addEventListener('keypress', function(e) {
                if (!/[\d]/.test(e.key) && !e.ctrlKey && !e.metaKey && e.key !== 'Backspace' && e.key !== 'Tab') {
                    e.preventDefault();
                }
            });
        }

        restrictToNumbers(phone);
        restrictToNumbers(secondaryPhone);
    </script>
@endsection
