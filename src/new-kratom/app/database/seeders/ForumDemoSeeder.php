<?php

namespace Database\Seeders;

use App\Models\ForumCategory;
use App\Models\ForumPost;
use App\Models\ForumPostReaction;
use App\Models\ForumPostVote;
use App\Models\ForumTopic;
use App\Models\User;
use App\Support\Forum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ForumDemoSeeder extends Seeder
{
    private const DEMO_EMAIL = 'parabellum.koval@gmail.com';

    /** @var array<string, User> */
    private array $forumUsers = [];

    public function run(): void
    {
        $categories = $this->seedCategories();
        $users = $this->seedUsers();
        $this->forumUsers = $users;

        ForumPostReaction::query()->delete();
        ForumPostVote::query()->delete();
        ForumPost::query()->delete();
        ForumTopic::query()->delete();

        $topics = $this->seedTopics($categories, $users);
        $this->seedPosts($topics, $users);
    }

    private function seedCategories(): array
    {
        $out = [];

        foreach (Forum::defaultCategories() as $category) {
            $model = ForumCategory::updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'label' => $category['label'],
                    'icon' => $category['icon'],
                    'position' => $category['position'],
                    'is_active' => true,
                ],
            );

            $out[$model->slug] = $model;
        }

        return $out;
    }

    private function seedUsers(): array
    {
        $demo = User::firstOrCreate(
            ['email' => self::DEMO_EMAIL],
            [
                'name' => 'Andrej Koval',
                'password' => Hash::make('password'),
                'phone' => '+420 777 111 222',
                'email_verified_at' => now(),
                'marketing_consent' => true,
                'locale' => 'cs',
            ],
        );

        $demo->forceFill([
            'forum_signature' => 'Sleduji COA, srovnávám šarže a rád si ukládám praktické poznámky z diskuzí.',
            'forum_reputation' => 180,
        ])->save();

        $people = [
            ['name' => 'Anna K.', 'email' => 'anna.forum@vivadzen.test', 'signature' => 'Moderátorka komunity. Kvalita, COA a pravidla PML.', 'rep' => 920],
            ['name' => 'Jakub M.', 'email' => 'jakub.forum@vivadzen.test', 'signature' => 'Pomáhám novým s výběrem a dávkováním.', 'rep' => 620],
            ['name' => 'Lucie B.', 'email' => 'lucie.forum@vivadzen.test', 'signature' => 'Čaje, tradice a příprava bez zbytečného spěchu.', 'rep' => 430],
            ['name' => 'Tereza H.', 'email' => 'tereza.forum@vivadzen.test', 'signature' => 'Botanika, původ a rozdíly mezi regiony.', 'rep' => 260],
            ['name' => 'Petr V.', 'email' => 'petr.forum@vivadzen.test', 'signature' => 'Začátečník, který si pečlivě zapisuje zkušenosti.', 'rep' => 35],
        ];

        $users = ['andrej' => Forum::ensureUserProfile($demo->fresh())];

        foreach ($people as $person) {
            $user = User::updateOrCreate(
                ['email' => $person['email']],
                [
                    'name' => $person['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'marketing_consent' => false,
                    'locale' => 'cs',
                    'forum_signature' => $person['signature'],
                    'forum_reputation' => $person['rep'],
                ],
            );

            $users[Str::before($person['email'], '.forum')] = Forum::ensureUserProfile($user);
        }

        return $users;
    }

    private function seedTopics(array $categories, array $users): array
    {
        $rows = [
            [
                'key' => 'coa-prakticky',
                'author' => 'andrej',
                'category' => 'beginners',
                'title' => 'Jak si ukládáte COA k jednotlivým šaržím?',
                'emoji' => '🧪',
                'body' => "Začal jsem si k produktům ukládat PDF certifikáty a čísla šarží, ale po pár objednávkách v tom začínám mít chaos.\n\nMáte někdo jednoduchý systém, jak si spojit datum nákupu, šarži, COA a vlastní poznámku? Ideálně něco, co zvládne i člověk bez tabulek.",
                'score' => 74,
                'featured' => true,
                'pinned' => true,
                'days' => 8,
                'cover' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Green_Kratom_Leaf.jpg?width=1280',
                'credit' => 'Wikimedia Commons / ThorPorre',
            ],
            [
                'key' => 'green-white-maeng-da',
                'author' => 'andrej',
                'category' => 'strains',
                'title' => 'Green Maeng Da vs White Maeng Da: rozdíl v praxi',
                'emoji' => '🌅',
                'body' => "Papírově chápu rozdíl mezi zelenou a bílou Maeng Da, ale zajímá mě praktická zkušenost u stejného výrobce.\n\nKdy berete zelenou a kdy bílou? A jak moc se rozdíl mění mezi šaržemi?",
                'score' => 52,
                'featured' => true,
                'days' => 6,
                'cover' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Mitragyna%20speciosa%20Leaf.JPG?width=1280',
                'credit' => 'Wikimedia Commons',
            ],
            [
                'key' => 'cajova-priprava',
                'author' => 'lucie',
                'category' => 'preparation',
                'title' => 'Čajová příprava: teplota, citron a čas louhování',
                'emoji' => '🍵',
                'body' => "Sbírám konkrétní postupy pro přípravu čaje. Zajímá mě hlavně teplota vody, kyselé prostředí a jestli někdo měří konzistenci mezi dávkami.\n\nMůj základ: voda pod bodem varu, citron, 15 minut a potom přecedit přes jemné sítko.",
                'score' => 88,
                'featured' => true,
                'days' => 12,
                'cover' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Kratom%20leaves.jpg?width=1280',
                'credit' => 'Wikimedia Commons',
            ],
            [
                'key' => 'pml-licence',
                'author' => 'anna',
                'category' => 'legal',
                'title' => 'PML licence: co si má zákazník ověřit před nákupem',
                'emoji' => '⚖️',
                'body' => "Krátký checklist pro nové členy: ověřte licenci prodejce, dostupné COA ke konkrétní šarži, jasné označení produktu a pravidla pro věk kupujícího.\n\nDo vlákna přidávejte otázky k legislativě, ale ne právní rady pro obcházení pravidel.",
                'score' => 134,
                'pinned' => true,
                'days' => 10,
                'cover' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Kratom%20tree.jpg?width=1280',
                'credit' => 'Wikimedia Commons',
            ],
            [
                'key' => 'prvni-objednavka',
                'author' => 'petr',
                'category' => 'beginners',
                'title' => 'První objednávka: co má smysl koupit na test?',
                'emoji' => '🌱',
                'body' => "Ahoj, chci udělat první menší objednávku a nechci vzít pět věcí najednou. Dává větší smysl 25 g jedné odrůdy, nebo malé balíčky více barev?\n\nJde mi hlavně o to, abych poznal rozdíl a neudělal z toho chaos.",
                'score' => 31,
                'days' => 3,
                'cover' => null,
            ],
            [
                'key' => 'pauzy-tolerance',
                'author' => 'jakub',
                'category' => 'effects',
                'title' => 'Tolerance a pauzy: jaký režim vám funguje dlouhodobě?',
                'emoji' => '🔥',
                'body' => "Téma pro zkušenější: jak často zařazujete pauzy a jak poznáte, že už je potřeba ubrat?\n\nProsím bez heroických dávek. Zajímají mě udržitelné návyky, rotace odrůd a signály, které sledujete.",
                'score' => 96,
                'days' => 16,
                'cover' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Kratom%20%28Drug%29.jpg?width=1280',
                'credit' => 'Wikimedia Commons',
            ],
        ];

        $topics = [];

        foreach ($rows as $row) {
            $publishedAt = Carbon::now()->subDays($row['days'])->subHours(random_int(1, 8));
            $slug = Str::slug($row['title']);
            $coverPath = $row['cover'] ? $this->downloadCover($row['cover'], $slug) : null;

            $topic = ForumTopic::create([
                'user_id' => $users[$row['author']]->id,
                'forum_category_id' => $categories[$row['category']]->id,
                'title' => $row['title'],
                'slug' => $slug,
                'emoji' => $row['emoji'],
                'body' => $row['body'],
                'cover_path' => $coverPath,
                'cover_url' => $coverPath ? null : $row['cover'],
                'cover_source_url' => $row['cover'],
                'cover_credit' => $row['credit'] ?? null,
                'status' => 'approved',
                'is_pinned' => (bool) ($row['pinned'] ?? false),
                'is_featured' => (bool) ($row['featured'] ?? false),
                'views_count' => random_int(240, 1800),
                'score' => $row['score'],
                'published_at' => $publishedAt,
                'last_post_at' => $publishedAt,
                'last_post_user_id' => $users[$row['author']]->id,
                'created_at' => $publishedAt,
                'updated_at' => $publishedAt,
            ]);

            $topics[$row['key']] = $topic;
        }

        return $topics;
    }

    private function seedPosts(array $topics, array $users): void
    {
        $this->post($topics['coa-prakticky'], $users['anna'], 'Používám jednoduchý zápis: číslo objednávky, šarže, odkaz na COA a vlastní poznámka. Nejdůležitější je ukládat COA hned v den nákupu, ne až zpětně.', 7, 18, ['👍' => ['andrej', 'jakub'], '🧪' => ['tereza']]);
        $p = $this->post($topics['coa-prakticky'], $users['andrej'], 'Dává smysl. Přidám si k tomu ještě datum otevření balení, protože někdy hodnotím až po týdnu a pak se mi pletou dojmy.', 6, 9, ['👍' => ['anna']]);
        $this->post($topics['coa-prakticky'], $users['tereza'], 'U rostlinných produktů se hodí zapisovat i region nebo název odrůdy. Když se šarže změní, člověk hned vidí, jestli reaguje na původ nebo jen na čerstvost.', 5, 14, ['🌿' => ['andrej', 'lucie']], $p);

        $this->post($topics['green-white-maeng-da'], $users['jakub'], 'U mě je zelená univerzálnější. Bílou beru jen dopoledne, jinak mi večer zbytečně prodlužuje aktivitu.', 5, 21, ['👍' => ['andrej', 'petr']]);
        $this->post($topics['green-white-maeng-da'], $users['andrej'], 'Tohle přesně řeším. Zelená mi přijde stabilnější mezi šaržemi, bílá je výraznější, ale citlivější na množství.', 4, 11, ['🔥' => ['jakub']]);
        $this->post($topics['green-white-maeng-da'], $users['lucie'], 'Doporučuji testovat ve stejný čas dne a bez kávy. Jinak člověk porovnává spíš rutinu než odrůdu.', 3, 15, ['🙏' => ['andrej', 'petr']]);

        $this->post($topics['cajova-priprava'], $users['andrej'], 'Citron mi pomohl hlavně chuťově. Louhuju 12-15 minut a potom přecedím přes papírový filtr, když chci jemnější nápoj.', 10, 19, ['🍵' => ['lucie'], '👍' => ['tereza']]);
        $this->post($topics['cajova-priprava'], $users['tereza'], 'Příliš vroucí voda podle mě zhorší chuť. Držím se kolem 85-90 °C a míchám až po pár minutách.', 9, 16, ['🌿' => ['lucie', 'andrej']]);

        $this->post($topics['pml-licence'], $users['andrej'], 'Za mě je největší rozdíl v tom, jestli obchod ukáže COA ke konkrétní šarži bez vyžádání. Když je certifikát schovaný, beru to jako varování.', 9, 24, ['🧪' => ['anna', 'jakub'], '👍' => ['petr']]);
        $this->post($topics['pml-licence'], $users['anna'], 'Přesně. A u COA hlídat, že sedí číslo šarže na obalu a v dokumentu. Obecný laboratorní list není totéž.', 8, 32, ['👍' => ['andrej', 'lucie', 'tereza']]);

        $this->post($topics['prvni-objednavka'], $users['andrej'], 'Vzal bych dvě malé věci: jednu zelenou a jednu červenou. Stejný prodejce, podobné datum testu, malé poznámky po každém použití.', 2, 12, ['👍' => ['petr']]);
        $this->post($topics['prvni-objednavka'], $users['jakub'], 'Souhlas. A hlavně netestovat dvě nové věci v jednom dni, protože pak nevíš, co hodnotíš.', 2, 17, ['🙏' => ['petr', 'andrej']]);

        $this->post($topics['pauzy-tolerance'], $users['andrej'], 'Mně funguje zapisovat si frekvenci. Když mám tendenci zvyšovat množství třikrát za sebou, dávám pauzu minimálně týden.', 14, 23, ['🔥' => ['jakub'], '👍' => ['anna']]);
        $this->post($topics['pauzy-tolerance'], $users['anna'], 'Dobrý signál je i ztráta rozdílu mezi odrůdami. Jakmile všechno působí stejně, není to problém produktu, ale režimu.', 13, 29, ['🤔' => ['andrej'], '👍' => ['jakub', 'lucie']]);

        foreach ($topics as $topic) {
            $last = $topic->approvedPosts()->latest()->first();
            if ($last) {
                $topic->forceFill([
                    'last_post_at' => $last->created_at,
                    'last_post_user_id' => $last->user_id,
                ])->save();
            }
        }
    }

    private function post(ForumTopic $topic, User $user, string $body, int $daysAgo, int $score, array $reactions = [], ?ForumPost $parent = null): ForumPost
    {
        $createdAt = Carbon::now()->subDays($daysAgo)->subHours(random_int(1, 10));

        $post = ForumPost::create([
            'forum_topic_id' => $topic->id,
            'user_id' => $user->id,
            'parent_id' => $parent?->id,
            'body' => $body,
            'status' => 'approved',
            'score' => $score,
            'published_at' => $createdAt,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        foreach ($reactions as $emoji => $userKeys) {
            foreach ($userKeys as $key) {
                if (! isset($this->forumUsers[$key])) {
                    continue;
                }

                ForumPostReaction::create([
                    'forum_post_id' => $post->id,
                    'user_id' => $this->forumUsers[$key]->id,
                    'emoji' => $emoji,
                ]);
            }
        }

        $voters = collect($this->forumUsers)->take(min(max(1, $score), count($this->forumUsers)));
        foreach ($voters as $voter) {
            ForumPostVote::updateOrCreate(
                ['forum_post_id' => $post->id, 'user_id' => $voter->id],
                ['value' => 1],
            );
        }

        return $post;
    }

    private function downloadCover(string $url, string $slug): ?string
    {
        $path = 'forum/covers/' . $slug . '.jpg';

        if (Storage::disk('public')->exists($path)) {
            return $path;
        }

        try {
            $response = Http::withUserAgent('VivadzenForumSeeder/1.0')
                ->timeout(12)
                ->retry(1, 300)
                ->get($url);

            if (! $response->successful()) {
                return null;
            }

            $contentType = (string) $response->header('Content-Type');
            if (! str_contains($contentType, 'image')) {
                return null;
            }

            Storage::disk('public')->put($path, $response->body());

            return $path;
        } catch (\Throwable) {
            return null;
        }
    }
}
