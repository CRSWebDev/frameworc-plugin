<?php namespace CRSCompany\FrameworC\Classes;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Tailor\Models\EntryRecord;
use Exception;

/**
 * GoogleReviews downloads reviews from the Google Places API (New) into
 * GooglePlace Tailor entries.
 *
 * Google returns at most five reviews per request, so reviews are merged by
 * their Google resource name instead of replaced: the stored list grows over
 * time, and the editor's "hidden" flag survives every sync.
 *
 * @link https://developers.google.com/maps/documentation/places/web-service/place-details
 */
class GoogleReviews
{
    const SECTION = 'GooglePlace';

    const ENDPOINT = 'https://places.googleapis.com/v1/places/';

    const FIELD_MASK = 'displayName,rating,userRatingCount,googleMapsUri,reviews';

    /**
     * syncAll refreshes every GooglePlace entry.
     *
     * @return array<string, string|null> error message (or null on success), keyed by entry title
     */
    public static function syncAll(): array
    {
        $results = [];

        foreach (EntryRecord::inSection(static::SECTION)->get() as $place) {
            $results[$place->title ?: $place->placeId] = static::sync($place);
        }

        return $results;
    }

    /**
     * sync refreshes one GooglePlace entry and returns an error message, or null on success.
     * The error is also stored on the entry so editors can see it in the backend.
     */
    public static function sync(EntryRecord $place): ?string
    {
        try {
            $data = static::fetch((string) $place->placeId, (string) ($place->languageCode ?: 'cs'));
        }
        catch (Exception $e) {
            $place->syncError = $e->getMessage();
            $place->save();
            return $e->getMessage();
        }

        $place->rating = $data['rating'] ?? null;
        $place->userRatingCount = $data['userRatingCount'] ?? null;
        $place->googleMapsUri = $data['googleMapsUri'] ?? null;
        $place->syncedAt = Carbon::now();
        $place->syncError = null;
        $place->save();

        static::mergeReviews($place, (array) ($data['reviews'] ?? []));

        return null;
    }

    /**
     * fetch calls the Place Details endpoint.
     */
    protected static function fetch(string $placeId, string $languageCode): array
    {
        $apiKey = SettingsHelper::getByPrefix('integration_')['google_places_api_key'] ?? '';

        if (!$apiKey) {
            throw new Exception('Chybí Google Places API klíč (Nastavení Fwc → Google recenze).');
        }

        if (!$placeId) {
            throw new Exception('Chybí Google Place ID.');
        }

        $response = Http::withHeaders([
                'X-Goog-Api-Key' => $apiKey,
                'X-Goog-FieldMask' => static::FIELD_MASK,
            ])
            ->timeout(15)
            ->get(static::ENDPOINT . rawurlencode($placeId), ['languageCode' => $languageCode]);

        if (!$response->successful()) {
            $message = $response->json('error.message') ?: $response->body();
            throw new Exception("Google Places API {$response->status()}: {$message}");
        }

        return (array) $response->json();
    }

    /**
     * mergeReviews updates known reviews and appends new ones.
     */
    protected static function mergeReviews(EntryRecord $place, array $reviews): void
    {
        $existing = [];
        foreach ($place->reviews as $item) {
            if ($item->reviewName) {
                $existing[$item->reviewName] = $item;
            }
        }

        $sort = $place->reviews->max('sort_order') ?: 0;

        foreach ($reviews as $review) {
            $name = $review['name'] ?? null;
            if (!$name) {
                continue;
            }

            $values = [
                'reviewName' => $name,
                'authorName' => $review['authorAttribution']['displayName'] ?? '',
                'authorUri' => $review['authorAttribution']['uri'] ?? '',
                'authorPhotoUri' => $review['authorAttribution']['photoUri'] ?? '',
                'rating' => (int) ($review['rating'] ?? 0),
                'text' => $review['text']['text'] ?? $review['originalText']['text'] ?? '',
                'publishTime' => isset($review['publishTime']) ? Carbon::parse($review['publishTime']) : null,
            ];

            if (isset($existing[$name])) {
                $existing[$name]->fill($values);
                $existing[$name]->save();
                continue;
            }

            $item = $place->makeRelation('reviews');
            $item->extendWithBlueprint();
            $item->fill($values);
            $item->sort_order = ++$sort;
            $place->reviews()->add($item);
        }
    }
}
