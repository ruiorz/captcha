<?php

declare(strict_types=1);
/**
 * @author ruiorz(ruiorz@qq.com)
 * @link https://github.com/ruiorz
 */

namespace Ruiorz\Captcha\Math;

use GdImage;
use Ruiorz\Captcha\CaptchaResult;
use Ruiorz\Captcha\Constant\ImageMine;
use Ruiorz\Captcha\Interface\Captcha;
use Ruiorz\Captcha\Interface\CaptchaConfig;

class MathCaptcha implements Captcha
{
    private CaptchaConfig $config;

    public function __construct(CaptchaConfig $config = null)
    {
        if ($config === null) {
            $config = new MathCaptchaConfig();
        }
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function draw(): CaptchaResult
    {
        $packagePath = dirname(__FILE__);
        # 字体路径
        $fontPath = $packagePath . '/fonts/actionj.ttf';
        if ($this->config->getFontPath()) {
            $fontPath = $this->config->getFontPath();
        }
        // 创建底图
        $width = $this->config->getWidth();
        $height = $this->config->getHeight();
        $image = imagecreate($width, $height);
        // 创建浅色背景
        [$r, $g, $b] = $this->getRandLightColor();
        $bgColor = imagecolorallocate($image, $r, $g, $b);
        imagefill($image, 0, 0, $bgColor);

        // 绘杂点
        $codeStr = '2345678abcdefhijkmnpqrstuvwxyz';
        $noiseLevel = $this->config->getNoiseLevel();
        for ($i = 0; $i < $noiseLevel; ++$i) {
            [$r, $g, $b] = $this->getRandLightColor();
            // 杂点颜色
            $noiseColor = imagecolorallocate($image, $r, $g, $b);
            for ($j = 0; $j < 5; ++$j) {
                imagestring($image, 5, mt_rand(-10, $width), mt_rand(-10, $height), $codeStr[mt_rand(0, 29)], $noiseColor);
            }
        }
        // 绘干扰线
        if ($this->config->isDrawLine()) {
            for ($i = 0; $i < intval($noiseLevel / 2); ++$i) {
                $this->drawLine($image, $width, $height);
            }
        }
        // 绘曲线
        if ($this->config->isDrawCurve()) {
            for ($i = 0; $i < intval($noiseLevel / 2); ++$i) {
                $this->drawSineLine($image, $width, $height);
            }
        }
        // 定义一个数组存放运算符号
        $bcArr = ['+', '-', 'x'];
        // 计算数组的长度
        $len = count($bcArr);
        // 定义一个1到20的数组
        $num = range(1, 20);
        $numLen = count($num);
        // 定义一个空数组来存放随机取得的验证码
        $code = [];
        for ($i = 0; $i < $len; ++$i) {
            if ($i == 1) {
                $code[] = $bcArr[mt_rand(0, $len - 1)];
            } else {
                $code[] = $num[mt_rand(0, $numLen - 1)];
            }
        }
        $code[] = '=';
        $code[] = '?';

        // 绘验证码
        $codeNX = 10; // 验证码第N个字符的左边距
        for ($i = 0; $i < count($code); ++$i) {
            [$r, $g, $b] = $this->getRandDeepColor();
            $color = imagecolorallocate($image, $r, $g, $b);
            $angle = mt_rand(-25, 25);
            if (in_array($code[$i], $bcArr) || in_array($code[$i], ['=', '?'])) {
                $angle = 0;
            }
            imagettftext($image, $this->config->getFontSize(), $angle, $codeNX, intval($this->config->getFontSize() * 1.5), $color, $fontPath, (string)$code[$i]);
            $codeNX += mt_rand($this->config->getFontSize() * 1, intval($this->config->getFontSize() * 2));
        }

        // 生成图片
        ob_start();
        imagepng($image);
        $imageByte = ob_get_contents();
        ob_end_clean();
        imagedestroy($image);
        // 组装结果数据
        $captchaData = [
            'result' => $this->getRes($code),
            'mime' => ImageMine::PNG,
            'type' => ImageMine::getImageExtension(ImageMine::PNG_TYPE),
            'width' => $width,
            'height' => $height,
        ];
        return new MathCaptchaResult($captchaData, $imageByte);
    }

    private function getRes($arr)
    {
        $res = 0;
        $arr = str_replace(['=', '?'], '', $arr);
        // 判断数组元素下标为1的运算符号是什么
        switch ($arr[1]) {
            case '+':
                $res = $arr[0] + $arr[2];
                break;
            case '-':
                $res = $arr[0] - $arr[2];
                break;
            case 'x':
                $res = $arr[0] * $arr[2];
                break;
        }
        return $res;
    }

    /**
     *  画曲线
     * @param mixed $image
     * @param mixed $width
     * @param mixed $height
     */
    private function drawSineLine(GdImage $image, int $width, int $height)
    {
        $py = 0;
        // 曲线前部分
        $A = mt_rand(1, $height / 2); // 振幅
        $b = mt_rand(-$height / 4, $height / 4); // Y轴方向偏移量
        $f = mt_rand(-$height / 4, $height / 4); // X轴方向偏移量
        $T = mt_rand($height, $width * 2); // 周期
        $w = (2 * M_PI) / $T;

        $px1 = 0; // 曲线横坐标起始位置
        $px2 = mt_rand($width / 2, intval($width * 0.8)); // 曲线横坐标结束位置
        $color = imagecolorallocate($image, mt_rand(1, 150), mt_rand(1, 150), mt_rand(1, 150));
        for ($px = $px1; $px <= $px2; ++$px) {
            if ($w != 0) {
                $py = intval($A * sin($w * $px + $f) + $b + $height / 2); // y = Asin(ωx+φ) + b
                $i = (int)($this->config->getFontSize() / 10);
                while ($i > 0) {
                    imagesetpixel($image, $px + $i, $py + $i, $color); // 这里(while)循环画像素点比imagettftext和imagestring用字体大小一次画出（不用这while循环）性能要好很多
                    --$i;
                }
            }
        }
        // 曲线后部分
        $A = mt_rand(1, $height / 2); // 振幅
        $f = mt_rand(-$height / 4, $height / 4); // X轴方向偏移量
        $T = mt_rand($height, $width * 2); // 周期
        $w = (2 * M_PI) / $T;
        $b = $py - $A * sin($w * $px + $f) - $height / 2;
        $px1 = $px2;
        $px2 = $width;
        for ($px = $px1; $px <= $px2; ++$px) {
            if ($w != 0) {
                $py = $A * sin($w * $px + $f) + $b + $height / 2; // y = Asin(ωx+φ) + b
                $i = (int)($this->config->getFontSize() / 5);
                while ($i > 0) {
                    imagesetpixel($image, $px + $i, (int)$py + $i, $color);
                    --$i;
                }
            }
        }
    }

    /**
     * Draw lines over the image.
     * @param mixed $image
     * @param mixed $width
     * @param mixed $height
     */
    private function drawLine(GdImage $image, int $width, int $height)
    {
        $t = imagecolorallocate($image, mt_rand(100, 255), mt_rand(100, 255), mt_rand(100, 255));
        if (mt_rand(0, 1)) { // Horizontal
            $Xa = mt_rand(0, $width / 2);
            $Ya = mt_rand(0, $height);
            $Xb = mt_rand($width / 2, $width);
            $Yb = mt_rand(0, $height);
        } else { // Vertical
            $Xa = mt_rand(0, $width);
            $Ya = mt_rand(0, $height / 2);
            $Xb = mt_rand(0, $width);
            $Yb = mt_rand($height / 2, $height);
        }
        imagesetthickness($image, mt_rand(1, 3));
        imageline($image, $Xa, $Ya, $Xb, $Yb, $t);
    }

    /**
     * 获取随机浅色.
     * @return array
     */
    private function getRandLightColor(): array
    {
        return [
            200 + mt_rand(1, 55),
            200 + mt_rand(1, 55),
            200 + mt_rand(1, 55),
        ];
    }

    /**
     * 获取随机深色.
     * @return array
     */
    private function getRandDeepColor(): array
    {
        return [
            mt_rand(1, 50),
            mt_rand(1, 50),
            mt_rand(1, 50),
        ];
    }
}
