<?php

declare(strict_types=1);
/**
 * @author ruiorz(ruiorz@qq.com)
 * @link https://github.com/ruiorz
 */

namespace Case;

use PHPUnit\Framework\TestCase;
use Ruiorz\Captcha\Click\ClickCaptcha;
use Ruiorz\Captcha\Click\ClickCaptchaConfig;
use Ruiorz\Captcha\Exception\ResourceNotFoundException;
use Ruiorz\Captcha\Interface\CaptchaConfig;

/**
 * @internal
 */
class ClickCaptchaTest extends TestCase
{
    public function testConfig(): void
    {
        $config = new ClickCaptchaConfig();
        $this->assertInstanceOf(CaptchaConfig::class, $config);
        $config->setTextLength(3);
        $this->assertEquals(4, $config->getTextLength());
        $config->setTextLength(100);
        $this->assertEquals(8, $config->getTextLength());
        $config->setVerifyLength(3);
        $this->assertEquals(3, $config->getVerifyLength());
        $config->setVerifyLength(100);
        $this->assertEquals($config->getTextLength(), $config->getVerifyLength());
        try {
            $config->setFontPath('not-exist.ttl');
        } catch (ResourceNotFoundException $e) {
            $this->assertSame('Font file not found: not-exist.ttl', $e->getMessage());
        }
        $this->assertSame($config->getFontPath(), null);
        $config->setFontPath('src/Click/fonts/msyh.ttc');
        try {
            $config->setImagePath('not-exist.jpg');
        } catch (ResourceNotFoundException $e) {
            $this->assertSame('Image file not found: not-exist.jpg', $e->getMessage());
        }
        $this->assertSame($config->getImagePath(), null);
        $config->setImagePath('src/Click/images/3.jpg');
    }

    public function testDraw(): void
    {
        $captcha = new ClickCaptcha();
        for ($i = 0; $i < 20; ++$i) {
            $result = $captcha->draw();
            $this->assertTrue(strlen($result->getImageBase64()) > 0);
            $this->assertTrue(strlen($result->getImageByte()) > 0);
            $this->assertCount(4, $result->getCaptchaData()['texts']);
        }
    }

    public function testDrawWithConfig(): void
    {
        $config = new ClickCaptchaConfig();
        $config->setFontPath('src/Click/fonts/msyh.ttc');
        $config->setImagePath('src/Click/images/3.jpg');
        $config->setVerifyLength(3);
        $result = (new ClickCaptcha($config))->draw();
        $this->assertCount(3, $result->getCaptchaData()['texts']);
    }

    public function testVerify(): void
    {
        $captcha = new ClickCaptcha();
        $result = $captcha->draw();
        $captchaData = $result->getCaptchaData();
        $verifyData = [
            [
                'text' => '中',
                'x' => 13,
                'y' => 20,
            ],
            [
                'text' => '文',
                'x' => 20,
                'y' => 20,
            ],
            [
                'text' => '汉',
                'x' => 20,
                'y' => 30,
            ],
            [
                'text' => '字',
                'x' => 13,
                'y' => 30,
            ],
        ];
        $this->assertFalse($captcha->verify($verifyData, $captchaData));
        for ($i = 0; $i < 20; ++$i) {
            $result = $captcha->draw();
            $captchaData = $result->getCaptchaData();
            $verifyData = [];
            $verifyDataFalse = [];
            for ($j = 0; $j < count($captchaData['texts']); ++$j) {
                $verifyData[] = [
                    'text' => $captchaData['texts'][$j]['text'],
                    'x' => $captchaData['texts'][$j]['x'] + mt_rand(1, $captchaData['texts'][$j]['width'] - 1),
                    'y' => $captchaData['texts'][$j]['y'] + mt_rand(1, $captchaData['texts'][$j]['height'] - 1),
                ];
                $verifyDataFalse[] = [
                    'text' => $captchaData['texts'][$j]['text'],
                    'x' => mt_rand(0, $captchaData['width']),
                    'y' => mt_rand(0, $captchaData['height']),
                ];
            }
            $this->assertTrue($captcha->verify($verifyData, $captchaData));
            $this->assertFalse($captcha->verify($verifyDataFalse, $captchaData));
        }
    }
}
