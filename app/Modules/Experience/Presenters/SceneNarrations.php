<?php

namespace App\Modules\Experience\Presenters;

use App\Models\Business;
use App\Models\BusinessKnowledgeItem;
use App\Models\CatalogItem;
use App\Models\ServiceArea;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * What the AI Business Assistant says when the guest steps into a scene. Every sentence
 * is built from stored business data, never generated.
 */
final class SceneNarrations
{
    /**
     * @param  Collection<int, CatalogItem>  $items
     * @return array{catalog: ?string, item: array<string, string>}
     */
    public static function catalog(Business $business, Collection $items, string $locale): array
    {
        $templates = StageCopy::narration($locale);
        $terms = StageCopy::terms($locale);
        $money = self::money($business);
        $sentence = self::sentence($locale);
        $separator = $locale === 'ja' ? '、' : ', ';

        if ($items->isEmpty()) {
            return ['catalog' => null, 'item' => []];
        }

        $intro = str_replace(
            [':count', ':price'],
            [(string) $items->count(), $money($items->min('price'))],
            $templates[$items->count() === 1 ? 'catalog_one' : 'catalog']
        );

        $narrations = $items->mapWithKeys(function (CatalogItem $item) use ($templates, $terms, $money, $sentence, $separator, $locale) {
            $parts = [$item->translatedName($locale).'.', filled($item->translatedDescription($locale)) ? $sentence(trim($item->translatedDescription($locale))) : null];

            if ($item->delivery) {
                $parts[] = str_replace(':delivery', $terms['delivery'][$item->delivery] ?? Str::headline($item->delivery), $templates['item_delivery']);
            }

            if (! empty($item->requirements)) {
                $list = collect($item->requirements)->map(fn ($requirement) => $terms['requirement'][$requirement] ?? $requirement)->implode($separator);
                $parts[] = str_replace(':list', $list, $templates['item_requirements']);
            }

            $parts[] = str_replace(':price', $money($item->price), $templates['item_price']);

            if ($item->is_unavailable) {
                $parts[] = $templates['item_unavailable'];
            }

            return [$item->slug => implode(' ', array_filter($parts))];
        })->all();

        return ['catalog' => $intro, 'item' => $narrations];
    }

    /**
     * @param  Collection<int, ServiceArea>  $areas
     * @return array{service: ?string, area: array<string, string>}
     */
    public static function service(Business $business, Collection $areas, string $locale): array
    {
        $templates = StageCopy::narration($locale);
        $money = self::money($business);
        $sentence = self::sentence($locale);

        if ($areas->isEmpty()) {
            return ['service' => null, 'area' => []];
        }

        $first = $areas->first()->translatedName($locale);
        $intro = str_replace(
            [':count', ':first'],
            [(string) $areas->count(), $first],
            $templates[$areas->count() === 1 ? 'service_one' : 'service']
        );

        $narrations = $areas->mapWithKeys(function (ServiceArea $area) use ($templates, $money, $sentence, $locale) {
            $parts = [$area->translatedName($locale).'.', filled($area->translatedDescription($locale)) ? $sentence(trim($area->translatedDescription($locale))) : null];

            $parts[] = $area->min_attendees > 1
                ? str_replace([':min', ':max'], [(string) $area->min_attendees, (string) $area->max_attendees], $templates['area_attendees'])
                : str_replace(':max', (string) $area->max_attendees, $templates['area_attendees_one']);

            $parts[] = (float) $area->booking_fee > 0
                ? str_replace(':fee', $money($area->booking_fee), $templates['area_fee'])
                : $templates['area_free'];

            if ($area->minimum_order !== null && (float) $area->minimum_order > 0) {
                $parts[] = str_replace(':spend', $money($area->minimum_order), $templates['area_spend']);
            }

            return [$area->slug => implode(' ', array_filter($parts))];
        })->all();

        return ['service' => $intro, 'area' => $narrations];
    }

    /**
     * Intros for the facilities list and the business information scene, built
     * only from stored business data.
     *
     * @param  Collection<int, BusinessKnowledgeItem>  $facilities
     * @return array{facilities: ?string, info: string}
     */
    public static function scenes(Business $business, Collection $facilities, string $locale): array
    {
        $templates = StageCopy::narration($locale);
        $separator = $locale === 'ja' ? '、' : '; ';
        $time = fn (?string $value) => $value ? substr($value, 0, 5) : null;

        $intro = null;

        if ($facilities->isNotEmpty()) {
            $examples = $facilities->take(3)->map(fn ($item) => $item->translatedTitle($locale))->implode($separator);
            $intro = str_replace(
                [':count', ':examples'],
                [(string) $facilities->count(), $examples],
                $templates[$facilities->count() === 1 ? 'facilities_one' : 'facilities']
            );
        }

        $location = collect([$business->city, $business->country])->filter()->implode($locale === 'ja' ? '、' : ', ');
        $parts = [str_replace(':business', $business->name, $templates['info_welcome'])];

        if ($location !== '') {
            $parts[] = str_replace(':location', $location, $templates['info_place']);
        }

        if ($time($business->opening_time) && $time($business->closing_time)) {
            $parts[] = str_replace([':open', ':close'], [$time($business->opening_time), $time($business->closing_time)], $templates['info_hours']);
        }

        $parts[] = $templates['info_more'];

        return ['facilities' => $intro, 'info' => implode(' ', $parts)];
    }

    /**
     * @return \Closure(mixed): string
     */
    private static function money(Business $business): \Closure
    {
        return fn ($value) => $business->currency.' '.number_format((float) $value, 0, ',', '.');
    }

    /**
     * @return \Closure(string): string
     */
    private static function sentence(string $locale): \Closure
    {
        return fn (string $text) => preg_match('/[.!?。！？]$/u', $text) ? $text : $text.($locale === 'ja' ? '。' : '.');
    }
}
