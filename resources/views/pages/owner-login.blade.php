<x-dashboard-sign-in :action="route('admin.login.submit')" :arabic="true" :copy="[
    'title' => 'دخول مالك الملعب',
    'brand' => 'لوحة مالك الملعب',
    'description' => 'أدر ملاعبك وحجوزاتك واشتراكاتك من مكان واحد.',
]">
    <a href="{{ route('admin.register') }}">سجّل منشأتك</a>
    <a href="{{ route('login') }}">دخول اللاعب</a>
</x-dashboard-sign-in>
