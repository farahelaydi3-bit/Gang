@php
    $ammanAreas = [
        'المدينة',
        'بسمان',
        'ماركا',
        'النصر',
        'اليرموك',
        'رأس العين',
        'بدر',
        'زهران',
        'العبدلي',
        'طارق',
        'القويسمة',
        'خريبة السوق',
        'أم قصير والمقابلين',
        'وادي السير',
        'بدر الجديدة',
        'صويلح',
        'تلاع العلي',
        'الجبيهة',
        'شفا بدران',
        'أبو نصير',
        'أحد',
        'مرج الحمام',
    ];
    $selectedAmmanArea = (string) ($selectedArea ?? request('area', ''));
@endphp

<option value="">كل مناطق عمّان</option>
@foreach ($ammanAreas as $ammanArea)
    <option value="{{ $ammanArea }}" @selected($selectedAmmanArea === $ammanArea)>{{ $ammanArea }}</option>
@endforeach
