<?php

namespace App\Modules\Experience\Presenters;

/**
 * Artwork for a scene: an illustrated background for the pane and the assistant
 * cut-out standing in it.
 */
final class StageBackdrop
{
    /**
     * @return array{image: string, focus: string, focusMobile: string, character: string, avatarImage: string}
     */
    public static function for(string $scene): array
    {
        $scenes = config('business.scenes');
        $artwork = $scenes[config('business.scene_artwork')[$scene] ?? $scene] ?? $scenes['home'];

        return [
            'image' => asset($artwork['image']),
            'focus' => $artwork['focus'],
            'focusMobile' => $artwork['focusMobile'],
            'character' => asset($artwork['character']),
            'avatarImage' => self::avatar(),
        ];
    }

    public static function avatar(): string
    {
        return asset(config('business.avatar_image'));
    }
}
