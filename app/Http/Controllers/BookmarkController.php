<?php

namespace App\Http\Controllers;

use App\Models\ProductBookmark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookmarkController extends Controller
{
    /**
     * Display user's bookmarks
     */
    public function index()
    {
        $bookmarks = Auth::user()->bookmarks()->with('produk.kategori')->latest()->get();
        
        return view('user.bookmarks', compact('bookmarks'));
    }
    
    /**
     * Toggle bookmark (add or remove)
     */
    public function toggle($produkId)
    {
        $user = Auth::user();
        
        $bookmark = ProductBookmark::where('user_id', $user->id)
            ->where('id_produk', $produkId)
            ->first();
        
        if ($bookmark) {
            // Remove bookmark
            $bookmark->delete();
            $message = 'Bookmark dihapus!';
        } else {
            // Add bookmark  
            ProductBookmark::create([
                'user_id' => $user->id,
                'id_produk' => $produkId
            ]);
            $message = 'Produk ditambahkan ke bookmark!';
        }
        
        // Return JSON for AJAX requests
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => $message]);
        }
        
        return back()->with('success', $message);
    }
}
