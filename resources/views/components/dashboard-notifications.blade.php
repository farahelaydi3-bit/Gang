@php
    $notifications = array_filter([
        'success' => session('success'),
        'error' => session('error'),
    ], fn ($message) => filled($message));
@endphp

@if ($notifications !== [])
    <div class="dashboard-notifications" id="dashboard-notifications">
        @foreach ($notifications as $type => $message)
            <div class="dashboard-notification dashboard-notification--{{ $type }}">
                <span class="dashboard-notification-icon" aria-hidden="true">{{ $type === 'success' ? '✓' : '!' }}</span>
                <p dir="auto" role="{{ $type === 'error' ? 'alert' : 'status' }}">{{ $message }}</p>
                <button type="button" class="dashboard-notification-close" hidden
                    aria-label="{{ app()->isLocale('ar') ? 'إغلاق التنبيه' : 'Dismiss notification' }}">×</button>
            </div>
        @endforeach
    </div>

    <script>
        (() => {
            const notifications = document.getElementById('dashboard-notifications');

            notifications.querySelectorAll('.dashboard-notification').forEach(notification => {
                const close = notification.querySelector('button');
                const dismiss = () => {
                    notification.remove();
                    if (!notifications.children.length) notifications.remove();
                };

                close.addEventListener('click', dismiss);
                notification.addEventListener('animationend', event => {
                    if (event.animationName === 'dashboard-notification-countdown') dismiss();
                });
                close.hidden = false;
            });

            notifications.dataset.enhanced = '';
        })();
    </script>
@endif
