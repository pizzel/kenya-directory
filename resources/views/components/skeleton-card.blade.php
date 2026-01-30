{{-- resources/views/components/skeleton-card.blade.php --}}
<div class="skeleton-card" style="background: #fff; border-radius: 12px; overflow: hidden; height: 100%; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
    <div class="skeleton-loader" style="width: 100%; height: 200px;"></div>
    <div style="padding: 15px;">
        <div class="skeleton-loader" style="width: 70%; height: 20px; margin-bottom: 10px; border-radius: 4px;"></div>
        <div class="skeleton-loader" style="width: 40%; height: 16px; margin-bottom: 15px; border-radius: 4px;"></div>
        <div style="display: flex; gap: 10px;">
            <div class="skeleton-loader" style="width: 60px; height: 24px; border-radius: 12px;"></div>
            <div class="skeleton-loader" style="width: 60px; height: 24px; border-radius: 12px;"></div>
        </div>
    </div>
</div>