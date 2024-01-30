<?php

declare(strict_types=1);
/**
 * @author ruiorz(ruiorz@qq.com)
 * @link https://github.com/ruiorz
 */

namespace Ruiorz\Captcha\Interface;

use Ruiorz\Captcha\CaptchaResult;

interface Captcha
{
    public function __construct(CaptchaConfig $config = null);

    /**
     * 画图、生成图片.
     * @return CaptchaResult
     */
    public function draw(): CaptchaResult;
}
