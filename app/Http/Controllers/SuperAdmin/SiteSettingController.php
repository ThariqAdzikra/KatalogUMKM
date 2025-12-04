<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SiteSettingController extends Controller
{
    /**
     * Display the site settings page
     */
    public function index()
    {
        $settings = [
            'logo_path' => SiteSetting::get('logo_path', 'images/logo.png'),
            'brand_name' => SiteSetting::get('brand_name', 'LaptopPremium'),
            'hero_title' => SiteSetting::get('hero_title', "Temukan Laptop\nImpian Anda"),
            'hero_subtitle' => SiteSetting::get('hero_subtitle', 'Koleksi laptop terlengkap dengan spesifikasi terbaik untuk kebutuhan kerja, gaming, dan entertainment.'),
            'carousel_images' => SiteSetting::get('carousel_images', []),
        ];

        return view('superadmin.settings', compact('settings'));
    }

    /**
     * Update site settings
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'logo' => 'nullable|image|mimes:jpeg,jpg,png,svg|max:2048',
            'brand_name' => 'nullable|string|max:100',
            'hero_title' => 'nullable|string|max:200',
            'hero_subtitle' => 'nullable|string|max:500',
            'carousel_images.*' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Update Logo
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('settings', 'public');
                SiteSetting::set('logo_path', 'storage/' . $logoPath, 'image', 'appearance');
            }

            // Update Brand Name
            if ($request->filled('brand_name')) {
                SiteSetting::set('brand_name', $request->brand_name, 'string', 'appearance');
            }

            // Update Hero Section
            if ($request->filled('hero_title')) {
                SiteSetting::set('hero_title', $request->hero_title, 'text', 'hero');
            }

            if ($request->filled('hero_subtitle')) {
                SiteSetting::set('hero_subtitle', $request->hero_subtitle, 'text', 'hero');
            }

            // Update Carousel Images
            if ($request->hasFile('carousel_images')) {
                $carouselData = SiteSetting::get('carousel_images', []);
                
                foreach ($request->file('carousel_images') as $file) {
                    $path = $file->store('settings/carousel', 'public');
                    $carouselData[] = 'storage/' . $path;
                }

                SiteSetting::set('carousel_images', $carouselData, 'json', 'carousel');
            }

            return redirect()->back()->with('success', 'Pengaturan website berhasil diperbarui!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete a carousel image
     */
    public function deleteCarouselImage($index)
    {
        try {
            $carouselData = SiteSetting::get('carousel_images', []);
            
            if (isset($carouselData[$index])) {
                // Delete file from storage if exists
                $imagePath = str_replace('storage/', '', $carouselData[$index]);
                if (Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                }

                // Remove from array
                unset($carouselData[$index]);
                
                // Reindex array
                $carouselData = array_values($carouselData);
                
                // Update setting
                SiteSetting::set('carousel_images', $carouselData, 'json', 'carousel');

                return response()->json([
                    'success' => true,
                    'message' => 'Gambar carousel berhasil dihapus!'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gambar tidak ditemukan!'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
