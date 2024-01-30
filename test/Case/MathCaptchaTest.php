<?php

declare(strict_types=1);
/**
 * @author ruiorz(ruiorz@qq.com)
 * @link https://github.com/ruiorz
 */

namespace Case;

use PHPUnit\Framework\TestCase;
use Ruiorz\Captcha\Exception\ResourceNotFoundException;
use Ruiorz\Captcha\Math\MathCaptcha;
use Ruiorz\Captcha\Math\MathCaptchaConfig;

/**
 * @internal
 */
class MathCaptchaTest extends TestCase
{
    public function testConfig(): void
    {
        $config = new MathCaptchaConfig();
        $config->setHeight(200);
        $this->assertEquals(200, $config->getHeight());
        $config->setHeight(100);
        $this->assertEquals(100, $config->getHeight());
        $config->setWidth(200);
        $this->assertEquals(200, $config->getWidth());
        $config->setIsDrawLine(true);
        $this->assertTrue($config->isDrawLine());
        $config->setIsDrawCurve(true);
        $this->assertTrue($config->isDrawCurve());
        $config->setNoiseLevel(100);
        $this->assertEquals(10, $config->getNoiseLevel());
        $config->setNoiseLevel(-11);
        $this->assertEquals(0, $config->getNoiseLevel());
        $config->setFontSize(20);
        $this->assertEquals(20, $config->getFontSize());
        try {
            $config->setFontPath('not-exist.ttl');
        } catch (ResourceNotFoundException $e) {
            $this->assertSame('Font file not found: not-exist.ttl', $e->getMessage());
        }
        $this->assertSame($config->getFontPath(), null);
        $config->setFontPath('src/Math/fonts/actionj.ttf');
        $this->assertTrue(true);
    }

    public function testDraw(): void
    {
        $captcha = new MathCaptcha();
        for ($i = 0; $i < 20; ++$i) {
            $result = $captcha->draw();
            $this->assertTrue(strlen($result->getImageBase64()) > 0);
            $this->assertTrue(strlen($result->getImageByte()) > 0);
        }
    }

    public function testDrawWithConfig()
    {
        $config = new MathCaptchaConfig();
        $config->setFontPath('src/Math/fonts/Bitsumishi.ttf');
        $result = (new MathCaptcha($config))->draw();
        $this->assertIsNumeric($result->getCaptchaData()['result']);
    }
}
