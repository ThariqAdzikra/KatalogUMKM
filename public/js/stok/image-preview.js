// public/js/stok/image-preview.js
function previewImage(event) {
    const reader = new FileReader();
    const imagePreview = document.getElementById('imagePreview');
    
    reader.onload = function() {
        if (imagePreview) {
             // Untuk create.blade.php
            imagePreview.innerHTML = `<img src="${reader.result}" alt="Preview">`;
            
            // Untuk edit.blade.php
            imagePreview.style.display = 'flex';
        }
    }
    
    if (event.target.files[0]) {
        reader.readAsDataURL(event.target.files[0]);
    } else if (imagePreview) {
        // Hanya relevan untuk edit.blade.php agar preview hilang jika file dibatalkan
        imagePreview.style.display = 'none';
        imagePreview.innerHTML = `
            <div class="image-placeholder">
                <i class="bi bi-image"></i>
                <p>Preview gambar baru</p>
            </div>`;
    }
}