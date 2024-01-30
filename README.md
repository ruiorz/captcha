# Captcha library for PHP

A simple captcha library for PHP

## Installation

Require this package with composer:

```bash
composer require ruiorz/captcha
```

## Usage
```php
# click captcha:
$captcha = new \Ruiorz\Captcha\Click\ClickCaptcha();
$result = $captcha->draw();

# or math captcha:
$captcha = new \Ruiorz\Captcha\Math\MathCaptcha();
$result = $captcha->draw();

# result:
print_r($result->getCaptchaData())
print_r($result->getImageByte())
print_r($result->getImageBase64())
```

## Configuration
```php
# click captcha:
$config = new \Ruiorz\Captcha\Click\ClickCaptchaConfig();
$config->setFontPath('src/Click/fonts/msyh.ttc');
$config->setImagePath('src/Click/images/3.jpg');
$config->setVerifyLength(3);
$result = (new \Ruiorz\Captcha\Click\ClickCaptcha($config))->draw();
print_r($result->getCaptchaData());

# or math captcha:
$config = new \Ruiorz\Captcha\Math\MathCaptchaConfig();
$config->setFontPath('src/Math/fonts/Bitsumishi.ttf');
$result = (new \Ruiorz\Captcha\Math\MathCaptcha($config))->draw();
print_r($result->getCaptchaData());
```

## Contributing

Pull requests are welcome. For major changes, please open an issue first
to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License

[MIT](https://choosealicense.com/licenses/mit/)