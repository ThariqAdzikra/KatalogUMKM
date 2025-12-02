@component('mail::message')
# Selamat Bergabung, {{ $pegawai->name }}!

Anda telah berhasil didaftarkan ke sistem manajemen Laptop Store sebagai **{{ ucfirst($pegawai->role) }}**.

Berikut adalah detail akun Anda yang dapat digunakan untuk login:

@component('mail::panel')
**Email:** {{ $pegawai->email }} <br>
**Password:** {{ $rawPassword }}
@endcomponent

Harap segera ganti password Anda setelah login pertama kali untuk menjaga keamanan akun.

@component('mail::button', ['url' => url('/login')])
Login ke Sistem
@endcomponent

Terima kasih,<br>
Tim {{ config('app.name') }}
@endcomponent