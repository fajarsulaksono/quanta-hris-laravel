@php
    $demos = [
        ['role' => 'Admin', 'color' => '#7c3aed', 'email' => 'admin@smartcool.id', 'password' => 'admin123'],
        ['role' => 'CEO', 'color' => '#d97706', 'email' => 'ceo@smartcool.id', 'password' => 'ceo123'],
        ['role' => 'Manager HRD', 'color' => '#4f46e5', 'email' => 'manager.hrd1@smartcool.id', 'password' => 'managerhrd123'],
        ['role' => 'Staff HRD', 'color' => '#0d9488', 'email' => 'staff.hrd1@smartcool.id', 'password' => 'staffhrd123'],
        ['role' => 'Manager Finance', 'color' => '#059669', 'email' => 'manager.finance@smartcool.id', 'password' => 'managerfinance123'],
        ['role' => 'Account Payment', 'color' => '#059669', 'email' => 'account.payment1@smartcool.id', 'password' => 'accountpayment123'],
        ['role' => 'Karyawan', 'color' => '#2563eb', 'email' => 'rizal@smartcool.id', 'password' => 'rizal123'],
    ];
@endphp

<div style="margin-top: 1.25rem;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:.5rem;">
        <span style="font-size:.8rem; font-weight:600; color:#334155;">Akun Demo</span>
        <span style="font-size:.7rem; color:#64748b;">Klik untuk mengisi otomatis</span>
    </div>
    <div style="border:1px solid #e2e8f0; border-radius:.75rem; padding:.5rem; background:#f8fafc; display:flex; flex-direction:column; gap:.25rem;">
        @foreach ($demos as $demo)
            <button
                type="button"
                x-on:click="$wire.set('data.email', '{{ $demo['email'] }}'); $wire.set('data.password', '{{ $demo['password'] }}')"
                style="display:flex; align-items:center; gap:.6rem; width:100%; border:none; background:transparent; cursor:pointer; text-align:left; padding:.4rem .5rem; border-radius:.5rem; transition:background .15s;"
                onmouseover="this.style.background='#e2e8f0'"
                onmouseout="this.style.background='transparent'"
            >
                <span style="width:.55rem; height:.55rem; border-radius:9999px; background:{{ $demo['color'] }}; flex-shrink:0;"></span>
                <span style="font-size:.78rem; font-weight:600; color:#1e293b; min-width:6.5rem;">{{ $demo['role'] }}</span>
                <span style="font-size:.72rem; color:#64748b; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                    {{ $demo['email'] }} · {{ $demo['password'] }}
                </span>
            </button>
        @endforeach
        <p style="margin:.25rem .5rem 0; font-size:.68rem; color:#94a3b8;">Khusus pengembangan — segera ubah sebelum production.</p>
    </div>
</div>
