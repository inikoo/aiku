<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

/** @noinspection PhpUnhandledExceptionInspection */

use App\Actions\CRM\SocialMedia\GetSocialMediaPerformance;
use App\Actions\CRM\SocialMedia\ImportSocialMediaInsights;
use App\Enums\CRM\SocialMedia\SocialMediaPlatformEnum;
use App\Enums\CRM\SocialMedia\SocialMediaPostTypeEnum;
use App\Enums\Helpers\Import\UploadRecordStatusEnum;
use App\Models\CRM\SocialMediaAccount;
use App\Models\CRM\SocialMediaPost;
use App\Models\Helpers\Upload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;

beforeAll(function () {
    loadDB();
});

beforeEach(function () {
    [$this->organisation, $this->user, $this->shop] = createOwnShop('social media');

    $this->shop->socialMediaAccounts()->each(fn (SocialMediaAccount $account) => $account->delete());

    Config::set('inertia.testing.page_paths', [resource_path('js/Pages/Grp')]);
    actingAs($this->user);
});

function socialCsv(string $name, array $lines): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, implode("\n", $lines)."\n");
}

function instagramContentExport(int $reelReach = 1000): UploadedFile
{
    return socialCsv('instagram-content.csv', [
        'Post ID,Account ID,Account username,Account name,Description,Duration (sec),Publish time,Permalink,Post type,Data comment,Date,Views,Reach,Likes,Shares,Follows,Comments,Saves',
        '"1801",17841400001,awgifts.eu,"AW Gifts Europe","Halloween range reveal",30,08/10/2026 10:00,https://www.instagram.com/reel/AAA/,IG reel,,Lifetime,5000,'.$reelReach.',80,10,4,6,4',
        '"1802",17841400001,awgifts.eu,"AW Gifts Europe","Brand spotlight",0,08/12/2026 09:30,https://www.instagram.com/p/BBB/,IG carousel,,Lifetime,"1,200",800,20,2,0,3,15',
        '"1803",17841400001,awgifts.eu,"AW Gifts Europe","Last month",0,07/20/2026 09:30,https://www.instagram.com/p/CCC/,IG image,,Lifetime,400,300,10,0,0,1,1',
    ]);
}

it('imports an instagram content export as one account and its posts', function () {
    $upload = ImportSocialMediaInsights::make()->handle($this->shop, instagramContentExport());

    expect($upload->number_rows)->toBe(3)
        ->and($upload->number_success)->toBe(3)
        ->and($upload->number_fails)->toBe(0);

    $account = $this->shop->socialMediaAccounts()->sole();

    expect($account->platform)->toBe(SocialMediaPlatformEnum::INSTAGRAM)
        ->and($account->external_id)->toBe('17841400001')
        ->and($account->name)->toBe('AW Gifts Europe')
        ->and($account->username)->toBe('awgifts.eu');

    $reel = $account->posts()->where('external_id', '1801')->sole();

    expect($reel->type)->toBe(SocialMediaPostTypeEnum::REEL)
        ->and($reel->reach)->toBe(1000)
        ->and($reel->impressions)->toBe(5000)
        ->and($reel->reactions)->toBe(80)
        ->and($reel->saves)->toBe(4)
        ->and($reel->follows)->toBe(4)
        ->and($reel->published_at->toDateString())->toBe('2026-08-10')
        ->and($account->posts()->where('external_id', '1802')->value('impressions'))->toBe(1200);
});

it('corrects a post when a later export is imported, never adding it twice', function () {
    ImportSocialMediaInsights::make()->handle($this->shop, instagramContentExport());
    ImportSocialMediaInsights::make()->handle($this->shop, instagramContentExport(reelReach: 2500));

    $account = $this->shop->socialMediaAccounts()->sole();

    expect($account->posts()->count())->toBe(3)
        ->and($account->posts()->where('external_id', '1801')->value('reach'))->toBe(2500);
});

it('reads a single metric meta export into the account days and its follower count', function () {
    ImportSocialMediaInsights::make()->handle($this->shop, instagramContentExport());

    $upload = ImportSocialMediaInsights::make()->handle($this->shop, socialCsv('followers.csv', [
        'sep=,',
        '"Instagram followers"',
        '"Date","Primary"',
        '"2026-07-31T00:00:00","1000"',
        '"2026-08-31T00:00:00","1150"',
    ]));

    expect($upload->number_success)->toBe(2);

    $account = $this->shop->socialMediaAccounts()->sole()->refresh();

    expect($account->dailyStats()->count())->toBe(2)
        ->and($account->followers)->toBe(1150)
        ->and($account->followers_at->toDateString())->toBe('2026-08-31');
});

it('splits a daily export carrying several platforms between their accounts', function () {
    ImportSocialMediaInsights::make()->handle($this->shop, socialCsv('overview.csv', [
        'Date,Facebook Page reach,Facebook Page new follows,Instagram reach,Instagram followers',
        '2026-08-01,500,3,900,2000',
    ]));

    $accounts = $this->shop->socialMediaAccounts()->get()->keyBy(fn (SocialMediaAccount $account) => $account->platform->value);

    expect($accounts)->toHaveCount(2)
        ->and($accounts['facebook']->dailyStats()->sole()->reach)->toBe(500)
        ->and($accounts['facebook']->dailyStats()->sole()->new_follows)->toBe(3)
        ->and($accounts['instagram']->dailyStats()->sole()->reach)->toBe(900)
        ->and($accounts['instagram']->refresh()->followers)->toBe(2000);
});

it('imports a tiktok studio content export without an account id', function () {
    ImportSocialMediaInsights::make()->handle($this->shop, socialCsv('tiktok.csv', [
        'Video title,Video link,Post time,Total likes,Total comments,Total shares,Total views',
        'Unboxing,https://www.tiktok.com/@awgifts/video/7400000000000000001,2026-08-05 18:00,120,8,15,9000',
    ]));

    $post = SocialMediaPost::where('permalink', 'like', '%7400000000000000001')->sole();

    expect($post->socialMediaAccount->platform)->toBe(SocialMediaPlatformEnum::TIKTOK)
        ->and($post->type)->toBe(SocialMediaPostTypeEnum::VIDEO)
        ->and($post->impressions)->toBe(9000)
        ->and($post->reactions)->toBe(120);
});

it('explains the rows it cannot import', function () {
    $upload = ImportSocialMediaInsights::make()->handle($this->shop, socialCsv('instagram-content.csv', [
        'Post ID,Account ID,Account username,Permalink,Post type,Publish time,Reach',
        '1901,17841400001,awgifts.eu,https://www.instagram.com/p/DDD/,IG image,,100',
        '1902,17841400001,awgifts.eu,https://www.instagram.com/p/EEE/,IG image,08/03/2026 08:00,100',
    ]));

    expect($upload->number_success)->toBe(1)
        ->and($upload->number_fails)->toBe(1)
        ->and($upload->records()->where('status', UploadRecordStatusEnum::FAILED)->sole()->errors)->toBe(['Post has no publish time']);
});

it('rejects a file that is not a social media export', function () {
    $upload = ImportSocialMediaInsights::make()->handle($this->shop, socialCsv('prospects.csv', [
        'Name,Email',
        'Ann,ann@example.com',
    ]));

    expect($upload->number_fails)->toBe(1)
        ->and($this->shop->socialMediaAccounts()->count())->toBe(0);
});

it('reports a period with its previous one, formats, platforms and top posts', function () {
    ImportSocialMediaInsights::make()->handle($this->shop, instagramContentExport());

    $performance = GetSocialMediaPerformance::run(
        $this->organisation,
        Carbon::parse('2026-08-01')->startOfDay(),
        Carbon::parse('2026-08-31')->endOfDay(),
        ['shop' => $this->shop->slug]
    );

    $current  = $performance['kpis']['current'];
    $previous = $performance['kpis']['previous'];

    expect($current['posts'])->toBe(2)
        ->and($current['reach'])->toBe(1800)
        ->and($current['engagements'])->toBe(100 + 40)
        ->and($current['engagement_rate'])->toBe(round(140 / 1800 * 100, 2))
        ->and($previous['posts'])->toBe(1)
        ->and($previous['reach'])->toBe(300)
        ->and($performance['previous_from'])->toBe('2026-07-01')
        ->and($performance['top_posts'][0]['description'])->toBe('Halloween range reveal')
        ->and(collect($performance['formats'])->pluck('type')->all())->toEqualCanonicalizing(['reel', 'carousel'])
        ->and($performance['platforms'][0]['platform'])->toBe('instagram')
        ->and($performance['platforms'][0]['share'])->toBe(100.0)
        ->and($performance['accounts'][0]['posts'])->toBe(2)
        ->and($performance['trend']['bucket'])->toBe('day')
        ->and($performance['trend']['points'])->toHaveCount(31);
});

it('filters by platform and leaves out shops outside the filter', function () {
    ImportSocialMediaInsights::make()->handle($this->shop, instagramContentExport());

    $from = Carbon::parse('2026-08-01')->startOfDay();
    $to   = Carbon::parse('2026-08-31')->endOfDay();

    expect(GetSocialMediaPerformance::run($this->organisation, $from, $to, ['shop' => $this->shop->slug, 'platform' => 'facebook'])['kpis']['current']['posts'])->toBe(0)
        ->and(GetSocialMediaPerformance::run($this->organisation, $from, $to, ['shop' => 'no-such-shop'])['kpis']['current']['posts'])->toBe(0);
});

it('uploads an export through the shop route', function () {
    $this->post(route('grp.models.shop.social_media.upload', ['shop' => $this->shop->id]), [
        'file' => instagramContentExport(),
    ])->assertCreated();

    expect(Upload::where('parent_type', 'Shop')->where('parent_id', $this->shop->id)->where('model', 'SocialMediaPost')->exists())->toBeTrue()
        ->and($this->shop->socialMediaAccounts()->count())->toBe(1);
});

it('shows the social media dashboard to marketing users', function () {
    $this->get(route('grp.org.marketing.social_media.dashboard', ['organisation' => $this->organisation->slug]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Org/Marketing/SocialMediaDashboard')
            ->where('title', 'Social media')
            ->has('upload_shops')
            ->has('export_route'));

    $this->get(route('grp.marketing.social_media.dashboard'))->assertOk();
});

it('exports the posts of the period as csv', function () {
    ImportSocialMediaInsights::make()->handle($this->shop, instagramContentExport());
    $this->user->update(['settings' => array_merge($this->user->settings ?? [], ['selected_interval' => 'all'])]);

    $csv = $this->get(route('grp.org.marketing.social_media.export', [
        'organisation' => $this->organisation->slug,
        'shop'         => $this->shop->slug,
    ]))->assertOk()->streamedContent();

    expect($csv)->toContain('Halloween range reveal')
        ->and($csv)->toContain('Instagram');
});
