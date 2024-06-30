<?php

declare(strict_types=1);
/**
 * @author ruiorz(ruiorz@qq.com)
 * @link https://github.com/ruiorz
 */

namespace Ruiorz\Captcha\Click;

use Ruiorz\Captcha\Exception\ResourceNotFoundException;
use Ruiorz\Captcha\Interface\CaptchaConfig;

class ClickCaptchaConfig implements CaptchaConfig
{
    // 字体路径
    private ?string $fontPath = null;

    // 底图路径
    private ?string $imagePath = null;

    // 文本数量
    private int $textLength = 8;

    // 验证文字数量
    private int $verifyLength = 4;

    // 点击验证码一个文字宽度 px
    private int $clickTextWidth = 40;

    // 点击验证码背景高度 px
    private int $clickTextHeight = 40;

    /**
     * @return int
     */
    public function getTextLength(): int
    {
        return $this->textLength;
    }

    /**
     * @param int $textLength
     */
    public function setTextLength(int $textLength): void
    {
        if ($textLength < 4) {
            $textLength = 4;
        }
        if ($textLength > 8) {
            $textLength = 8;
        }
        $this->textLength = $textLength;
    }

    /**
     * @return int
     */
    public function getVerifyLength(): int
    {
        return $this->verifyLength;
    }

    /**
     * @param int $verifyLength
     */
    public function setVerifyLength(int $verifyLength): void
    {
        if ($verifyLength > $this->textLength) {
            $verifyLength = $this->textLength;
        }
        $this->verifyLength = $verifyLength;
    }

    /**
     * @param string $fontPath
     */
    public function setFontPath(string $fontPath): void
    {
        if (!file_exists($fontPath)) {
            throw new ResourceNotFoundException("Font file not found: {$fontPath}");
        }
        $this->fontPath = $fontPath;
    }

    /**
     * @param string $imagePath
     */
    public function setImagePath(string $imagePath): void
    {
        if (!file_exists($imagePath)) {
            throw new ResourceNotFoundException("Image file not found: {$imagePath}");
        }
        $this->imagePath = $imagePath;
    }

    /**
     * @return ?string
     */
    public function getFontPath(): ?string
    {
        return $this->fontPath;
    }

    /**
     * @return ?string
     */
    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }


    /**
     * 获取需要点击文字的宽度
     * @return int
     */
    public function getClickTextWidth(): int
    {
        return $this->clickTextWidth;
    }

    /**
     * 设置点击文字的宽度
     * @param int $clickTextWidth
     * @return void
     */
    public function setClickTextWidth(int $clickTextWidth): void
    {
        $this->clickTextWidth = $clickTextWidth;
    }


    /**
     * 获取需要点击文字背景的高度
     * @return int
     */
    public function getClickTextHeight(): int
    {
        return $this->clickTextHeight;
    }

    /**
     * 设置点击文字背景的高度
     * @param int $clickTextHeight
     * @return void
     */
    public function setClickTextHeight(int $clickTextHeight): void
    {
        $this->clickTextHeight = $clickTextHeight;
    }
}
