@extends('layouts.site')

@section('title', 'Edit Journey - ' . $itinerary->title)

@section('styles')
<style>
    .itinerary-create-view {
        padding: 60px 20px;
        background-color: #f8fafc;
        min-height: calc(100vh - 100px);
    }

    .itinerary-main-container {
        max-width: 600px;
        margin: 0 auto;
        padding: 50px;
        background: white;
        border-radius: 40px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.05);
        position: relative;
        z-index: 10;
        border: 1px solid #f1f5f9;
    }

    .itinerary-form-header {
        text-align: center;
        margin-bottom: 50px;
        position: relative;
    }

    .itinerary-icon-box {
        width: 64px;
        height: 64px;
        background: #eff6ff;
        color: #2563eb;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 24px;
    }

    .itinerary-main-title {
        font-size: 2.8rem;
        font-weight: 900;
        letter-spacing: -1.5px;
        color: #0f172a;
        margin: 0 0 10px 0;
        line-height: 1.1;
    }

    .itinerary-subtitle {
        font-size: 1rem;
        color: #64748b;
        margin: 0;
    }

    .form-group {
        margin-bottom: 30px;
    }

    .form-label {
        display: block;
        font-size: 0.85rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 10px;
        color: #64748b;
    }

    .form-input {
        width: 100%;
        padding: 18px 24px;
        border-radius: 20px;
        border: 2px solid #f1f5f9;
        background: #f8fafc;
        font-size: 1rem;
        color: #1e293b;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }

    .form-input:focus {
        outline: none;
        border-color: #2563eb;
        background: white;
        box-shadow: 0 0 0 5px rgba(37, 99, 235, 0.1);
    }

    textarea.form-input {
        min-height: 120px;
        resize: vertical;
    }

    .btn-submit {
        width: 100%;
        padding: 20px;
        background: #0f172a;
        color: white;
        border-radius: 20px;
        font-weight: 800;
        font-size: 1.1rem;
        margin-top: 10px;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .btn-submit:hover {
        background: #1e293b;
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    }

    .color-picker-grid {
        display: flex;
        gap: 15px;
        margin-top: 15px;
    }

    .color-option {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        cursor: pointer;
        border: 4px solid white;
        box-shadow: 0 0 0 1px #e2e8f0;
        transition: all 0.2s ease;
    }

    .color-option:hover { transform: scale(1.1); }
    .color-option.selected {
        transform: scale(1.2);
        box-shadow: 0 0 0 2px #2563eb;
    }
</style>
@endsection

@section('content')
<div class="itinerary-create-view">
    <div class="itinerary-main-container">
        <div class="itinerary-form-header">
            <div class="itinerary-icon-box">
                <i class="fas fa-edit"></i>
            </div>
            <h1 class="itinerary-main-title">Edit Your Journey</h1>
            <p class="itinerary-subtitle">Refine the details of your adventure.</p>
        </div>

        <form action="{{ route('itineraries.update', $itinerary->id) }}" method="POST" class="itinerary-creation-form">
            @csrf
            @method('PATCH')
            
            <div class="form-group">
                <label class="form-label">Journey Title</label>
                <input type="text" name="title" class="form-input" value="{{ old('title', $itinerary->title) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Description (Optional)</label>
                <textarea name="description" class="form-input" rows="4">{{ old('description', $itinerary->description) }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Theme Color</label>
                <div class="color-picker-grid">
                    @php $colors = ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899']; @endphp
                    @foreach($colors as $color)
                        <div class="color-option {{ old('theme_color', $itinerary->theme_color) == $color ? 'selected' : '' }}" 
                             style="background: {{ $color }}"
                             onclick="selectColor('{{ $color }}', this)"></div>
                    @endforeach
                    <input type="hidden" name="theme_color" id="theme_color_input" value="{{ old('theme_color', $itinerary->theme_color) }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Who can see this?</label>
                <select name="visibility" class="form-input">
                    <option value="public" {{ old('visibility', $itinerary->visibility) == 'public' ? 'selected' : '' }}>Public (Everyone)</option>
                    <option value="unlisted" {{ old('visibility', $itinerary->visibility) == 'unlisted' ? 'selected' : '' }}>Unlisted (Only people with link)</option>
                    <option value="private" {{ old('visibility', $itinerary->visibility) == 'private' ? 'selected' : '' }}>Private (Only me)</option>
                </select>
            </div>

            <button type="submit" class="btn-submit">
                Update Journey <i class="fas fa-check ml-2"></i>
            </button>
        </form>
    </div>
</div>
@endsection

@push('footer-scripts')
<script>
    function selectColor(color, el) {
        document.querySelectorAll('.color-option').forEach(c => c.classList.remove('selected'));
        el.classList.add('selected');
        document.getElementById('theme_color_input').value = color;
    }
</script>
@endpush
