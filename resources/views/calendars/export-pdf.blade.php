<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $calendar->title }} - {{ $calendar->year }}</title>
    <style>
        @page {
            margin: 20mm;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #ec4899;
        }
        .header h1 {
            font-size: 28px;
            color: #ec4899;
            margin: 0 0 10px 0;
            font-weight: bold;
        }
        .header .subtitle {
            font-size: 16px;
            color: #666;
            margin: 5px 0;
        }
        .day-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
            border: 1px solid #f9a8d4;
            border-radius: 8px;
            padding: 15px;
            background-color: #fce7f3;
        }
        .day-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ec4899;
        }
        .day-number {
            font-size: 24px;
            font-weight: bold;
            color: #ec4899;
            margin-right: 15px;
            min-width: 50px;
        }
        .day-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            flex: 1;
        }
        .day-content {
            margin-top: 15px;
        }
        .day-content-text {
            font-size: 14px;
            color: #555;
            line-height: 1.8;
            margin-bottom: 15px;
        }
        .day-content-image {
            max-width: 100%;
            height: auto;
            margin: 15px 0;
            border-radius: 4px;
            border: 2px solid #f9a8d4;
        }
        .gift-type-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            margin-left: 10px;
        }
        .gift-type-text {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .gift-type-image_text {
            background-color: #fef3c7;
            color: #92400e;
        }
        .gift-type-product {
            background-color: #d1fae5;
            color: #065f46;
        }
        .unlocked-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            background-color: #10b981;
            color: white;
            margin-left: 10px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #f9a8d4;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
        .no-content {
            color: #999;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $calendar->title }}</h1>
        <div class="subtitle">{{ __('calendar.year') }}: {{ $calendar->year }}</div>
        @if($calendar->description)
            <div class="subtitle">{{ $calendar->description }}</div>
        @endif
        @if($calendar->owner)
            <div class="subtitle">{{ __('calendar.created_by') }}: {{ $calendar->owner->name }}</div>
        @endif
    </div>

    @forelse($days as $day)
        <div class="day-section">
            <div class="day-header">
                <div class="day-number">{{ __('calendar.day') }} {{ $day->day_number }}</div>
                <div class="day-title">
                    {{ $day->title ?? __('calendar.day_number', ['number' => $day->day_number]) }}
                    <span class="gift-type-badge gift-type-{{ $day->gift_type }}">
                        @if($day->gift_type === 'text')
                            {{ __('calendar.gift_type_text') }}
                        @elseif($day->gift_type === 'image_text')
                            {{ __('calendar.gift_type_image_text') }}
                        @elseif($day->gift_type === 'product')
                            {{ __('calendar.gift_type_product') }}
                        @endif
                    </span>
                    @if($day->unlocked_at)
                        <span class="unlocked-badge">{{ __('calendar.unlocked') }}</span>
                    @endif
                </div>
            </div>

            <div class="day-content">
                @if($day->content_image_path)
                    @php
                        $imagePath = storage_path('app/public/' . $day->content_image_path);
                        $imageExists = file_exists($imagePath);
                    @endphp
                    @if($imageExists)
                        <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents($imagePath)) }}" alt="{{ $day->title ?? __('calendar.day_number', ['number' => $day->day_number]) }}" class="day-content-image" />
                    @else
                        <div class="no-content">{{ __('calendar.image_not_found') }}</div>
                    @endif
                @endif

                @if($day->content_text && $day->content_text !== __('calendar.gift_hasnt_setup'))
                    <div class="day-content-text">
                        {!! nl2br(e($day->content_text)) !!}
                    </div>
                @endif

                @if($day->gift_type === 'product' && $day->product_code)
                    <div class="day-content-text">
                        <strong>{{ __('calendar.product_code') }}:</strong> {{ $day->product_code }}
                    </div>
                @endif

                @if(!$day->content_text || $day->content_text === __('calendar.gift_hasnt_setup'))
                    @if(!$day->content_image_path && $day->gift_type !== 'product')
                        <div class="no-content">{{ __('calendar.no_content_available') }}</div>
                    @endif
                @endif
            </div>
        </div>
    @empty
        <div class="day-section">
            <div class="no-content">{{ __('calendar.no_days_with_content') }}</div>
        </div>
    @endforelse

    <div class="footer">
        {{ __('calendar.exported_on') }}: {{ now()->format('d-m-Y H:i') }} | 
        {{ __('calendar.total_days') }}: {{ $days->count() }}
    </div>
</body>
</html>
