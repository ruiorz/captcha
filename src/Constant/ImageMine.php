<?php

declare(strict_types=1);
/**
 * @author ruiorz(ruiorz@qq.com)
 * @link https://github.com/ruiorz
 */

namespace Ruiorz\Captcha\Constant;

class ImageMine
{
    public const PNG = 'image/png';

    public const JPEG = 'image/jpeg';

    public const GIF = 'image/gif';

    public const PNG_TYPE = IMAGETYPE_PNG;

    public const JPEG_TYPE = IMAGETYPE_JPEG;

    public const GIF_TYPE = IMAGETYPE_GIF;

    public static function getImageMine(int $type): string
    {
        return match ($type) {
            IMAGETYPE_GIF => 'image/gif',
            IMAGETYPE_PNG => 'image/png',
            default => 'image/jpeg',
        };
    }

    public static function getImageExtension(int $type): string
    {
        return match ($type) {
            IMAGETYPE_GIF => 'gif',
            IMAGETYPE_PNG => 'png',
            default => 'jpeg',
        };
    }
}
