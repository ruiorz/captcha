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

    public function __construct($captchaData, $imageByte)
    {
        $this->captchaData = $captchaData;
        $this->imageByte = $imageByte;
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
}
