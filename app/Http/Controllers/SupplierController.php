<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier; 
use Illuminate\Support\Facades\Validator; 

class SupplierController extends Controller
{
    public function search(Request $request)
    {
        $data = [];
        $term = $request->term; 
        
        $suppliers = Supplier::where('nama_supplier', 'like', '%' . $term . '%')
                            ->take(10)
                            ->get();

        foreach ($suppliers as $s) {
            $data[] = [
                'label' => $s->nama_supplier,
                'value' => $s->nama_supplier, 
                'kontak' => $s->kontak,
                'alamat' => $s->alamat,
            ];
        }
        return response()->json($data);
    }

    public function storeAjax(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_supplier' => 'required|string|max:255|unique:supplier,nama_supplier',
            'kontak' => 'required|string|max:20', 
            'alamat' => 'nullable|string',
        ], [
            'nama_supplier.required' => 'Nama supplier tidak boleh kosong.',
            'nama_supplier.unique' => 'Nama supplier ini sudah terdaftar.',
            'kontak.required' => 'Nomor HP tidak boleh kosong.', 
        ]);

        // Jika validasi gagal, kirim error 422
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $supplier = Supplier::create($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Supplier berhasil ditambahkan!',
                'supplier' => $supplier
            ]);
        
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Gagal menyimpan ke database: ' . $e->getMessage()
            ], 500);
        }
    }
}