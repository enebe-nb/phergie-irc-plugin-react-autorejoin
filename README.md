# enebe-nb/phergie-irc-plugin-react-autorejoin

[Phergie](http://github.com/phergie/phergie-irc-bot-react/) plugin for automatically rejoining IRC channels on PART or KICK event.

[![Build Status](https://travis-ci.org/enebe-nb/phergie-irc-plugin-react-autorejoin.svg?branch=master)](https://travis-ci.org/enebe-nb/phergie-irc-plugin-react-autorejoin)

## Install

The recommended method of installation is [through composer](http://getcomposer.org).

```JSON
{
    "require": {
        "enebe-nb/phergie-irc-plugin-react-autorejoin": "^1.0"
    }
}
```

See [Phergie documentation](https://github.com/phergie/phergie-irc-bot-react/wiki/Usage#plugins) for more information on installing plugins.

## Configuration

```php
return array(
    'plugins' => array(
        new \EnebeNb\Phergie\Plugin\AutoRejoin\Plugin(array(

            // Required: list of channels to rejoin
            'channels' => array('#channel1', '#channel2', '#channelN'),
            // or
            'channels' => '#channel1,#channel2,#channelN',

            // Optional: channel keys
            'keys' => array('key1', 'key2', 'keyN'),
            // or
            'keys' => 'key1,key2,keyN',

        )),
    ),
);
```

## Tests

To run the unit test suite:

```
curl -s https://getcomposer.org/installer | php
php composer.phar install
./vendor/bin/phpunit
```

## License

Released under the BSD License. See `LICENSE`.
