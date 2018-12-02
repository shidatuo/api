<h1 align="center"> Laravel Emoji </h1>

<p align="center"> :smile: This package assist you in getting started with emoji easily.</p>

## Installing

```shell
$ composer require overtrue/laravel-emoji
```

> Laravel 5.5 auto-discovery supported.

If you are using < laravel 5.5

### Add service provider

```php
Overtrue\LaravelEmoji\EmojiServiceProvider::class,
```

### Add alias

```php
'Emoji' => Overtrue\LaravelEmoji\Emoji::class,
```

## Usage

```php
Emoji::toImage(':smile:'); // <img class="emojione" alt="&#x1f604;" title=":smile:" src="https://cdn.jsdelivr.net/emojione/assets/3.1/png/32/1f604.png"/>'
Emoji::toShort('😄'); // :smile:
Emoji::shortnameToUnicode(':smile:'); // 😄

// using helper
// default transform shorname to unicode, you can change it in config file.
emoji(':smile:'); // 😄

// access emoji services, return \Emojione\Client instance.
app('emoji');
// or 
app(\Emojione\Client::class);
```

### Configurations && emoji images

```shell
// config
$ php artisan vendor:publish --provider="Overtrue\\LaravelEmoji\\EmojiServiceProvider" --tag=config

// png images
$ php artisan vendor:publish --provider="Overtrue\\LaravelEmoji\\EmojiServiceProvider" --tag=public

// sprites images
$ php artisan vendor:publish --provider="Overtrue\\LaravelEmoji\\EmojiServiceProvider" --tag=sprites
```

## License

MIT
