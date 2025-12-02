<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserDashboardController extends Controller
{
    /**
     * Display user dashboard
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get stats
        $bookmarksCount = $user->bookmarks()->count();
        $ratingsCount = $user->ratings()->count();
        $purchasesCount = Penjualan::where('id_user', $user->id)->count();
        
        // Get recent purchases (limit 5)
        $recentPurchases = Penjualan::where('id_user', $user->id)
            ->with('detailPenjualans.produk')
            ->latest()
            ->take(5)
            ->get();
        
        // Get bookmarked products
        $bookmarkedProducts = $user->bookmarks()
            ->with('produk.kategori')
            ->latest()
            ->take(4)
            ->get()
            ->pluck('produk');
        
        return view('user.dashboard', compact(
            'user',
            'bookmarksCount',
            'ratingsCount',
            'purchasesCount',
            'recentPurchases',
            'bookmarkedProducts'
        ));
    }
    
    /**
     * Display user's purchase history
     */
    public function purchaseHistory()
    {
        $purchases = Penjualan::where('id_user', Auth::id())
            ->with('detailPenjualans.produk.kategori')
            ->latest()
            ->paginate(10);
        
        return view('user.purchases', compact('purchases'));
    }
    
    /**
     * Display user's ratings
     */
    public function myRatings()
    {
        $ratings = Auth::user()->ratings()
            ->with('produk.kategori')
            ->latest()
            ->paginate(10);
        
        return view('user.ratings', compact('ratings'));
    }
}
