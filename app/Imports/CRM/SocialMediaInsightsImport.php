<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Imports\CRM;

use App\Actions\CRM\SocialMedia\GetSocialMediaAccount;
use App\Enums\CRM\SocialMedia\SocialMediaPlatformEnum;
use App\Enums\CRM\SocialMedia\SocialMediaPostTypeEnum;
use App\Imports\WithImport;
use App\Models\Catalogue\Shop;
use App\Models\CRM\SocialMediaAccount;
use App\Models\CRM\SocialMediaAccountDailyStat;
use App\Models\CRM\SocialMediaPost;
use App\Models\Helpers\Upload;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Reads the exports of Meta Business Suite (Facebook and Instagram) and TikTok Studio, in either of
 * their two shapes: content exports, one row per post, and account exports, one row per day. Which
 * one a file is comes from its header row, wherever it sits: Meta puts a separator line and the
 * metric's name above the headers of a single-metric export.
 *
 * Both platforms write comma separated files, so the delimiter is set rather than guessed: a content
 * export's long, multi-line descriptions, links and times make the guess land on the colon, which
 * leaves every row as one cell and no header to find.
 */
class SocialMediaInsightsImport implements ToCollection, WithEvents, WithCustomCsvSettings
{
    use WithImport;

    /**
     * @var array<string, array<int, string>>
     */
    public const array POST_ALIASES = [
        'external_id'         => ['post_id', 'video_id', 'media_id'],
        'account_external_id' => ['page_id', 'account_id', 'ig_account_id'],
        'account_name'        => ['page_name', 'account_name'],
        'username'            => ['account_username', 'username'],
        'description'         => ['description', 'title', 'video_title', 'caption', 'message'],
        'permalink'           => ['permalink', 'post_link', 'video_link'],
        'published_at'        => ['publish_time', 'post_time', 'published_time', 'created_time', 'date_posted', 'posted_on'],
        'post_type'           => ['post_type', 'media_type', 'type'],
        'reach'               => ['reach', 'accounts_reached'],
        'impressions'         => ['impressions', 'views', 'video_views', 'total_views', 'plays'],
        'reactions'           => ['reactions', 'likes', 'total_likes'],
        'comments'            => ['comments', 'total_comments'],
        'shares'              => ['shares', 'total_shares'],
        'saves'               => ['saves', 'total_saves', 'add_to_favorites'],
        'link_clicks'         => ['link_clicks', 'total_clicks', 'clicks'],
        'follows'             => ['follows', 'new_followers'],
    ];

    /**
     * @var array<string, array<int, string>>
     */
    public const array DAY_ALIASES = [
        'followers'      => ['followers', 'total_followers', 'page_followers', 'follower_count', 'lifetime_followers'],
        'new_follows'    => ['follows', 'new_follows', 'new_followers', 'net_followers', 'difference_in_followers_from_previous_day', 'new_likes'],
        'reach'          => ['reach', 'page_reach', 'accounts_reached'],
        'impressions'    => ['impressions', 'views', 'video_views', 'page_views'],
        'engagements'    => ['engagements', 'interactions', 'content_interactions', 'engagement'],
        'profile_visits' => ['visits', 'profile_visits', 'profile_views', 'page_visits'],
        'link_clicks'    => ['link_clicks', 'external_link_taps', 'link_in_bio_taps', 'clicks'],
    ];

    private const array DATE_HEADERS = ['date', 'day', 'date_time'];

    /**
     * @var array<int, int>
     */
    private array $touchedAccountIds = [];

    public function __construct(public Shop $shop, Upload $upload)
    {
        $this->upload = $upload;
    }

    /**
     * @return array{delimiter: string}
     */
    public function getCsvSettings(): array
    {
        return ['delimiter' => ','];
    }

    public function collection(Collection $collection): void
    {
        $rows = $collection->map(fn (Collection $row) => $row->values()->all())->values();

        $headerIndex = $rows->search(fn (array $row) => $this->isHeaderRow($row));

        if ($headerIndex === false) {
            $this->upload->update(['number_rows' => 1]);
            $this->setRecordAsFailed(
                $this->createUploadRecord(collect($rows->first() ?? []), 1),
                [__('No header row found: expected the export of Meta Business Suite or TikTok Studio, with a Post ID or a Date column')]
            );

            return;
        }

        $headers  = array_map(fn ($header) => self::normaliseHeader($header), $rows[$headerIndex]);
        $title    = $headerIndex > 0 ? trim(implode(' ', array_filter(array_map('strval', $rows[$headerIndex - 1])))) : '';
        $dataRows = $rows->slice($headerIndex + 1)
            ->filter(fn (array $row) => array_filter($row, fn ($value) => $value !== null && trim((string) $value) !== '') !== []);

        $this->upload->update(['number_rows' => $dataRows->count()]);

        $postColumns = $this->columnsFor($headers, self::POST_ALIASES);
        $isPostFile  = isset($postColumns['external_id']) || isset($postColumns['permalink']);

        foreach ($dataRows as $index => $row) {
            $record = $this->createUploadRecord(collect(array_combine(
                array_map(fn ($header, $position) => $header !== '' ? $header : (string) $position, $headers, array_keys($headers)),
                array_pad(array_slice($row, 0, count($headers)), count($headers), null)
            )), $index + 1);

            try {
                if ($isPostFile) {
                    $this->storePost($headers, $row);
                } else {
                    $this->storeDay($headers, $row, $title);
                }
                $this->setRecordAsCompleted($record);
            } catch (Exception $e) {
                $this->setRecordAsFailed($record, [$e->getMessage()]);
            }
        }

        $this->refreshFollowers();
    }

    public static function normaliseHeader(mixed $header): string
    {
        return trim((string) preg_replace('/[^a-z0-9]+/', '_', strtolower(trim((string) $header))), '_');
    }

    private function isHeaderRow(array $row): bool
    {
        $headers = array_map(fn ($header) => self::normaliseHeader($header), $row);

        return array_intersect($headers, array_merge(self::POST_ALIASES['external_id'], self::POST_ALIASES['permalink'], self::DATE_HEADERS)) !== [];
    }

    /**
     * @param array<int, string>                $headers
     * @param array<string, array<int, string>> $aliases
     *
     * @return array<string, int>
     */
    private function columnsFor(array $headers, array $aliases): array
    {
        $columns = [];

        foreach ($aliases as $field => $fieldAliases) {
            foreach ($fieldAliases as $alias) {
                $position = array_search($alias, $headers, true);
                if ($position !== false && !in_array($position, $columns, true)) {
                    $columns[$field] = $position;
                    break;
                }
            }
        }

        return $columns;
    }

    /**
     * @param array<int, string> $headers
     */
    private function storePost(array $headers, array $row): void
    {
        $columns = $this->columnsFor($headers, self::POST_ALIASES);
        $value   = fn (string $field) => isset($columns[$field]) ? $this->text($row[$columns[$field]] ?? null) : null;

        $permalink = $value('permalink');
        $platform  = SocialMediaPlatformEnum::fromText($permalink)
            ?? match (true) {
                isset($columns['username'])                             => SocialMediaPlatformEnum::INSTAGRAM,
                SocialMediaPlatformEnum::fromText($value('post_type')) !== null => SocialMediaPlatformEnum::fromText($value('post_type')),
                in_array('page_id', $headers, true)                    => SocialMediaPlatformEnum::FACEBOOK,
                default                                                 => $this->platformFromFilename(),
            };

        if ($platform === null) {
            throw new Exception(__('Could not tell whether this post is Facebook, Instagram or TikTok'));
        }

        $externalId = $value('external_id') ?? $permalink;
        if ($externalId === null) {
            throw new Exception(__('Post has no Post ID or link'));
        }

        $publishedAt = $this->date($value('published_at'));
        if ($publishedAt === null) {
            throw new Exception(__('Post has no publish time'));
        }

        $account = $this->account($platform, $value('account_external_id'), $value('account_name'), $value('username'));

        SocialMediaPost::updateOrCreate(
            [
                'social_media_account_id' => $account->id,
                'external_id'             => $externalId,
            ],
            [
                'type'         => SocialMediaPostTypeEnum::fromExport(str_contains((string) $permalink, '/reel/') ? 'reel' : $value('post_type'), $platform),
                'description'  => $value('description'),
                'permalink'    => $permalink,
                'published_at' => $publishedAt,
                'reach'        => $this->number($value('reach')) ?? 0,
                'impressions'  => $this->number($value('impressions')) ?? 0,
                'reactions'    => $this->number($value('reactions')) ?? 0,
                'comments'     => $this->number($value('comments')) ?? 0,
                'shares'       => $this->number($value('shares')) ?? 0,
                'saves'        => $this->number($value('saves')) ?? 0,
                'link_clicks'  => $this->number($value('link_clicks')) ?? 0,
                'follows'      => $this->number($value('follows')) ?? 0,
            ]
        );
    }

    /**
     * A day row may carry several platforms at once ("Facebook reach", "Instagram reach"), so every
     * metric column is read as a platform and a field, and each platform's figures go to its account.
     *
     * @param array<int, string> $headers
     */
    private function storeDay(array $headers, array $row, string $title): void
    {
        $dateColumn = $this->columnsFor($headers, ['date' => self::DATE_HEADERS])['date'] ?? null;
        $date       = $dateColumn !== null ? $this->date($this->text($row[$dateColumn] ?? null)) : null;

        if ($date === null) {
            throw new Exception(__('Row has no date'));
        }

        $figures = [];

        foreach ($headers as $position => $header) {
            if ($position === $dateColumn || $header === '') {
                continue;
            }

            $label = $header === 'primary' ? self::normaliseHeader($title) : $header;
            [$platform, $field] = $this->platformAndField($label);

            if ($field === null) {
                continue;
            }

            $platform ??= SocialMediaPlatformEnum::fromText($title) ?? $this->platformFromFilename() ?? $this->onlyPlatform();

            if ($platform === null) {
                throw new Exception(__('Could not tell whether these figures are Facebook, Instagram or TikTok: put the platform in the file name'));
            }

            $number = $this->number($this->text($row[$position] ?? null));
            if ($number !== null) {
                $figures[$platform->value][$field] = $number;
            }
        }

        if ($figures === []) {
            throw new Exception(__('Row has no figures we recognise'));
        }

        foreach ($figures as $platform => $values) {
            $account = $this->account(SocialMediaPlatformEnum::from($platform), null, null, null);

            SocialMediaAccountDailyStat::updateOrCreate(
                [
                    'social_media_account_id' => $account->id,
                    'date'                    => $date->toDateString(),
                ],
                $values
            );
        }
    }

    /**
     * @return array{0: SocialMediaPlatformEnum|null, 1: string|null}
     */
    private function platformAndField(string $label): array
    {
        $platform = SocialMediaPlatformEnum::fromText(str_replace('_', ' ', $label));
        $metric   = (string) preg_replace('/^(facebook_page_|facebook_|fb_|instagram_|ig_|tiktok_)/', '', $label);

        foreach (self::DAY_ALIASES as $field => $aliases) {
            if (in_array($metric, $aliases, true)) {
                return [$platform, $field];
            }
        }

        return [$platform, null];
    }

    private function account(SocialMediaPlatformEnum $platform, ?string $externalId, ?string $name, ?string $username): SocialMediaAccount
    {
        $account = GetSocialMediaAccount::run($this->shop, $platform, $externalId, $name, $username);

        $this->touchedAccountIds[$account->id] = $account->id;

        return $account;
    }

    private function refreshFollowers(): void
    {
        foreach (SocialMediaAccount::whereIn('id', $this->touchedAccountIds)->get() as $account) {
            $latest = $account->dailyStats()->whereNotNull('followers')->orderByDesc('date')->first();

            if ($latest && ($account->followers_at === null || $latest->date->gte($account->followers_at))) {
                $account->update(['followers' => $latest->followers, 'followers_at' => $latest->date]);
            }
        }
    }

    private function platformFromFilename(): ?SocialMediaPlatformEnum
    {
        return SocialMediaPlatformEnum::fromText(str_replace(['_', '-', '.'], ' ', (string) $this->upload->original_filename));
    }

    private function onlyPlatform(): ?SocialMediaPlatformEnum
    {
        $platforms = $this->shop->socialMediaAccounts()->distinct()->pluck('platform');

        return $platforms->count() === 1 ? $platforms->first() : null;
    }

    private function text(mixed $value): ?string
    {
        $value = is_scalar($value) ? trim((string) $value) : null;

        return $value === '' ? null : $value;
    }

    private function number(?string $value): ?int
    {
        if ($value === null) {
            return null;
        }

        $value = str_replace([',', ' ', "\u{00A0}"], '', $value);

        return is_numeric($value) ? (int) round((float) $value) : null;
    }

    private function date(?string $value): ?Carbon
    {
        if ($value === null) {
            return null;
        }

        try {
            return is_numeric($value)
                ? Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))
                : Carbon::parse($value);
        } catch (Exception) {
            return null;
        }
    }
}
