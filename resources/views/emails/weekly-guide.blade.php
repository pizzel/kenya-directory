<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica', Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 0; }
        .wrapper { width: 100%; background-color: #f8fafc; padding: 40px 0; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .hero-img { width: 100%; height: 250px; object-fit: cover; }
        .content { padding: 30px; }
        .badge { background: #3b82f6; color: #ffffff; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        h1 { font-size: 24px; color: #1e293b; margin: 15px 0; line-height: 1.2; }
        .intro { font-size: 16px; line-height: 1.6; color: #475569; margin-bottom: 30px; border-left: 4px solid #3b82f6; padding-left: 15px; }
        .item { margin-bottom: 40px; border-bottom: 1px solid #f1f5f9; padding-bottom: 40px; }
        .item-number { font-size: 32px; font-weight: 900; color: #e2e8f0; margin-bottom: 10px; }
        .item-title { font-size: 20px; font-weight: bold; color: #1e293b; text-decoration: none; }
        .item-img { width: 100%; height: 200px; object-fit: cover; border-radius: 12px; margin: 15px 0; }
        .btn { display: inline-block; background: #1e293b; color: #ffffff; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 14px; }
        .footer { text-align: center; padding: 30px; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <img src="{{ $collection->getCoverImageUrl() }}" class="hero-img">
            <div class="content">
                <span class="badge">Weekly Discovery Guide</span>
                <h1>{{ $collection->title }}</h1>
                <div class="intro">{{ Str::limit(strip_tags($collection->description), 200) }}</div>

                @foreach($businesses->take(5) as $index => $business)
                    <div class="item">
                        <div class="item-number">{{ sprintf('%02d', $index + 1) }}</div>
                        <a href="{{ route('listings.show', $business->slug) }}" class="item-title">{{ $business->name }}</a>
                        <div style="font-size: 13px; color: #64748b;">★ {{ $business->google_rating }} • {{ $business->county->name }}</div>
                        <img src="{{ $business->getImageUrl('card') }}" class="item-img">
                        <a href="{{ route('listings.show', $business->slug) }}" class="btn">View Details</a>
                    </div>
                @endforeach

                <div style="text-align: center;">
                    <a href="{{ route('collections.show', $collection->slug) }}" style="color: #3b82f6; font-weight: bold; text-decoration: none;">View all {{ $businesses->count() }} places in this guide &rarr;</a>
                </div>
            </div>
            <div class="footer">
                <p><strong>Discover Kenya</strong></p>
                <p>Sent with ❤️ to help you explore the best of Kenya.</p>
                <p><a href="#">Unsubscribe</a></p>
            </div>
        </div>
    </div>
</body>
</html>