# Course Project - Alpha Release

## 🛠️ التقنيات المستخدمة (Tech Stack)

* **الواجهة الخلفية (Backend):** Laravel Framework (PHP)
* **قاعدة البيانات (Database):** MySQL
* **الواجهة الأمامية (Frontend):** HTML5, CSS3, JavaScript, Bootstrap
* **التحكم في الإصدار (Version Control):** Githup Desctop

---------------------

## ⚙️ متطلبات التشغيل (Prerequisites)

تأكد من تنصيب البرامج التالية على جهازك قبل البدء:
* [PHP](https://www.php.net/) (>= 8.1)
* [Composer](https://getcomposer.org/)
* [MySQL](https://www.mysql.com/) (أو XAMPP/Laragon)
* [Git](https://git-scm.com/)

----------------------

## 🚀 طريقة التثبيت والتشغيل (Installation)

 اتبع الخطوات التالية لتشغيل المشروع على بيئتك المحلية:

1. **استنساخ المستودع (Clone the repository):**
   ```bash
   git clone [ضع_رابط_مستودع_جيت_هاب_هنا]
   cd spotly
------------------------
   تثبيت الحزم البرمجية (Install Dependencies):

Bash
composer install
------------------------
إعداد ملف البيئة (Environment Setup):
قم بتعديل ملف .env و إعدادات قاعدة البيانات في الملف:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=spotly
DB_USERNAME=root
DB_PASSWORD=
------------------------
إعداد سيرفر البريد الإلكتروني (SMTP Configuration):
لضمان عمل نظام الـ OTP وإشعارات المخالفات، يجب إعداد الـ SMTP في ملف .env:

Code snippet
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="SpotLy System"
ملاحظة مهمة استخراج "كلمة مرور التطبيق" من جوجل
افتح متصفحك وادخل على حساب جوجل بتاعك (إيميلك الشخصي اللي تبي النظام يبعت منه).

اذهب إلى صفحة إدارة حساب Google (Manage your Google Account).

من القائمة الجانبية، اختر الأمان (Security).

انزل لقسم "كيفية الدخول إلى Google"، وتأكد إن ميزة التحقق بخطوتين (2-Step Verification) مفعلة (لو مش مفعلة، فعلها برقم تليفونك).

اضغط على التحقق بخطوتين، وانزل لآخر الصفحة بكل، حتلقى خيار اسمه كلمات مرور التطبيقات (App Passwords). اضغط عليه.
او اكتب في خانة البحث App Passwords

حيطلب منك تكتب اسم للتطبيق، اكتب مثلاً Spotly Project واضغط إنشاء (Create).

حتطلعلك نافذة فيها رقم سري متكون من 16 حرف (خلفيته صفراء). انسخ هذا الرقم لأن هذا هو اللي بنستخدموه في لارافيل
------------------------

توليد مفتاح التطبيق (Generate App Key):

Bash
php artisan key:generate

------------------------
بناء قاعدة البيانات (Run Migrations):

php artisan migrate
او لاستخدام بيانات جاهزة قم برفع ملف spotly.sql في موقع http://localhost/phpmyadmin
يفضل
**بناء قاعدة البيانات وزراعة البيانات الأولية (Migrate & Seed):**
   لتجهيز الجداول وإدخال بيانات تجريبية (ساحات، موظف، سائق بمحفظة مشحونة)، نفذ هذا الأمر:

php artisan migrate:fresh --seed
كيف تجربها عندك توا؟
افتح التيرمينال واكتب:

php artisan db:seed
------------------------

تشغيل السيرفر المحلي (Start the Server):

Bash
php artisan serve
المشروع الآن يعمل على الرابط: http://127.0.0.1:8000
