<?php namespace CRSCompany\FrameworC\Classes;

use Log;
use Media\Classes\MediaLibrary;
use Request;
use Site;
use Tailor\Models\EntryRecord;
use Url;

/**
 * JsonLd builds the schema.org structured data for the current page from the
 * Meta single, the Footer single and the rendered record (Builder page or blog
 * post). Each returned string is the body of one ld+json script tag.
 */
class JsonLd
{
    /**
     * @var int flags keeps output readable while JSON_HEX_TAG escapes `<` and
     * `>`, so no value can close the surrounding script tag.
     */
    const FLAGS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG;

    /**
     * forPage returns the JSON documents for the page: the generated graph
     * first (unless the record opts out), then the site-wide and the record's
     * custom JSON-LD.
     */
    public static function forPage(?EntryRecord $meta, ?EntryRecord $record, bool $isBlogPost): array
    {
        if (!$meta || !$record) {
            return [];
        }

        $documents = [];

        if (!$record->jsonLdDisable) {
            $graph = [static::organization($meta), static::website($meta)];

            if ($isBlogPost) {
                $graph[] = static::blogPosting($meta, $record);
            } else {
                $graph[] = static::webPage($meta, $record);

                if ($breadcrumbs = static::breadcrumbs($record)) {
                    $graph[] = $breadcrumbs;
                }
            }

            $documents[] = json_encode(['@context' => 'https://schema.org', '@graph' => $graph], static::FLAGS);
        }

        foreach ([$meta->jsonLdCustom, $record->jsonLdCustom] as $custom) {
            if ($json = static::custom($custom)) {
                $documents[] = $json;
            }
        }

        return $documents;
    }

    protected static function organization(EntryRecord $meta): array
    {
        $footer = EntryRecord::inSection('Footer')->first();
        $type = $meta->orgType ?: 'Organization';
        $isBusiness = $type !== 'Organization';

        $address = static::filter([
            '@type' => 'PostalAddress',
            'streetAddress' => $meta->orgStreet,
            'addressLocality' => $meta->orgCity,
            'postalCode' => $meta->orgZip,
            'addressCountry' => $meta->orgCountry,
        ]);

        $sameAs = [];
        foreach ($footer->socials ?? [] as $social) {
            if (!empty($social->url)) {
                $sameAs[] = $social->url;
            }
        }

        $openingHours = [];
        if ($isBusiness) {
            foreach ($meta->orgOpeningHours ?? [] as $row) {
                $openingHours[] = static::filter([
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => array_values((array) $row->days),
                    'opens' => static::time($row->opens),
                    'closes' => static::time($row->closes),
                ]);
            }
        }

        $geo = $isBusiness && $meta->orgLat !== null && $meta->orgLat !== '' && $meta->orgLng !== null && $meta->orgLng !== ''
            ? ['@type' => 'GeoCoordinates', 'latitude' => (float) $meta->orgLat, 'longitude' => (float) $meta->orgLng]
            : null;

        $logo = static::mediaUrl($meta->orgLogo ?: ($footer->logo ?? null));

        return static::filter([
            '@type' => $type,
            '@id' => static::rootUrl() . '#organization',
            'name' => $meta->orgName ?: $meta->metaTitle,
            'legalName' => $meta->orgLegalName,
            'taxID' => $meta->orgIco,
            'url' => static::rootUrl(),
            'logo' => $logo,
            // LocalBusiness subtypes expect an image of the business itself
            'image' => $isBusiness ? $logo : null,
            'telephone' => $meta->orgPhone,
            'email' => $meta->orgEmail,
            'address' => count($address) > 1 ? $address : null,
            'geo' => $geo,
            'openingHoursSpecification' => $openingHours,
            'sameAs' => $sameAs,
        ]);
    }

    protected static function website(EntryRecord $meta): array
    {
        return static::filter([
            '@type' => 'WebSite',
            '@id' => static::rootUrl() . '#website',
            'name' => $meta->metaTitle,
            'url' => static::rootUrl(),
            'inLanguage' => static::locale(),
            'publisher' => ['@id' => static::rootUrl() . '#organization'],
        ]);
    }

    protected static function webPage(EntryRecord $meta, EntryRecord $record): array
    {
        $url = static::currentUrl();

        return static::filter([
            '@type' => $record->jsonLdPageType ?: 'WebPage',
            '@id' => $url . '#webpage',
            'url' => $url,
            'name' => $record->metaTitle ?: $record->title,
            'description' => $record->metaDescription ?: $meta->description,
            'inLanguage' => static::locale(),
            'isPartOf' => ['@id' => static::rootUrl() . '#website'],
            'primaryImageOfPage' => static::mediaUrl($record->ogImage),
            'dateModified' => $record->updated_at?->toAtomString(),
        ]);
    }

    protected static function blogPosting(EntryRecord $meta, EntryRecord $post): array
    {
        $url = static::currentUrl();
        $organization = ['@id' => static::rootUrl() . '#organization'];

        $author = $post->authorName
            ? static::filter(['@type' => 'Person', 'name' => $post->authorName, 'url' => $post->authorUrl])
            : $organization;

        return static::filter([
            '@type' => 'BlogPosting',
            '@id' => $url . '#article',
            'mainEntityOfPage' => $url,
            'headline' => $post->metaTitle ?: $post->title,
            'description' => $post->metaDescription ?: $post->perex,
            'image' => static::mediaUrl($post->image),
            'datePublished' => $post->published_at_date?->toAtomString(),
            'dateModified' => $post->updated_at?->toAtomString(),
            'inLanguage' => static::locale(),
            'author' => $author,
            'publisher' => $organization,
            'isPartOf' => ['@id' => static::rootUrl() . '#website'],
        ]);
    }

    /**
     * breadcrumbs walks the Builder structure up from the record. The parent
     * relation is followed rather than the nested-set bounds, so the trail
     * stays on the record's own site. Returns null on the home page.
     */
    protected static function breadcrumbs(EntryRecord $record): ?array
    {
        if ($record->fullslug === 'home') {
            return null;
        }

        $trail = [];
        $node = $record;

        while ($node && count($trail) < 20) {
            array_unshift($trail, $node);
            $node = $node->parent_id ? $node->parent : null;
        }

        $home = EntryRecord::inSection('Builder')->where('fullslug', 'home')->first();
        $items = [['name' => $home->title ?? 'Home', 'item' => static::rootUrl()]];

        foreach ($trail as $page) {
            if ($page->fullslug === 'home') {
                continue;
            }

            $items[] = ['name' => $page->title, 'item' => static::rootUrl() . ltrim($page->fullslug, '/')];
        }

        $list = [];
        foreach ($items as $index => $item) {
            $list[] = ['@type' => 'ListItem', 'position' => $index + 1] + $item;
        }

        return ['@type' => 'BreadcrumbList', 'itemListElement' => $list];
    }

    /**
     * custom re-encodes JSON entered in a codeeditor field. Invalid JSON is
     * logged and dropped so a typo never breaks the page markup.
     */
    protected static function custom($source): ?string
    {
        if (!is_string($source) || trim($source) === '') {
            return null;
        }

        $data = json_decode($source, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            Log::warning('FrameworC custom JSON-LD is not valid JSON: ' . json_last_error_msg());
            return null;
        }

        return json_encode($data, static::FLAGS);
    }

    /**
     * filter drops empty values so the output never carries blank properties.
     */
    protected static function filter(array $data): array
    {
        return array_filter($data, function ($value) {
            return $value !== null && $value !== '' && $value !== [];
        });
    }

    protected static function mediaUrl($path): ?string
    {
        if (empty($path)) {
            return null;
        }

        return Url::to(MediaLibrary::url($path));
    }

    /**
     * time trims a datepicker value to HH:MM.
     */
    protected static function time($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        return preg_match('/(\d{1,2}:\d{2})/', (string) $value, $match) ? $match[1] : null;
    }

    protected static function rootUrl(): string
    {
        $site = Site::getActiveSite();

        return rtrim($site?->base_url ?: Url::to('/'), '/') . '/';
    }

    /**
     * currentUrl is the request URL without the query string; the site root
     * keeps its trailing slash so it matches the @id used by the website node.
     */
    protected static function currentUrl(): string
    {
        $url = Request::url();

        return rtrim($url, '/') . '/' === static::rootUrl() ? static::rootUrl() : $url;
    }

    protected static function locale(): ?string
    {
        return Site::getActiveSite()?->hard_locale ?: app()->getLocale();
    }
}
