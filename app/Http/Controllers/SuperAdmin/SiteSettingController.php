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
            'brand_description' => SiteSetting::get('brand_description', 'Toko laptop terpercaya dengan koleksi lengkap dan harga terbaik untuk semua kebutuhan Anda. Memberikan solusi teknologi berkualitas sejak 2020.'),
            'hero_title' => SiteSetting::get('hero_title', "Temukan Laptop\nImpian Anda"),
            'hero_subtitle' => SiteSetting::get('hero_subtitle', 'Koleksi laptop terlengkap dengan spesifikasi terbaik untuk kebutuhan kerja, gaming, dan entertainment.'),
            'carousel_images' => SiteSetting::get('carousel_images', []),
            'social_links' => SiteSetting::get('social_links', []),
            'footer_address' => SiteSetting::get('footer_address', 'Pekanbaru, Riau, Indonesia'),
            'footer_phone' => SiteSetting::get('footer_phone', '+62 823-1659-2733'),
            'footer_email' => SiteSetting::get('footer_email', 'laptopPremium@gmail.com'),
            'footer_copyright_text' => SiteSetting::get('footer_copyright_text', '© 2025 LaptopPremium. All rights reserved.'),
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
            'brand_description' => 'nullable|string|max:500',
            'hero_title' => 'nullable|string|max:200',
            'hero_subtitle' => 'nullable|string|max:500',
            'carousel_images.*' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
            'social_links' => 'nullable|array',
            'social_links.*.platform' => 'required|string',
            'social_links.*.url' => 'required|url',
            'footer_address' => 'nullable|string|max:255',
            'footer_phone' => 'nullable|string|max:50',
            'footer_email' => 'nullable|email|max:100',
            'footer_copyright_text' => 'nullable|string|max:255',
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

            if ($request->filled('brand_description')) {
                SiteSetting::set('brand_description', $request->brand_description, 'text', 'appearance');
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

            // Update Social Links
            if ($request->has('social_links')) {
                // Filter out empty entries
                $socialLinks = array_filter($request->social_links, function($link) {
                    return !empty($link['platform']) && !empty($link['url']);
                });
                SiteSetting::set('social_links', array_values($socialLinks), 'json', 'social');
            }

            // Update Footer Contact Info
            if ($request->filled('footer_address')) {
                SiteSetting::set('footer_address', $request->footer_address, 'string', 'contact');
            }
            if ($request->filled('footer_phone')) {
                SiteSetting::set('footer_phone', $request->footer_phone, 'string', 'contact');
            }
            if ($request->filled('footer_email')) {
                SiteSetting::set('footer_email', $request->footer_email, 'string', 'contact');
            }
            if ($request->filled('footer_copyright_text')) {
                SiteSetting::set('footer_copyright_text', $request->footer_copyright_text, 'string', 'appearance');
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
