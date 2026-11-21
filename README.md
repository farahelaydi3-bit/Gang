# Gang

**Gang** منصة لحجز الملاعب وتنظيم مباريات الأصدقاء في عمّان. بتجمع اللاعبين وأصحاب الملاعب بمكان واحد، وبتسهّل استكشاف الملاعب، معرفة المواعيد والأسعار، تثبيت الحجوزات، والانضمام للمباريات المفتوحة.

## عن المشروع

المشروع مبني باستخدام Laravel، وواجهاته مكتوبة بـ Blade وCSS وJavaScript. المنصة فيها ثلاثة أنواع من الحسابات:

- **اللاعب:** يستكشف الملاعب والمباريات، يحجز، يدير اشتراكاته، ويستخدم المساعد الذكي.
- **صاحب الملعب:** يدير منشأته وملاعبه والحجوزات والمباريات.
- **السوبر أدمن:** يدير أصحاب الملاعب والمنشآت والرياضات ومحتوى المنصة.

## الميزات

- استعراض الملاعب والبحث حسب الرياضة والمنطقة.
- عرض الملاعب القريبة على خريطة تفاعلية.
- حجز الملاعب مع اختيار وقت متاح حسب ساعات عمل الملعب.
- دفع عربون الحجز ومتابعة الحجوزات.
- اشتراكات بمواعيد أسبوعية ثابتة مع فحص التوفر والفاتورة.
- إنشاء مباريات مفتوحة والانضمام إليها وقوائم الانتظار.
- ملفات اللاعبين وأصحاب الملاعب والتحقق من رقم الهاتف.
- تقييمات للملاعب ومراجعة المحتوى.
- مساعد Gang الذكي للبحث عن الملاعب والمواعيد.
- صفحات عامة مخصصة لأصحاب الملاعب.

## المتطلبات

- PHP 8.2 أو أحدث، مع إضافات PHP اللازمة لـ Laravel وPDO لقاعدة البيانات.
- Composer.
- MySQL أو MariaDB للتشغيل الاعتيادي.
- Node.js وnpm لبناء ملفات الواجهة وتشغيل Vite.

لتشغيل وضع الديمو المعزول، تحتاج كمان إضافة `pdo_sqlite`.

## تشغيل المشروع محليًا

1. نزّل المشروع وثبّت اعتماديات PHP وJavaScript:

   ```bash
   composer install
   npm install
   ```

2. أنشئ ملف البيئة ومفتاح التطبيق:

   على Windows PowerShell:

   ```powershell
   Copy-Item .env.example .env
   php artisan key:generate
   ```

   على macOS أو Linux:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. أنشئ قاعدة بيانات MySQL باسم `gang`، وبعدها حدّث بيانات الاتصال في `.env`:

   ```dotenv
   APP_NAME=Gang
   APP_URL=http://127.0.0.1:8000
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=gang
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. شغّل migrations والبيانات الأساسية:

   ```bash
   php artisan migrate --seed
   ```

5. شغّل بيئة التطوير:

   ```bash
   composer run dev
   ```

   هذا الأمر يشغّل Laravel وVite وعامل الطوابير وسجلّات التطوير. افتح [http://127.0.0.1:8000](http://127.0.0.1:8000).

   لبناء ملفات الواجهة بدون وضع التطوير:

   ```bash
   npm run build
   ```

## روابط المنصة الأساسية

| الصفحة | المسار |
| --- | --- |
| الرئيسية | `/` |
| الملاعب | `/courts` |
| ملعب قريب | `/nearby` |
| المباريات المفتوحة | `/games` |
| إنشاء حجز | `/bookings/create` |
| حجوزاتي | `/bookings` |
| اشتراكاتي | `/my-subscriptions` |
| المساعد الذكي | `/assistant` |
| دخول صاحب الملعب | `/admin/login` |
| دخول السوبر أدمن | `/super-admin/login` |

## الدفع والخدمات الخارجية

الإعدادات الافتراضية في `.env.example` تستخدم بوابة الدفع التجريبية:

```dotenv
PAYMENT_GATEWAY=demo
PAYMENT_DEMO_ENABLED=true
```

الدفع التجريبي مخصص للتطوير ولا يخصم مبلغًا حقيقيًا. يمكن إعداد مفاتيح خدمات خارجية اختيارية من ملف `.env` عند الحاجة:

- `GEMINI_API_KEY` للمساعد الذكي وأدوات Gemini.
- `TEXTBEE_API_KEY` و`TEXTBEE_DEVICE_ID` لإرسال رسائل SMS عبر TextBee.
- مفاتيح Stripe عند إعداد الدفع على بيئة مناسبة.

لا تضف مفاتيح API أو كلمات مرور أو أي قيم سرية إلى Git أو إلى README.

## أوامر مفيدة

```bash
php artisan route:list       # عرض المسارات
php artisan migrate:status   # حالة migrations
php artisan test             # تشغيل الاختبارات
npm run dev                  # تشغيل Vite أثناء التطوير
npm run build                # بناء ملفات الواجهة
```

## ملفات المشروع

- `app/Http/Controllers` — منطق الصفحات والحجوزات والمدفوعات.
- `app/Models` — نماذج قاعدة البيانات.
- `app/Services` — منطق التوفر والاشتراكات والتكاملات.
- `resources/views` — قوالب Blade.
- `public/courtbook-assets` — CSS والصور وأصول واجهة Gang.
- `routes` — مسارات اللاعبين وأصحاب الملاعب والسوبر أدمن.
- `database/migrations` — تغييرات بنية قاعدة البيانات.

## روابط المشروع

- [لوحة Trello](https://trello.com/b/VnELJ4DJ/courtly)
- [ملف Figma](https://www.figma.com/design/Ga1DsZWUEpK9qxpq9icO96/Untitled?node-id=0-1&t=Ot7QhuLpYDX8w93T-1)
