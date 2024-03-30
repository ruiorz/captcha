<?php

declare(strict_types=1);
/**
 * @author ruiorz(ruiorz@qq.com)
 * @link https://github.com/ruiorz
 */

namespace Ruiorz\Captcha\Click;

use Ruiorz\Captcha\Constant\ImageMine;
use Ruiorz\Captcha\Interface\Captcha;
use Ruiorz\Captcha\Interface\CaptchaConfig;

class ClickCaptcha implements Captcha
{
    private CaptchaConfig $config;

    public function __construct(CaptchaConfig $config = null)
    {
        if ($config === null) {
            $config = new ClickCaptchaConfig();
        }
        $this->config = $config;
    }

    public function draw(): ClickCaptchaResult
    {
        $packagePath = dirname(__FILE__);
        $imagePathArr = [
            $packagePath . '/images/1.jpg',
            $packagePath . '/images/2.jpg',
            $packagePath . '/images/3.jpg',
        ];
        # 随机一张底图
        $imagePath = $imagePathArr[rand(0, count($imagePathArr) - 1)];
        if ($this->config->getImagePath()) {
            $imagePath = $this->config->getImagePath();
        }
        # 字体路径
        $fontPath = $packagePath . '/fonts/SourceHanSansCN-Normal.ttf';
        if ($this->config->getFontPath()) {
            $fontPath = $this->config->getFontPath();
        }
        $texts = [];
        $textLength = $this->config->getTextLength();
        foreach ($this->randChars($textLength) as $v) {
            $fontSize = rand(15, 30);
            // 字符串文本框宽度和长度
            $fontArea = imagettfbbox($fontSize, 0, $fontPath, $v);
            $textWidth = $fontArea[2] - $fontArea[0];
            $textHeight = $fontArea[1] - $fontArea[7];
            $tmp['text'] = $v;
            $tmp['size'] = $fontSize;
            $tmp['width'] = $textWidth;
            $tmp['height'] = $textHeight;
            $texts[] = $tmp;
        }
        // 获取图片宽高和类型
        [$imageWidth, $imageHeight, $imageType] = getimagesize($imagePath);
        // 随机生成汉字位置
        foreach ($texts as &$v) {
            [$x, $y] = $this->randPosition($texts, $imageWidth, $imageHeight, $v['width'], $v['height']);
            $v['x'] = $x;
            $v['y'] = $y;
        }
        unset($v);
        // 创建图片的实例
        $image = imagecreatefromstring(file_get_contents($imagePath));
        foreach ($texts as $v) {
            [$r, $g, $b] = $this->getImageColor($imagePath, intval($v['x'] + $v['width'] / 2), intval($v['y'] - $v['height'] / 2));
            // 字体颜色
            $color = imagecolorallocate($image, $r, $g, $b);
            // 阴影字体颜色
            $r = $r > 127 ? 0 : 255;
            $g = $g > 127 ? 0 : 255;
            $b = $b > 127 ? 0 : 255;
            $shadowColor = imagecolorallocate($image, $r, $g, $b);
            // 文字随机旋转角度
            $randAngle = mt_rand(-45, 45);
            // 绘画阴影
            imagettftext($image, $v['size'], $randAngle, $v['x'] + 1, $v['y'], $shadowColor, $fontPath, $v['text']);
            imagettftext($image, $v['size'], $randAngle, $v['x'], $v['y'] + 1, $shadowColor, $fontPath, $v['text']);
            // 绘画文字
            imagettftext($image, $v['size'], $randAngle, $v['x'], $v['y'], $color, $fontPath, $v['text']);
            // 计算旋转后的“精确”边界框
            $borderBox = $this->calculateTextBox($v['size'], $randAngle, $fontPath, $v['text']);
            // 计算字体左下角的x,y,宽高
            $v['x'] = $v['x'] - $borderBox['left'];
            $v['y'] = $v['y'] - $borderBox['top'] + $borderBox['height'];
            $v['width'] = $borderBox['width'];
            $v['height'] = $borderBox['height'];
        }
        // 删除汉字数组后面4个，实现图片上展示8个字，实际只需点击4个的效果
        $verifyLength = $this->config->getVerifyLength();
        $texts = array_splice($texts, 3, $verifyLength);
        // 生成图片
        ob_start();
        $func = 'image' . ImageMine::getImageExtension($imageType);
        $func($image);
        $imageByte = ob_get_contents();
        ob_end_clean();
        imagedestroy($image);
        // 组装结果数据
        $captchaData = [
            'texts' => $texts,
            'mime' => ImageMine::getImageMine($imageType),
            'type' => ImageMine::getImageExtension($imageType),
            'width' => $imageWidth,
            'height' => $imageHeight,
        ];
        return new ClickCaptchaResult($captchaData, $imageByte);
    }

    public function verify(array $verifyData, array $captchaData): bool
    {
        // 判断顺序
        for ($i = 0; $i < count($verifyData); ++$i) {
            if ($verifyData[$i]['text'] !== $captchaData['texts'][$i]['text']) {
                return false;
            }
        }
        // 判断坐标是否在范围内
        for ($i = 0; $i < count($verifyData); ++$i) {
            if ($verifyData[$i]['x'] < $captchaData['texts'][$i]['x'] || $verifyData[$i]['x'] > $captchaData['texts'][$i]['x'] + $captchaData['texts'][$i]['width']) {
                return false;
            }
            if ($verifyData[$i]['y'] < $captchaData['texts'][$i]['y'] - $captchaData['texts'][$i]['height'] || $verifyData[$i]['y'] > $captchaData['texts'][$i]['y']) {
                return false;
            }
        }
        // 验证通过
        return true;
    }

    // 随机生成中文汉字
    private function randChars($length = 4): array
    {
        $chars = '们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然如应形想制心样干都向变关问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处己将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书术状厂须离再目海交权且儿青才证低越际八试规斯近注办布门铁需走议县兵固除般引齿千胜细影济白格效置推空配刀叶率述今选养德话查差半敌始片施响收华觉备名红续均药标记难存测士身紧液派准斤角降维板许破述技消底床田势端感往神便贺村构照容非搞亚磨族火段算适讲按值美态黄易彪服早班麦削信排台声该击素张密害侯草何树肥继右属市严径螺检左页抗苏显苦英快称坏移约巴材省黑武培著河帝仅针怎植京助升王眼她抓含苗副杂普谈围食射源例致酸旧却充足短划剂宣环落首尺波承粉践府鱼随考刻靠够满夫失包住促枝局菌杆周护岩师举曲春元超负砂封换太模贫减阳扬江析亩木言球朝医校古呢稻宋听唯输滑站另卫字鼓刚写刘微略范供阿块某功套友限项余倒卷创律雨让骨远帮初皮播优占死毒圈伟季训控激找叫云互跟裂粮粒母练塞钢顶策双留误础吸阻故寸盾晚丝女散焊功株亲院冷彻弹错散商视艺灭版烈零室轻血倍缺厘泵察绝富城冲喷壤简否柱李望盘磁雄似困巩益洲脱投送奴侧润盖挥距触星松送获兴独官混纪依未突架宽冬章湿偏纹吃执阀矿寨责熟稳夺硬价努翻奇甲预职评读背协损棉侵灰虽矛厚罗泥辟告卵箱掌氧恩爱停曾溶营终纲孟钱待尽俄缩沙退陈讨奋械载胞幼哪剥迫旋征槽倒握担仍呀鲜吧卡粗介钻逐弱脚怕盐末阴丰雾冠丙街莱贝辐肠付吉渗瑞惊顿挤秒悬姆烂森糖圣凹陶词迟蚕亿矩康遵牧遭幅园腔订香肉弟屋敏恢忘编印蜂急拿扩伤飞露核缘游振操央伍域甚迅辉异序免纸夜乡久隶缸夹念兰映沟乙吗儒杀汽磷艰晶插埃燃欢铁补咱芽永瓦倾阵碳演威附牙芽永瓦斜灌欧献顺猪洋腐请透司危括脉宜笑若尾束壮暴企菜穗楚汉愈绿拖牛份染既秋遍锻玉夏疗尖殖井费州访吹荣铜沿替滚客召旱悟刺脑措贯藏敢令隙炉壳硫煤迎铸粘探临薄旬善福纵择礼愿伏残雷延烟句纯渐耕跑泽慢栽鲁赤繁境潮横掉锥希池败船假亮谓托伙哲怀割摆贡呈劲财仪沉炼麻罪祖息车穿货销齐鼠抽画饲龙库守筑房歌寒喜哥洗蚀废纳腹乎录镜妇恶脂庄擦险赞钟摇典柄辩竹谷卖乱虚桥奥伯赶垂途额壁网截野遗静谋弄挂课镇妄盛耐援扎虑键归符庆聚绕摩忙舞遇索顾胶羊湖钉仁音迹碎伸灯避泛亡答勇频皇柳哈揭甘诺概宪浓岛袭谁洪谢炮浇斑讯懂灵蛋闭孩释乳巨徒私银伊景坦累匀霉杜乐勒隔弯绩招绍胡呼痛峰零柴簧午跳居尚丁秦稍追梁折耗碱殊岗挖氏刃剧堆赫荷胸衡勤膜篇登驻案刊秧缓凸役剪川雪链渔啦脸户洛孢勃盟买杨宗焦赛旗滤硅炭股坐蒸凝竟陷枪黎救冒暗洞犯筒您宋弧爆谬涂味津臂障褐陆啊健尊豆拔莫抵桑坡缝警挑污冰柬嘴啥饭塑寄赵喊垫丹渡耳刨虎笔稀昆浪萨茶滴浅拥穴覆伦娘吨浸袖珠雌妈紫戏塔锤震岁貌洁剖牢锋疑霸闪埔猛诉刷狠忽灾闹乔唐漏闻沈熔氯荒茎男凡抢像浆旁玻亦忠唱蒙予纷捕锁尤乘乌智淡允叛畜俘摸锈扫毕璃宝芯爷鉴秘净蒋钙肩腾枯抛轨堂拌爸循诱祝励肯酒绳穷塘燥泡袋朗喂铝软渠颗惯贸粪综墙趋彼届墨碍启逆卸航衣孙龄岭骗休借';
        $res = [];
        for ($i = 0; $i < $length; ++$i) {
            $res[] = $this->msubstr($chars, (int)floor(mt_rand(0, mb_strlen($chars, 'utf-8') - 1)), false);
        }
        return $res;
    }

    /**
     * 字符串截取，支持中文和其他编码
     * @static
     * @param string $str 需要转换的字符串
     * @param int $start 开始位置
     * @param bool|string $suffix 截断显示字符
     * @return string
     */
    private function msubstr(string $str, int $start, bool|string $suffix = true): string
    {
        $slice = mb_substr($str, $start, 1, 'utf-8');
        return $suffix ? $slice . '...' : $slice;
    }

    // 随机生成位置布局
    private function randPosition($texts, $imgW, $imgH, $fontW, $fontH)
    {
        $x = rand(0, $imgW - $fontW);
        $y = rand($fontH, $imgH);
        // 碰撞验证
        if (!$this->checkPosition($texts, $x, $y, $fontW, $fontH)) {
            $res = $this->randPosition($texts, $imgW, $imgH, $fontW, $fontH);
        } else {
            $res = [$x, $y];
        }
        return $res;
    }

    private function checkPosition($texts, $x, $y, $w, $h): bool
    {
        $flag = true;
        foreach ($texts as $v) {
            if (isset($v['x'], $v['y'])) {
                // 分别判断X和Y是否都有交集，如果都有交集，则判断为覆盖
                $flagX = true;
                if ($v['x'] > $x) {
                    if ($x + $w > $v['x']) {
                        $flagX = false;
                    }
                } elseif ($x > $v['x']) {
                    if ($v['x'] + $v['width'] > $x) {
                        $flagX = false;
                    }
                } else {
                    $flagX = false;
                }
                $flagY = true;
                if ($v['y'] > $y) {
                    if ($y + $h > $v['y']) {
                        $flagY = false;
                    }
                } elseif ($y > $v['y']) {
                    if ($v['y'] + $v['height'] > $y) {
                        $flagY = false;
                    }
                } else {
                    $flagY = false;
                }
                if (!$flagX && !$flagY) {
                    $flag = false;
                }
            }
        }
        return $flag;
    }

    // 获取图片某个定点上的主要色
    private function getImageColor($img, $x, $y): array
    {
        $imageType = getimagesize($img)[2];
        $func = 'imagecreatefrom' . ImageMine::getImageExtension($imageType);
        $im = $func($img);
        $rgb = imagecolorat($im, $x, $y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        return [$r, $g, $b];
    }

    // 计算旋转后的“精确”边界框
    private function calculateTextBox($font_size, $font_angle, $font_file, $text) {
        $box = imagettfbbox($font_size, $font_angle, $font_file, $text);
        if(!$box) {
            return false;
        }

        $min_x = min( array($box[0], $box[2], $box[4], $box[6]) );
        $max_x = max( array($box[0], $box[2], $box[4], $box[6]) );
        $min_y = min( array($box[1], $box[3], $box[5], $box[7]) );
        $max_y = max( array($box[1], $box[3], $box[5], $box[7]) );
        $width  = ($max_x - $min_x);
        $height = ($max_y - $min_y);
        $left   = abs($min_x) + $width;
        $top    = abs($min_y) + $height;
        // to calculate the exact bounding box i write the text in a large image
        $img     = @imagecreatetruecolor( $width << 2, $height << 2 );
        $white   =  imagecolorallocate( $img, 255, 255, 255 );
        $black   =  imagecolorallocate( $img, 0, 0, 0 );
        imagefilledrectangle($img, 0, 0, imagesx($img), imagesy($img), $black);
        // for sure the text is completely in the image!
        imagettftext( $img, $font_size,
            $font_angle, $left, $top,
            $white, $font_file, $text);
        // start scanning (0=> black => empty)
        $rleft  = $w4 = $width<<2;
        $rright = 0;
        $rbottom   = 0;
        $rtop = $h4 = $height<<2;
        for( $x = 0; $x < $w4; $x++ ) {
            for( $y = 0; $y < $h4; $y++ ) {
                if(imagecolorat( $img, $x, $y )){
                    $rleft   = min( $rleft, $x );
                    $rright  = max( $rright, $x );
                    $rtop    = min( $rtop, $y );
                    $rbottom = max( $rbottom, $y );
                }
            }
        }

        // destroy img and serve the result
        imagedestroy( $img );
        return [
            "left"   => $left - $rleft,
            "top"    => $top  - $rtop,
            "width"  => $rright - $rleft + 1,
            "height" => $rbottom - $rtop + 1
        ];
    }
}
