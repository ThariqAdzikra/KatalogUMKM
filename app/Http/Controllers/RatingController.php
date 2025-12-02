<?php

namespace App\Http\Controllers;

use App\Models\ProductRating;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    /**
     * Store a new rating
     */
    public function store(Request $request, $produkId)
    {
        $user = Auth::user();
        
        // Check if user has purchased this product
        if (!$user->hasPurchased($produkId)) {
            return back()->with('error', 'Anda hanya bisa memberikan rating pada produk yang sudah dibeli.');
        }
        
        // Validate
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000'
        ]);
        
        // Create or update rating
        ProductRating::updateOrCreate(
            ['user_id' => $user->id, 'id_produk' => $produkId],
            $validated
        );
        
        return back()->with('success', 'Rating berhasil disimpan!');
    }
    
    /**
     * Update existing rating
     */
    public function update(Request $request, $ratingId)
    {
        $rating = ProductRating::findOrFail($ratingId);
        
        // Check ownership
        if ($rating->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }
        
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000'
        ]);
        
        $rating->update($validated);
        
        return back()->with('success', 'Rating berhasil diupdate!');
    }
    
    /**
     * Delete rating
     */
    public function destroy($ratingId)
    {
        $rating = ProductRating::findOrFail($ratingId);
        
        // Check ownership
        if ($rating->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }
        
        $rating->delete();
        
        return back()->with('success', 'Rating berhasil dihapus!');
    }
}
