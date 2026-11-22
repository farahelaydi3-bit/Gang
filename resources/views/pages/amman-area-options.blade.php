@php
    $ammanAreas = [
        'المدينة' => 'Al Madinah',
        'بسمان' => 'Basman',
        'ماركا' => 'Marka',
        'النصر' => 'Al Nasr',
        'اليرموك' => 'Al Yarmouk',
        'رأس العين' => 'Ras Al Ain',
        'بدر' => 'Badr',
        'زهران' => 'Zahran',
        'العبدلي' => 'Al Abdali',
        'طارق' => 'Tariq',
        'القويسمة' => 'Al Quwaysimah',
        'خريبة السوق' => 'Khreibet Al Souq',
        'أم قصير والمقابلين' => 'Umm Qusair and Al Muqabalayn',
        'وادي السير' => 'Wadi Al Seer',
        'بدر الجديدة' => 'Badr Al Jadidah',
        'صويلح' => 'Sweileh',
        'تلاع العلي' => 'Tlaa Al Ali',
        'الجبيهة' => 'Al Jubaiha',
        'شفا بدران' => 'Shafa Badran',
        'أبو نصير' => 'Abu Nseir',
        'أحد' => 'Uhud',
        'مرج الحمام' => 'Marj Al Hamam',
    ];
    $selectedAmmanArea = (string) ($selectedArea ?? request('area', ''));
@endphp

<option value="">{{ __('All areas of Amman') }}</option>
@foreach ($ammanAreas as $ammanArea => $englishName)
    <option value="{{ $ammanArea }}" @selected($selectedAmmanArea === $ammanArea)>{{ app()->isLocale('ar') ? $ammanArea : $englishName }}</option>
@endforeach
