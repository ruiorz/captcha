<?php

declare(strict_types=1);
/**
 * @author ruiorz(ruiorz@qq.com)
 * @link https://github.com/ruiorz
 */

namespace Ruiorz\Captcha;

abstract class CaptchaResult
{
    private string $imageByte;

    private array $captchaData;

    private string $textsImageByte;

    public function __construct($captchaData, $imageByte, $textsImageByte = '')
    {
        $this->captchaData = $captchaData;
        $this->imageByte = $imageByte;
        $this->textsImageByte = $textsImageByte;
    }

    public function getCaptchaData(): array
    {
        return $this->captchaData;
    }

    public function getImageByte(): string
    {
        return $this->imageByte;
    }

    public function getImageBase64(): string
    {
        $base64Data = base64_encode($this->imageByte);
        $mime = $this->captchaData['mime'];
        return "data:{$mime};base64,{$base64Data}";
    }

    /**
     * 返回需要连续点击文字的byte
     * @return string
     */
    public function getTextsImageByte(): string
    {
        return $this->textsImageByte;
    }

    /**
     * 返回需要连续点击文字的base64
     * @return string
     */
    public function getTextsImageBase64(): string
    {
        $base64Data = base64_encode($this->textsImageByte);
        $mime = 'image/png';
        return "data:{$mime};base64,{$base64Data}";
    }
}
