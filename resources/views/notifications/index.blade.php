@extends('layouts.app')

@section('title', 'Notifikasi - ' . config('app.name'))

@section('content')
<div class="notification-page-container">
    {{-- Page Header --}}
    <div class="notification-page-header">
        <h2><i class="bi bi-bell-fill"></i>Notifikasi</h2>
        <div class="notification-actions">
            <form action="{{ route('notifications.read-all') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-check2-all"></i> Tandai Semua Dibaca
                </button>
            </form>
            <form id="bulkDeleteForm" action="{{ route('notifications.bulk-delete') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" id="bulkDeleteBtn" class="btn btn-outline-danger" disabled>
                    <i class="bi bi-trash3"></i> Hapus Terpilih <span class="count"></span>
                </button>
            </form>
        </div>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" 
             style="background: rgba(40, 167, 69, 0.15); border: 1px solid rgba(40, 167, 69, 0.3); color: #28a745; border-radius: 10px;">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" style="filter: invert(50%) sepia(95%) saturate(400%) hue-rotate(80deg);"></button>
        </div>
    @endif

    {{-- Select All Checkbox --}}
    @if($notifications->count() > 0)
        @php
            $readCount = $notifications->where('is_read', true)->count();
        @endphp
        <div class="select-all-row">
            <input type="checkbox" id="selectAllNotifications" class="form-check-input" {{ $readCount == 0 ? 'disabled' : '' }}>
            <label for="selectAllNotifications">Pilih Semua yang Sudah Dibaca</label>
            <span class="text-muted small ms-auto">
                <i class="bi bi-info-circle me-1"></i>{{ $readCount }} dari {{ $notifications->total() }} bisa dihapus
            </span>
        </div>
    @endif

    {{-- Notification List --}}
    @forelse($notifications as $notif)
        <div class="notification-card {{ $notif->is_read ? 'read' : 'unread' }}" 
             onclick="showNotificationDetail({{ $notif->id }})" 
             style="cursor: pointer;">
            <div class="notification-card-body">
                {{-- Checkbox - Only for READ notifications --}}
                <div class="notification-card-checkbox" onclick="event.stopPropagation();">
                    @if($notif->is_read)
                        <input type="checkbox" 
                               class="notification-checkbox form-check-input" 
                               value="{{ $notif->id }}"
                               title="Pilih untuk hapus">
                    @else
                        <div class="checkbox-placeholder" title="Baca dulu sebelum bisa dihapus">
                            <i class="bi bi-lock-fill" style="color: rgba(156, 163, 175, 0.5); font-size: 0.85rem;"></i>
                        </div>
                    @endif
                </div>

                {{-- Icon --}}
                <div class="notification-card-icon" style="color: {{ $notif->color }};">
                    <i class="bi {{ $notif->icon }}"></i>
                </div>

                {{-- Content - Full Width --}}
                <div class="notification-card-content flex-grow-1">
                    <div class="notification-card-title">{{ $notif->title }}</div>
                    <div class="notification-card-message">{{ $notif->message }}</div>
                    <div class="notification-card-meta">
                        <span><i class="bi bi-clock me-1"></i>{{ $notif->time_ago }}</span>
                        @php
                            $typeBadge = match($notif->type) {
                                'penjualan' => 'bg-success',
                                'pembelian' => 'bg-primary',
                                default => 'bg-info',
                            };
                        @endphp
                        <span class="badge {{ $typeBadge }}">{{ ucfirst(str_replace('_', ' ', $notif->type)) }}</span>
                        @if(!$notif->is_read)
                            <span class="badge bg-warning">Baru</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Hidden data for modal --}}
        <script type="application/json" id="notif-data-{{ $notif->id }}">
            {!! json_encode([
                'id' => $notif->id,
                'type' => $notif->type,
                'title' => $notif->title,
                'message' => $notif->message,
                'data' => $notif->data,
                'time_ago' => $notif->time_ago,
                'created_at' => $notif->created_at->format('d M Y, H:i'),
                'icon' => $notif->icon,
                'color' => $notif->color,
            ]) !!}
        </script>
    @empty
        <div class="notification-page-empty">
            <i class="bi bi-bell-slash"></i>
            <h4>Tidak Ada Notifikasi</h4>
            <p>Anda tidak memiliki notifikasi saat ini.</p>
        </div>
    @endforelse

    {{-- Pagination --}}
    @if($notifications->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $notifications->links() }}
        </div>
    @endif
</div>

{{-- Notification Detail Modal --}}
<div class="modal fade" id="notificationDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 800px;">
        <div class="modal-content" style="background: rgba(30, 33, 57, 0.98); border: 1px solid rgba(6, 182, 212, 0.3); border-radius: 16px;">
            <div class="modal-header" style="border-bottom: 1px solid rgba(6, 182, 212, 0.2); padding: 1.5rem;">
                <div class="d-flex align-items-center gap-3">
                    <div class="modal-icon" id="modalIcon" style="width: 50px; height: 50px; border-radius: 12px; background: rgba(6, 182, 212, 0.1); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                        <i class="bi bi-bell"></i>
                    </div>
                    <div>
                        <h5 class="modal-title" id="modalTitle" style="color: #ffffff; font-weight: 700; margin: 0;"></h5>
                        <small id="modalTime" style="color: #9ca3af;"></small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1);"></button>
            </div>
            <div class="modal-body" style="padding: 1.5rem; max-height: 60vh; overflow-y: auto;">
                {{-- Transaction Info --}}
                <div id="transactionInfo" class="mb-4">
                    {{-- Filled dynamically --}}
                </div>
                
                {{-- Product Table --}}
                <div id="productTableContainer" style="display: none;">
                    <h6 style="color: #17a2b8; margin-bottom: 1rem; font-weight: 600;">
                        <i class="bi bi-box me-2"></i>Daftar Produk
                    </h6>
                    <div class="table-responsive">
                        <table class="table" style="color: #e5e7eb;">
                            <thead style="background: rgba(6, 182, 212, 0.1);">
                                <tr>
                                    <th style="color: #17a2b8; border: none; padding: 0.75rem;">Produk</th>
                                    <th style="color: #17a2b8; border: none; padding: 0.75rem; text-align: center;">Jumlah</th>
                                    <th style="color: #17a2b8; border: none; padding: 0.75rem; text-align: right;">Harga</th>
                                    <th style="color: #17a2b8; border: none; padding: 0.75rem; text-align: right;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="productTableBody">
                                {{-- Filled dynamically --}}
                            </tbody>
                            <tfoot style="background: rgba(6, 182, 212, 0.05);">
                                <tr>
                                    <td colspan="3" style="border: none; padding: 0.75rem; font-weight: 700; text-align: right; color: #ffffff;">Total:</td>
                                    <td id="totalHarga" style="border: none; padding: 0.75rem; font-weight: 700; text-align: right; color: #17a2b8;"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid rgba(6, 182, 212, 0.2); padding: 1rem 1.5rem;">
                <button type="button" class="btn" data-bs-dismiss="modal" 
                        style="background: rgba(6, 182, 212, 0.1); border: 1px solid rgba(6, 182, 212, 0.3); color: #17a2b8; padding: 0.5rem 1.5rem; border-radius: 8px;">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showNotificationDetail(id) {
    const dataElement = document.getElementById('notif-data-' + id);
    if (!dataElement) return;
    
    const notif = JSON.parse(dataElement.textContent);
    const modal = new bootstrap.Modal(document.getElementById('notificationDetailModal'));
    
    // Set modal header
    document.getElementById('modalTitle').textContent = notif.title;
    document.getElementById('modalTime').textContent = notif.created_at + ' (' + notif.time_ago + ')';
    document.getElementById('modalIcon').innerHTML = '<i class="bi ' + notif.icon + '" style="color: ' + notif.color + ';"></i>';
    
    // Build transaction info
    let infoHtml = '';
    const data = notif.data || {};
    
    if (notif.type === 'penjualan') {
        infoHtml = `
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="info-card" style="background: rgba(6, 182, 212, 0.15); border: 1px solid rgba(6, 182, 212, 0.4); border-radius: 12px; padding: 1rem;">
                        <div style="color: #06b6d4; font-size: 0.85rem; margin-bottom: 0.25rem; font-weight: 500;">ID Transaksi</div>
                        <div style="color: #ffffff; font-weight: 600; font-size: 1.1rem;">#${data.id_transaksi || '-'}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card" style="background: rgba(6, 182, 212, 0.15); border: 1px solid rgba(6, 182, 212, 0.4); border-radius: 12px; padding: 1rem;">
                        <div style="color: #06b6d4; font-size: 0.85rem; margin-bottom: 0.25rem; font-weight: 500;">Tanggal</div>
                        <div style="color: #ffffff; font-weight: 600; font-size: 1.1rem;">${data.tanggal || '-'}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card" style="background: rgba(6, 182, 212, 0.15); border: 1px solid rgba(6, 182, 212, 0.4); border-radius: 12px; padding: 1rem;">
                        <div style="color: #06b6d4; font-size: 0.85rem; margin-bottom: 0.25rem; font-weight: 500;"><i class="bi bi-person me-1"></i>Pelanggan</div>
                        <div style="color: #ffffff; font-weight: 600; font-size: 1.1rem;">${data.pelanggan || 'Umum'}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card" style="background: rgba(6, 182, 212, 0.15); border: 1px solid rgba(6, 182, 212, 0.4); border-radius: 12px; padding: 1rem;">
                        <div style="color: #06b6d4; font-size: 0.85rem; margin-bottom: 0.25rem; font-weight: 500;"><i class="bi bi-person-badge me-1"></i>Kasir</div>
                        <div style="color: #ffffff; font-weight: 600; font-size: 1.1rem;">${data.kasir || '-'}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card" style="background: rgba(6, 182, 212, 0.15); border: 1px solid rgba(6, 182, 212, 0.4); border-radius: 12px; padding: 1rem;">
                        <div style="color: #06b6d4; font-size: 0.85rem; margin-bottom: 0.25rem; font-weight: 500;"><i class="bi bi-wallet2 me-1"></i>Metode Pembayaran</div>
                        <div style="color: #ffffff; font-weight: 600; font-size: 1.1rem;">${data.metode_pembayaran || 'CASH'}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card" style="background: rgba(6, 182, 212, 0.15); border: 1px solid rgba(6, 182, 212, 0.4); border-radius: 12px; padding: 1rem;">
                        <div style="color: #06b6d4; font-size: 0.85rem; margin-bottom: 0.25rem; font-weight: 500;"><i class="bi bi-cash-stack me-1"></i>Total Harga</div>
                        <div style="color: #06b6d4; font-weight: 700; font-size: 1.25rem;">Rp ${Number(data.total_harga || 0).toLocaleString('id-ID')}</div>
                    </div>
                </div>
            </div>
        `;
    } else if (notif.type === 'pembelian') {
        infoHtml = `
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="info-card" style="background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.4); border-radius: 12px; padding: 1rem;">
                        <div style="color: #3b82f6; font-size: 0.85rem; margin-bottom: 0.25rem; font-weight: 500;">ID Pembelian</div>
                        <div style="color: #ffffff; font-weight: 600; font-size: 1.1rem;">#${data.id_transaksi || '-'}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card" style="background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.4); border-radius: 12px; padding: 1rem;">
                        <div style="color: #3b82f6; font-size: 0.85rem; margin-bottom: 0.25rem; font-weight: 500;">Tanggal</div>
                        <div style="color: #ffffff; font-weight: 600; font-size: 1.1rem;">${data.tanggal || '-'}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card" style="background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.4); border-radius: 12px; padding: 1rem;">
                        <div style="color: #3b82f6; font-size: 0.85rem; margin-bottom: 0.25rem; font-weight: 500;"><i class="bi bi-truck me-1"></i>Supplier</div>
                        <div style="color: #ffffff; font-weight: 600; font-size: 1.1rem;">${data.supplier || '-'}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card" style="background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.4); border-radius: 12px; padding: 1rem;">
                        <div style="color: #3b82f6; font-size: 0.85rem; margin-bottom: 0.25rem; font-weight: 500;"><i class="bi bi-person-badge me-1"></i>Input Oleh</div>
                        <div style="color: #ffffff; font-weight: 600; font-size: 1.1rem;">${data.user || '-'}</div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="info-card" style="background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.4); border-radius: 12px; padding: 1rem;">
                        <div style="color: #3b82f6; font-size: 0.85rem; margin-bottom: 0.25rem; font-weight: 500;"><i class="bi bi-cash-stack me-1"></i>Total Harga</div>
                        <div style="color: #3b82f6; font-weight: 700; font-size: 1.25rem;">Rp ${Number(data.total_harga || 0).toLocaleString('id-ID')}</div>
                    </div>
                </div>
            </div>
        `;
    } else {
        infoHtml = `
            <div class="info-card" style="background: rgba(6, 182, 212, 0.1); border: 1px solid rgba(6, 182, 212, 0.2); border-radius: 12px; padding: 1rem;">
                <div style="color: #ffffff;">${notif.message}</div>
            </div>
        `;
    }
    
    document.getElementById('transactionInfo').innerHTML = infoHtml;
    
    // Build product table
    const productContainer = document.getElementById('productTableContainer');
    const productBody = document.getElementById('productTableBody');
    
    if (data.produk && data.produk.length > 0) {
        productBody.innerHTML = '';
        data.produk.forEach(function(p) {
            const row = document.createElement('tr');
            row.style.borderBottom = '1px solid rgba(6, 182, 212, 0.1)';
            row.innerHTML = `
                <td style="border: none; padding: 0.75rem;">${p.nama}</td>
                <td style="border: none; padding: 0.75rem; text-align: center;">${p.jumlah}</td>
                <td style="border: none; padding: 0.75rem; text-align: right;">Rp ${Number(p.harga_satuan).toLocaleString('id-ID')}</td>
                <td style="border: none; padding: 0.75rem; text-align: right;">Rp ${Number(p.subtotal).toLocaleString('id-ID')}</td>
            `;
            productBody.appendChild(row);
        });
        
        document.getElementById('totalHarga').textContent = 'Rp ' + Number(data.total_harga).toLocaleString('id-ID');
        productContainer.style.display = 'block';
    } else {
        productContainer.style.display = 'none';
    }
    
    modal.show();
    
    // Mark as read via AJAX and update visual
    const notifCard = document.querySelector(`.notification-card[onclick*="showNotificationDetail(${id})"]`);
    
    fetch('/notifications/' + id + '/read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        }
    }).then(response => {
        if (response.ok && notifCard) {
            // Update visual from unread to read
            notifCard.classList.remove('unread');
            notifCard.classList.add('read');
            
            // Remove "Baru" badge if exists
            const baruBadge = notifCard.querySelector('.badge.bg-warning');
            if (baruBadge) baruBadge.remove();
            
            // Replace lock icon with checkbox
            const checkboxArea = notifCard.querySelector('.notification-card-checkbox');
            const lockPlaceholder = checkboxArea.querySelector('.checkbox-placeholder');
            if (lockPlaceholder) {
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.className = 'notification-checkbox form-check-input';
                checkbox.value = id;
                checkbox.title = 'Pilih untuk hapus';
                lockPlaceholder.replaceWith(checkbox);
            }
        }
    });
}
</script>
@endpush
@endsection
