<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Demo data that walks through the main flows of the system:
 * sale, rent and exchange requests in every status, appointments,
 * rentals (active / returned / disputed), reviews, reports,
 * wishlists, notifications and activity logs.
 *
 * All demo accounts use the password: password
 */
class DemoDataSeeder extends Seeder
{
    /** @var array<string, int> */
    private array $users = [];

    /** @var array<string, int> */
    private array $items = [];

    /** @var array<string, int> */
    private array $requests = [];

    public function run(): void
    {
        if (DB::table('users')->where('email', 'admin@nearby.test')->exists()) {
            $this->command?->warn('Demo data already exists, skipping. Use migrate:fresh --seed to reseed.');

            return;
        }

        DB::transaction(function () {
            $this->seedUsers();
            $this->seedItems();
            $this->seedWishlists();
            $this->seedRequests();
            $this->seedReviews();
            $this->seedReports();
            $this->seedNotifications();
            $this->seedActivityLogs();
        });
    }

    private function seedUsers(): void
    {
        $password = Hash::make('password');
        $now = now();

        $users = [
            'admin' => ['مدير النظام', 'admin@nearby.test', '0911000000', 'أبو رمانة', 33.5155, 36.2860, false, true],
            'ahmad' => ['أحمد الخطيب', 'ahmad@nearby.test', '0933111222', 'المزة - فيلات غربية', 33.5032, 36.2541, false, false],
            'sara' => ['سارة العلي', 'sara@nearby.test', '0944222333', 'المالكي', 33.5201, 36.2789, false, false],
            'mohammad' => ['محمد حسن', 'mohammad@nearby.test', '0955333444', 'باب توما', 33.5136, 36.3155, false, false],
            'layla' => ['ليلى منصور', 'layla@nearby.test', '0966444555', 'كفرسوسة', 33.4952, 36.2700, false, false],
            'khaled' => ['خالد يوسف', 'khaled@nearby.test', '0977555666', 'ركن الدين', 33.5345, 36.3010, false, false],
            'rana' => ['رنا إبراهيم', 'rana@nearby.test', '0988666777', 'جرمانا', 33.4870, 36.3480, true, false],
        ];

        foreach ($users as $key => [$name, $email, $phone, $address, $lat, $lng, $blocked, $admin]) {
            $this->users[$key] = DB::table('users')->insertGetId([
                'full_name' => $name,
                'email' => $email,
                'email_verified_at' => $now,
                'password' => $password,
                'phone' => $phone,
                'address' => $address,
                'city' => 'دمشق',
                'latitude' => $lat,
                'longitude' => $lng,
                'is_blocked' => $blocked,
                'is_admin' => $admin,
                'rate' => 0,
                'created_at' => $now->copy()->subMonths(3),
                'updated_at' => $now,
            ]);
        }
    }

    private function seedItems(): void
    {
        $categories = DB::table('categories')->pluck('id', 'category_name');
        $conditions = DB::table('conditions')->pluck('id', 'condition_name');

        // key => [owner, category, condition, type, title, description, price, count, state, hint, lat, lng, variants, max_rent, rent_unit, images]
        $items = [
            'laptop' => ['ahmad', 'إلكترونيات', 'مستعمل - حالة ممتازة', 'sale', 'لابتوب Dell XPS 13', 'معالج i7 الجيل الحادي عشر، رام 16 غيغا، بطارية ممتازة مع الشاحن الأصلي.', 4500000, 1, 'sold', 'قرب جامع الأكرم', 33.5035, 36.2545, ['color' => 'فضي', 'storage' => '512GB SSD'], null, null, 2],
            'camera' => ['ahmad', 'إلكترونيات', 'شبه جديد', 'rent', 'كاميرا Canon EOS 250D', 'مناسبة للتصوير في المناسبات، مع عدسة 18-55 وبطاقة ذاكرة 64 غيغا.', 75000, 1, 'rented', 'المزة - جانب الحديقة', 33.5030, 36.2550, ['lens' => '18-55mm'], 14, 'day', 2],
            'tent' => ['ahmad', 'رياضة ورحلات', 'مستعمل - حالة جيدة', 'rent', 'خيمة رحلات لأربعة أشخاص', 'خيمة مقاومة للماء مع أوتاد وحقيبة حمل.', 25000, 2, 'active', 'المزة', 33.5028, 36.2539, ['capacity' => '4 أشخاص'], 7, 'day', 1],
            'drill' => ['sara', 'أدوات وعدد', 'مستعمل - حالة ممتازة', 'rent', 'مثقب كهربائي Bosch', 'مثقب مع مجموعة رؤوس كاملة، مناسب للخشب والجدران.', 15000, 1, 'active', 'المالكي - قرب الفرن', 33.5205, 36.2792, null, 5, 'day', 1],
            'projector' => ['sara', 'إلكترونيات', 'مستعمل - حالة جيدة', 'rent', 'جهاز عرض (بروجكتر) Epson', 'دقة Full HD مع كبل HDMI وشاشة عرض قابلة للطي.', 40000, 1, 'active', 'المالكي', 33.5199, 36.2785, null, 10, 'day', 1],
            'dress' => ['sara', 'ملابس وأحذية', 'جديد', 'sale', 'فستان سهرة', 'فستان سهرة لم يُلبس، مع الملصق الأصلي.', 350000, 1, 'active', 'المالكي', 33.5203, 36.2790, ['size' => 'M', 'color' => 'كحلي'], null, null, 2],
            'bicycle' => ['mohammad', 'دراجات ومركبات', 'مستعمل - حالة جيدة', 'exchange', 'دراجة جبلية مقاس 26', 'دراجة بـ 21 سرعة، الفرامل والجنزير بحالة جيدة. أقبل المبادلة بلابتوب أو جهاز لوحي.', 0, 1, 'active', 'باب توما - قرب الساحة', 33.5138, 36.3150, ['size' => '26'], null, null, 1],
            'guitar' => ['mohammad', 'آلات موسيقية', 'مستعمل - حالة ممتازة', 'sale', 'غيتار كلاسيكي Yamaha C40', 'مع حقيبة وأوتار احتياطية.', 600000, 1, 'active', 'باب توما', 33.5140, 36.3160, null, null, null, 1],
            'books' => ['layla', 'كتب وقرطاسية', 'مستعمل - حالة جيدة', 'exchange', 'مجموعة روايات عربية (12 كتاباً)', 'روايات لنجيب محفوظ وغسان كنفاني وغيرهم، أرغب بمبادلتها بكتب علمية.', 0, 1, 'active', 'كفرسوسة', 33.4955, 36.2705, null, null, null, 1],
            'stroller' => ['layla', 'مستلزمات أطفال', 'شبه جديد', 'sale', 'عربة أطفال قابلة للطي', 'خفيفة الوزن، استُعملت لأشهر قليلة.', 450000, 1, 'active', 'كفرسوسة - قرب المول', 33.4950, 36.2698, ['color' => 'رمادي'], null, null, 1],
            'sofa' => ['khaled', 'أثاث', 'مستعمل - حالة مقبولة', 'sale', 'كنبة ثلاثية', 'كنبة قماش متينة، تحتاج تنظيف بسيط. النقل على المشتري.', 800000, 1, 'active', 'ركن الدين', 33.5340, 36.3015, null, null, null, 1],
            'tablet' => ['khaled', 'هواتف وأجهزة لوحية', 'مستعمل - حالة ممتازة', 'exchange', 'جهاز لوحي Samsung Galaxy Tab A8', 'مع غطاء وشاحن، أرغب بمبادلته بدراجة.', 0, 1, 'active', 'ركن الدين', 33.5348, 36.3008, ['storage' => '64GB'], null, null, 1],
            'washer' => ['khaled', 'أجهزة منزلية', 'يحتاج إلى صيانة', 'sale', 'غسالة أوتوماتيك 7 كغ', 'تعمل لكن تحتاج تبديل مضخة التصريف.', 300000, 1, 'inactive', 'ركن الدين', 33.5343, 36.3012, null, null, null, 1],
            'phone_fake' => ['rana', 'هواتف وأجهزة لوحية', 'جديد', 'sale', 'آيفون 15 برو جديد بسعر مغرٍ', 'السعر نهائي، الدفع مسبقاً فقط.', 500000, 5, 'banned', 'جرمانا', 33.4872, 36.3482, null, null, null, 1],
        ];

        $now = now();

        foreach ($items as $key => [$owner, $category, $condition, $type, $title, $description, $price, $count, $state, $hint, $lat, $lng, $variants, $maxRent, $rentUnit, $images]) {
            $createdAt = $now->copy()->subDays(random_int(20, 60));

            $this->items[$key] = DB::table('items')->insertGetId([
                'owner_id' => $this->users[$owner],
                'category_id' => $categories[$category],
                'condition_id' => $conditions[$condition],
                'transaction_type' => $type,
                'title' => $title,
                'description' => $description,
                'price' => $price,
                'count' => $count,
                'state' => $state,
                'location_hint' => $hint,
                'latitude' => $lat,
                'longitude' => $lng,
                'variants' => $variants ? json_encode($variants, JSON_UNESCAPED_UNICODE) : null,
                'max_rent_duration' => $maxRent,
                'rent_unit' => $rentUnit,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            for ($i = 1; $i <= $images; $i++) {
                DB::table('item_images')->insert([
                    'item_id' => $this->items[$key],
                    'image_path' => "items/{$key}-{$i}.jpg",
                ]);
            }
        }
    }

    private function seedWishlists(): void
    {
        $wishlists = [
            ['sara', 'guitar'], ['sara', 'tent'],
            ['mohammad', 'projector'], ['mohammad', 'tablet'],
            ['layla', 'camera'], ['layla', 'dress'],
            ['khaled', 'bicycle'], ['ahmad', 'books'],
        ];

        foreach ($wishlists as [$user, $item]) {
            DB::table('wishlists')->insert([
                'user_id' => $this->users[$user],
                'item_id' => $this->items[$item],
                'created_at' => now()->subDays(random_int(1, 15)),
            ]);
        }
    }

    private function seedRequests(): void
    {
        $now = now();

        // key => [item, requester, status, requester_note, owner_note, count, days_ago]
        $requests = [
            'laptop_sale' => ['laptop', 'sara', 'finished', 'هل السعر قابل للتفاوض؟', 'تم الاتفاق على السعر، أهلاً بك.', 1, 18],
            'camera_rent' => ['camera', 'mohammad', 'received', 'أحتاجها لتصوير حفل زفاف أخي.', 'موافق، يرجى المحافظة عليها.', 1, 6],
            'drill_rent' => ['drill', 'layla', 'finished', 'لتركيب رفوف في المنزل.', null, 1, 15],
            'projector_rent' => ['projector', 'khaled', 'finished', 'لعرض تقديمي في الجامعة.', null, 1, 25],
            'bicycle_exchange' => ['bicycle', 'khaled', 'waiting', 'أعرض عليك جهازي اللوحي مقابل الدراجة.', null, 1, 2],
            'books_exchange' => ['books', 'ahmad', 'approved', 'لدي كتب برمجة وفيزياء للمبادلة.', 'فكرة جيدة، لنلتقِ ونتفق.', 1, 3],
            'sofa_sale' => ['sofa', 'sara', 'rejected', 'هل يمكن التوصيل؟', 'عذراً، تم الاتفاق مع شخص آخر.', 1, 9],
            'stroller_sale' => ['stroller', 'mohammad', 'canceled', 'أرغب بشرائها.', null, 1, 7],
            'tent_rent' => ['tent', 'layla', 'waiting', 'لرحلة عائلية نهاية الأسبوع.', null, 1, 1],
            'guitar_sale' => ['guitar', 'sara', 'approved', 'أريد شراءه لابني.', 'موافق، متى يناسبك الاستلام؟', 1, 2],
        ];

        foreach ($requests as $key => [$item, $requester, $status, $rNote, $oNote, $count, $daysAgo]) {
            $date = $now->copy()->subDays($daysAgo);

            $this->requests[$key] = DB::table('requests')->insertGetId([
                'item_id' => $this->items[$item],
                'requester_id' => $this->users[$requester],
                'requester_note' => $rNote,
                'owner_note' => $oNote,
                'count' => $count,
                'status' => $status,
                'request_date' => $date,
                'updated_at' => $status === 'waiting' ? $date : $date->copy()->addDay(),
            ]);
        }

        // Exchange offers
        DB::table('exchange_offers')->insert([
            [
                'request_id' => $this->requests['bicycle_exchange'],
                'offered_item_id' => $this->items['tablet'],
                'offered_description' => null,
            ],
            [
                'request_id' => $this->requests['books_exchange'],
                'offered_item_id' => null,
                'offered_description' => 'خمسة كتب: أساسيات البرمجة بلغة بايثون، الفيزياء الحديثة، وثلاثة كتب في الرياضيات.',
            ],
        ]);

        // Rentals: active, returned & confirmed, disputed (returned 2 days late)
        // [request, start_days_ago, expected_return_days_ago, actual_return_days_ago, return_status, reported_days_ago]
        $rentals = [
            ['camera_rent', 5, -2, null, 'pending', null],
            ['drill_rent', 14, 11, 11, 'confirmed', 11],
            ['projector_rent', 24, 17, 15, 'disputed', 15],
        ];

        foreach ($rentals as [$request, $startAgo, $expectedAgo, $actualAgo, $status, $reportedAgo]) {
            DB::table('rentals')->insert([
                'request_id' => $this->requests[$request],
                'start_date' => $now->copy()->subDays($startAgo)->toDateString(),
                // negative "ago" = in the future
                'expected_return_date' => $now->copy()->subDays($expectedAgo)->toDateString(),
                'actual_return_date' => $actualAgo !== null ? $now->copy()->subDays($actualAgo)->toDateString() : null,
                'return_status' => $status,
                'return_reported_at' => $reportedAgo !== null ? $now->copy()->subDays($reportedAgo) : null,
                'created_at' => $now->copy()->subDays($startAgo),
                'updated_at' => $now,
            ]);
        }

        // Appointments
        $appointments = [
            ['laptop_sale', -17, 'مقهى الروضة - شارع العابد', 'confirmed'],
            ['camera_rent', -5, 'المزة - أمام جامع الأكرم', 'confirmed'],
            ['drill_rent', -14, 'المالكي - قرب الفرن', 'confirmed'],
            ['projector_rent', -24, 'كلية الهندسة المعلوماتية', 'confirmed'],
            ['books_exchange', 2, 'مكتبة الأسد الوطنية', 'new'],
            ['guitar_sale', 1, 'باب توما - الساحة', 'new'],
            ['sofa_sale', -8, 'ركن الدين', 'canceled'],
        ];

        foreach ($appointments as [$request, $inDays, $location, $status]) {
            DB::table('appointments')->insert([
                'request_id' => $this->requests[$request],
                'meeting_date' => $now->copy()->addDays($inDays)->setTime(17, 30),
                'meeting_location' => $location,
                'status' => $status,
                'created_at' => $now->copy()->subDays(max(1, -$inDays + 1)),
                'updated_at' => $now,
            ]);
        }
    }

    private function seedReviews(): void
    {
        // [request, reviewer, reviewed, rating, comment]
        $reviews = [
            ['laptop_sale', 'sara', 'ahmad', 5, 'الجهاز كما في الوصف تماماً، والبائع محترم وملتزم بالموعد.'],
            ['laptop_sale', 'ahmad', 'sara', 5, 'مشترية متعاونة ودقيقة في الموعد.'],
            ['drill_rent', 'layla', 'sara', 4, 'المثقب ممتاز، تأخرت قليلاً في الرد على الرسائل.'],
            ['drill_rent', 'sara', 'layla', 5, 'أعادت المثقب نظيفاً وفي الموعد.'],
            ['projector_rent', 'sara', 'khaled', 2, 'تأخر في إعادة الجهاز وكان الكبل مفقوداً.'],
            ['projector_rent', 'khaled', 'sara', 3, 'الجهاز جيد لكن حصل خلاف على موعد الإرجاع.'],
        ];

        foreach ($reviews as [$request, $reviewer, $reviewed, $rating, $comment]) {
            DB::table('reviews')->insert([
                'request_id' => $this->requests[$request],
                'reviewer_id' => $this->users[$reviewer],
                'reviewed_user_id' => $this->users[$reviewed],
                'rating' => $rating,
                'comment' => $comment,
                'created_at' => now()->subDays(random_int(1, 10)),
            ]);
        }

        // Refresh the cached rating on users.
        DB::table('reviews')
            ->selectRaw('reviewed_user_id, AVG(rating) as avg_rating')
            ->groupBy('reviewed_user_id')
            ->get()
            ->each(fn ($row) => DB::table('users')
                ->where('id', $row->reviewed_user_id)
                ->update(['rate' => round($row->avg_rating, 2)]));
    }

    private function seedReports(): void
    {
        $now = now();

        DB::table('reports')->insert([
            [
                'reporter_id' => $this->users['layla'],
                'item_id' => $this->items['phone_fake'],
                'request_id' => null,
                'reason' => 'إعلان احتيالي: سعر غير منطقي ويطلب الدفع المسبق.',
                'status' => 'resolved',
                'reported_at' => $now->copy()->subDays(12),
                'updated_at' => $now->copy()->subDays(11),
            ],
            [
                'reporter_id' => $this->users['sara'],
                'item_id' => null,
                'request_id' => $this->requests['projector_rent'],
                'reason' => 'المستأجر أعاد الجهاز متأخراً يومين ومن دون كبل HDMI.',
                'status' => 'pending',
                'reported_at' => $now->copy()->subDays(15),
                'updated_at' => $now->copy()->subDays(15),
            ],
            [
                'reporter_id' => $this->users['mohammad'],
                'item_id' => $this->items['sofa'],
                'request_id' => null,
                'reason' => 'الصور لا تطابق الحالة الحقيقية للكنبة.',
                'status' => 'rejected',
                'reported_at' => $now->copy()->subDays(5),
                'updated_at' => $now->copy()->subDays(4),
            ],
        ]);
    }

    private function seedNotifications(): void
    {
        // [user, title, message, is_read, days_ago]
        $notifications = [
            ['mohammad', 'طلب جديد', 'قدّم خالد يوسف عرض مبادلة على "دراجة جبلية مقاس 26".', false, 2],
            ['layla', 'تمت الموافقة على طلبك', 'وافق المالك على طلب المبادلة على "مجموعة روايات عربية".', false, 3],
            ['ahmad', 'تمت الموافقة على طلبك', 'وافقت ليلى منصور على طلب المبادلة الخاص بك.', true, 3],
            ['sara', 'تم رفض طلبك', 'رفض خالد يوسف طلبك على "كنبة ثلاثية".', true, 8],
            ['mohammad', 'تذكير بموعد الإرجاع', 'موعد إرجاع "كاميرا Canon EOS 250D" بعد أيام قليلة.', false, 0],
            ['ahmad', 'طلب جديد', 'طلبت ليلى منصور استئجار "خيمة رحلات لأربعة أشخاص".', false, 1],
            ['sara', 'تقييم جديد', 'حصلت على تقييم جديد من ليلى منصور.', true, 9],
            ['rana', 'تم حظر إعلانك', 'تم حظر إعلان "آيفون 15 برو" لمخالفته سياسة الاستخدام.', true, 11],
            ['admin', 'بلاغ جديد', 'تم تقديم بلاغ جديد على طلب استئجار "جهاز عرض (بروجكتر) Epson".', false, 15],
        ];

        foreach ($notifications as [$user, $title, $message, $read, $daysAgo]) {
            DB::table('notifications')->insert([
                'user_id' => $this->users[$user],
                'title' => $title,
                'message' => $message,
                'is_read' => $read,
                'created_at' => now()->subDays($daysAgo),
            ]);
        }
    }

    private function seedActivityLogs(): void
    {
        $now = now();

        $logs = [
            ['ahmad', 'create', 'items', $this->items['tent'], null, ['title' => 'خيمة رحلات لأربعة أشخاص', 'state' => 'active'], 40],
            ['ahmad', 'update', 'items', $this->items['laptop'], ['state' => 'active'], ['state' => 'sold'], 17],
            ['admin', 'update', 'items', $this->items['phone_fake'], ['state' => 'active'], ['state' => 'banned'], 11],
            ['admin', 'update', 'users', $this->users['rana'], ['is_blocked' => false], ['is_blocked' => true], 11],
            ['khaled', 'update', 'requests', $this->requests['sofa_sale'], ['status' => 'waiting'], ['status' => 'rejected'], 8],
            ['mohammad', 'update', 'requests', $this->requests['stroller_sale'], ['status' => 'waiting'], ['status' => 'canceled'], 6],
            ['khaled', 'update', 'items', $this->items['washer'], ['state' => 'active'], ['state' => 'inactive'], 4],
        ];

        foreach ($logs as [$user, $action, $table, $recordId, $old, $new, $daysAgo]) {
            DB::table('activity_logs')->insert([
                'user_id' => $this->users[$user],
                'action' => $action,
                'table_name' => $table,
                'record_id' => $recordId,
                'old_values' => $old ? json_encode($old, JSON_UNESCAPED_UNICODE) : null,
                'new_values' => $new ? json_encode($new, JSON_UNESCAPED_UNICODE) : null,
                'created_at' => $now->copy()->subDays($daysAgo),
            ]);
        }
    }
}
