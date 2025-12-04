@extends('layouts.app')

@section('title', 'Pengaturan Website')

@push('styles')
<style>
    .settings-container {
        padding: 2rem;
        max-width: 1200px;
        margin: 0 auto;
    }

    .settings-header {
        margin-bottom: 2rem;
    }

    .settings-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: #fff;
        margin-bottom: 0.5rem;
    }

    .settings-header p {
        color: rgba(255, 255, 255, 0.7);
        font-size: 1rem;
    }

    .settings-card {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
        transition: all 0.3s ease;
    }

    .settings-card:hover {
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(59, 130, 246, 0.3);
    }

    .settings-card h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #fff;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .settings-card h3 i {
        color: #3b82f6;
        font-size: 1.5rem;
    }

    .form-label {
        color: rgba(255, 255, 255, 0.9);
        font-weight: 500;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }

    .form-control, .form-select {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #fff;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        background: rgba(255, 255, 255, 0.08);
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        color: #fff;
    }

    .form-control::placeholder {
        color: rgba(255, 255, 255, 0.4);
    }

    .preview-logo {
        max-width: 150px;
        max-height: 150px;
        border-radius: 8px;
        border: 2px solid rgba(59, 130, 246, 0.3);
        padding: 1rem;
        background: rgba(255, 255, 255, 0.05);
    }

    .carousel-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-top: 1rem;
    }

    .carousel-item-card {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        overflow: hidden;
        position: relative;
        transition: all 0.3s ease;
    }

    .carousel-item-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
    }

    .carousel-item-card img {
        width: 100%;
        height: 180px;
        object-fit: cover;
    }

    .btn-delete-carousel {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(220, 38, 38, 0.9);
        border: none;
        color: white;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-delete-carousel:hover {
        background: rgba(220, 38, 38, 1);
        transform: scale(1.1);
    }

    .btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border: none;
        color: white;
        padding: 0.75rem 2rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(59, 130, 246, 0.4);
    }

    .alert {
        border-radius: 8px;
        border: none;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
    }

    .alert-success {
        background: rgba(34, 197, 94, 0.1);
        border-left: 4px solid #22c55e;
        color: #22c55e;
    }

    .alert-danger {
        background: rgba(220, 38, 38, 0.1);
        border-left: 4px solid #dc2626;
        color: #ef4444;
    }

    .char-counter {
        font-size: 0.875rem;
        color: rgba(255, 255, 255, 0.5);
        text-align: right;
        margin-top: 0.25rem;
    }

    .file-upload-wrapper {
        position: relative;
        display: inline-block;
        width: 100%;
    }

    .file-upload-wrapper input[type="file"] {
        position: absolute;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }

    .file-upload-label {
        background: rgba(59, 130, 246, 0.1);
        border: 2px dashed rgba(59, 130, 246, 0.3);
        border-radius: 8px;
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        display: block;
    }

    .file-upload-label:hover {
        background: rgba(59, 130, 246, 0.15);
        border-color: rgba(59, 130, 246, 0.5);
    }

    .file-upload-label i {
        font-size: 2.5rem;
        color: #3b82f6;
        margin-bottom: 1rem;
    }
</style>
@endpush

@section('content')
<div class="settings-container">
    <div class="settings-header">
        <h1><i class="bi bi-gear-fill"></i> Pengaturan Website</h1>
        <p>Sesuaikan tampilan website dengan jenis usaha Anda</p>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('superadmin.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Logo & Branding --}}
        <div class="settings-card">
            <h3><i class="bi bi-image"></i> Logo & Branding</h3>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Logo Saat Ini</label>
                    <div>
                        <img src="{{ asset($settings['logo_path']) }}" alt="Logo" class="preview-logo" id="logoPreview">
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Upload Logo Baru</label>
                    <div class="file-upload-wrapper">
                        <input type="file" name="logo" id="logoInput" accept="image/jpeg,image/jpg,image/png,image/svg+xml">
                        <label for="logoInput" class="file-upload-label">
                            <i class="bi bi-cloud-upload"></i>
                            <div class="text-white">Klik untuk upload logo baru</div>
                            <small class="text-muted">PNG, JPG, SVG (Max: 2MB)</small>
                        </label>
                    </div>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Nama Brand</label>
                    <input type="text" name="brand_name" class="form-control" 
                           value="{{ old('brand_name', $settings['brand_name']) }}" 
                           placeholder="Contoh: LaptopPremium">
                </div>
            </div>
        </div>

        {{-- Hero Section --}}
        <div class="settings-card">
            <h3><i class="bi bi-card-heading"></i> Hero Section</h3>
            
            <div class="mb-3">
                <label class="form-label">Judul Hero</label>
                <textarea name="hero_title" class="form-control" rows="2" 
                          maxlength="200" id="heroTitle"
                          placeholder="Temukan Laptop&#10;Impian Anda">{{ old('hero_title', $settings['hero_title']) }}</textarea>
                <div class="char-counter">
                    <span id="heroTitleCount">{{ strlen($settings['hero_title']) }}</span>/200 karakter
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Deskripsi Hero</label>
                <textarea name="hero_subtitle" class="form-control" rows="3" 
                          maxlength="500" id="heroSubtitle"
                          placeholder="Koleksi laptop terlengkap dengan spesifikasi terbaik...">{{ old('hero_subtitle', $settings['hero_subtitle']) }}</textarea>
                <div class="char-counter">
                    <span id="heroSubtitleCount">{{ strlen($settings['hero_subtitle']) }}</span>/500 karakter
                </div>
            </div>
        </div>

        {{-- Carousel Images --}}
        <div class="settings-card">
            <h3><i class="bi bi-images"></i> Carousel Images</h3>
            
            @if(!empty($settings['carousel_images']))
            <div class="mb-4">
                <label class="form-label">Gambar yang Ada ({{ count($settings['carousel_images']) }} gambar)</label>
                <div class="carousel-grid">
                    @foreach($settings['carousel_images'] as $index => $imagePath)
                    <div class="carousel-item-card" id="carouselItem{{ $index }}">
                        <img src="{{ asset($imagePath) }}" alt="Carousel {{ $index + 1 }}">
                        <button type="button" class="btn-delete-carousel" 
                                onclick="deleteCarouselImage({{ $index }})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="mb-3">
                <label class="form-label">Jumlah Gambar yang Ingin Diupload</label>
                <select class="form-select" id="carouselCount" style="max-width: 200px;">
                    <option value="1">1 Gambar</option>
                    <option value="2">2 Gambar</option>
                    <option value="3" selected>3 Gambar</option>
                    <option value="4">4 Gambar</option>
                    <option value="5">5 Gambar</option>
                    <option value="6">6 Gambar</option>
                    <option value="7">7 Gambar</option>
                    <option value="8">8 Gambar</option>
                </select>
            </div>

            <div id="carouselInputsContainer"></div>
        </div>

        {{-- Submit Button --}}
        <div class="text-end">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-2"></i>Simpan Perubahan
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Logo Preview
    document.getElementById('logoInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('logoPreview').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });

    // Character Counter
    function updateCharCount(inputId, countId) {
        const input = document.getElementById(inputId);
        const counter = document.getElementById(countId);
        
        input.addEventListener('input', function() {
            counter.textContent = this.value.length;
        });
    }

    updateCharCount('heroTitle', 'heroTitleCount');
    updateCharCount('heroSubtitle', 'heroSubtitleCount');

    // Carousel Upload with Count Selector
    const carouselCount = document.getElementById('carouselCount');
    const carouselContainer = document.getElementById('carouselInputsContainer');

    function generateCarouselInputs() {
        const count = parseInt(carouselCount.value);
        carouselContainer.innerHTML = '';

        for (let i = 0; i < count; i++) {
            const inputGroup = document.createElement('div');
            inputGroup.className = 'mb-3';
            inputGroup.innerHTML = `
                <label class="form-label">Gambar ${i + 1}</label>
                <input type="file" name="carousel_images[]" class="form-control" 
                       accept="image/jpeg,image/jpg,image/png" required>
            `;
            carouselContainer.appendChild(inputGroup);
        }
    }

    carouselCount.addEventListener('change', generateCarouselInputs);
    
    // Generate default inputs on page load
    generateCarouselInputs();

    // Delete Carousel Image
    function deleteCarouselImage(index) {
        if (!confirm('Yakin ingin menghapus gambar ini?')) {
            return;
        }

        fetch(`/superadmin/settings/carousel/${index}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('carouselItem' + index).remove();
                
                // Show success message
                const alert = document.createElement('div');
                alert.className = 'alert alert-success';
                alert.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>' + data.message;
                document.querySelector('.settings-container').insertBefore(alert, document.querySelector('.settings-header').nextSibling);
                
                setTimeout(() => alert.remove(), 3000);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus gambar');
        });
    }
</script>
@endpush
@endsection
