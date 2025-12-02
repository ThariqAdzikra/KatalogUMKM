<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Notifications\SetelPasswordPegawai; 
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;


class PegawaiController extends Controller
{
    public function index(Request $request)
    {
        // Tampilkan admin dan pegawai, agar sesuai dengan form
        $query = User::whereIn('role', ['pegawai', 'admin']);

        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $pegawais = $query->orderBy('name')->paginate(10);
        return view('pegawai.index', compact('pegawais'));
    }

    public function create()
    {
        return view('pegawai.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|string|in:admin,pegawai',
            'password' => 'required|min:6',
        ]);

        // Buat akun pegawai
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => $validated['role'], 
            'email_verified_at' => now(),
        ]);

        // Kirim email reset password 
        try {
            $token = app('auth.password.broker')->createToken($user);
            $user->notify(new SetelPasswordPegawai($token)); 

        } catch (\Throwable $e) {
            Log::error('Gagal kirim email setel password: ' . $e->getMessage());
            return redirect()->route('pegawai.index')->with('warning', 'Pegawai ditambahkan, tapi email tidak terkirim.');
        }

        // Feedback sukses
        return redirect()->route('pegawai.index')->with('success', 'Pegawai berhasil ditambahkan dan link setel password dikirim ke email.');
    }

    public function edit(User $pegawai)
    {
        return view('pegawai.edit', compact('pegawai'));
    }

    public function update(Request $request, User $pegawai)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$pegawai->id,
            'role' => 'required|string|in:admin,pegawai', 
            'password' => 'nullable|min:6',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']); 
        }

        $pegawai->update($validated); 

        return redirect()->route('pegawai.index')->with('success', 'Pegawai diperbarui.');
    }

    public function destroy(User $pegawai)
    {
        if ($pegawai->id === auth()->id()) {
            return redirect()->route('pegawai.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }
        
        try {
            $pegawai->delete();
            return redirect()->route('pegawai.index')->with('success', 'Pegawai dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Check if it's a foreign key constraint error
            if ($e->getCode() === '23000') {
                return redirect()->route('pegawai.index')->with('error', 
                    'Tidak dapat menghapus pegawai ini karena masih memiliki data transaksi penjualan. Hapus atau transfer data penjualan terlebih dahulu.');
            }
            
            // Re-throw if it's a different error
            throw $e;
        }
    }

    public function searchAjax(Request $request)
    {
        $query = $request->query('query', '');
        $pegawais = \App\Models\User::query()
            ->whereIn('role', ['admin', 'pegawai']) 
            ->when($query, function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('name', 'like', "%$query%")
                        ->orWhere('email', 'like', "%$query%");
                });
            })
            ->orderBy('name')
            ->take(20)
            ->get(['id', 'name', 'email', 'role', 'created_at']);

        return response()->json($pegawais);
    }
}