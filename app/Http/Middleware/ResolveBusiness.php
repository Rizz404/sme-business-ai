<?php

namespace App\Http\Middleware;

use App\Models\Business;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the published business named by the {businessSlug} route segment and the
 * guest's language, so controllers never look either up themselves.
 *
 * The language is ?lang= on pages and "locale" in JSON bodies; an unsupported
 * value falls back to the business's default language.
 */
class ResolveBusiness
{
    private const BUSINESS = 'business';

    private const LOCALE = 'locale';

    public function handle(Request $request, Closure $next): Response
    {
        $business = Business::query()
            ->published()
            ->where('slug', $request->route('businessSlug'))
            ->firstOrFail();

        $requested = $request->query('lang', $request->input('locale'));

        $locale = in_array($requested, config('business.locales'), true) ? $requested : $business->default_locale;

        $request->attributes->set(self::BUSINESS, $business);
        $request->attributes->set(self::LOCALE, $locale);

        app()->setLocale($locale);

        return $next($request);
    }

    public static function business(Request $request): Business
    {
        return $request->attributes->get(self::BUSINESS);
    }

    public static function locale(Request $request): string
    {
        return $request->attributes->get(self::LOCALE);
    }
}
