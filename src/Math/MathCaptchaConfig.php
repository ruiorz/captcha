<?php

declare(strict_types=1);
/**
 * @author ruiorz(ruiorz@qq.com)
 * @link https://github.com/ruiorz
 */

namespace Ruiorz\Captcha\Math;

use Ruiorz\Captcha\Exception\ResourceNotFoundException;
use Ruiorz\Captcha\Interface\CaptchaConfig;

class MathCaptchaConfig implements CaptchaConfig
{
    private int $width = 150;

    private int $height = 40;

    private int $noiseLevel = 4; // 干扰等级（1-10）

    private ?string $fontPath = null; // 字体路径

    private int $fontSize = 18; // 字体大小

    private bool $isDrawLine = true; // 是否启用干扰线

    private bool $isDrawCurve = true; // 是否启用曲线

    /**
     * @return null|string
     */
    public function getFontPath(): ?string
    {
        return $this->fontPath;
    }

    /**
     * @param null|string $fontPath
     */
    public function setFontPath(?string $fontPath): void
    {
        if (!file_exists($fontPath)) {
            throw new ResourceNotFoundException("Font file not found: {$fontPath}");
        }
        $this->fontPath = $fontPath;
    }

    /**
     * @return int
     */
    public function getFontSize(): int
    {
        return $this->fontSize;
    }

    /**
     * @param int $fontSize
     */
    public function setFontSize(int $fontSize): void
    {
        $this->fontSize = $fontSize;
    }

    /**
     * @return bool
     */
    public function isDrawLine(): bool
    {
        return $this->isDrawLine;
    }

    /**
     * @param bool $isDrawLine
     */
    public function setIsDrawLine(bool $isDrawLine): void
    {
        $this->isDrawLine = $isDrawLine;
    }

    /**
     * @return bool
     */
    public function isDrawCurve(): bool
    {
        return $this->isDrawCurve;
    }

    /**
     * @param bool $isDrawCurve
     */
    public function setIsDrawCurve(bool $isDrawCurve): void
    {
        $this->isDrawCurve = $isDrawCurve;
    } // 字体路径

    /**
     * @return int
     */
    public function getNoiseLevel(): int
    {
        return $this->noiseLevel;
    }

    /**
     * @param int $noiseLevel
     */
    public function setNoiseLevel(int $noiseLevel): void
    {
        if ($noiseLevel > 10) {
            $noiseLevel = 10;
        }
        if ($noiseLevel < 0) {
            $noiseLevel = 0;
        }
        $this->noiseLevel = $noiseLevel;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @param int $width
     */
    public function setWidth(int $width): void
    {
        $this->width = $width;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @param int $height
     */
    public function setHeight(int $height): void
    {
        $this->height = $height;
    }
}
