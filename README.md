Behapi
======
Behat extension to help write describe features related to HTTP APIs.

PHP 7.1 and Behat 3.3, and a discoverable php-http client are required to make
this extension work.

Installing this extension is pretty easy, and there are multiple ways to do
that ; but the one exposed here is the best (this is pretty subjective), which
is via Composer. You just need to require `taluu/behapi` and an implementation
of a http/client (providing `php-http/client-implementation ^1.0`,
`php-http/message-factory-implementation ^1.0` and
`psr/http-message-implementation ^1.0`), and it's done !

Howto
-----
Add this in your behat.yml (it's for the `default` configuration but you can
use it for any configurations actually) :

```yaml
default:
  extensions:
    Behapi\Behapi:
      base_url: 'http://localhost'
```

The `base_url` is the only requirement in the config for this extension to work.

There are other configurations keys, such as which formatter to use in a debug
environment, which headers you want to output in request or response while
debugging, and the possibility to use and configure some twig options **if you
have twig installed** ; Use the `--config-reference` flag when invoking behat
to have more information on the available configuration.

After having installed the extension, you can then use the provided contexts
such as the `Behapi\Context\Http` for the http api operations. In order to use
them, you need to use behapi's container (`@behapi.container`, see example in
the `behat.yml.dist` file), or a container capable of using behapi's container.

Some services are provided to be injected in contexts, which are the following:

- `@Http\Client\HttpClient`, which is the http client
- `@Behapi\Tools\HttpHistory`, which is a sort of a container with the last
  requests done and last responses received
- `@Http\Message\MessageFactory`, which is the message factory (see psr 7)
- `@Http\Message\StreamFactory`, which is the stream factory (see psr 7)
- `@Twig_Environment`, which is the `Twig_Environment`, if twig is installed
  (`null` otherwise)

*Note:* If you are using Behat 3.4 (you should !), you don't really need to
bother with the services names, as they are compatible with behat 3.4's
auto-wiring feature. :}

In order to use (and customize) the `Json` context, you actually need to either
extend `Behapi\Context\AbstractJson` or `Behapi\Context\Json`. If you want to
use something else for the source (as the `Json` context is dependant on
[php-http](https://github.com/php-http/)), extend (or use) the
`Behapi\Context\AbstractJson` class.

If you need to play with the request being built, or the response created when
the request is sent, you need to inject the `@Behapi\Tools\HttpHistory`. It is
automatically reseted between scenarios (and scenarios outlines)

A documentation will be made (soon hopefully) with more details.

Contributing
------------
Contributing (issues, pull-requests) are of course always welcome ! Just be
sure to respect the standards (such as psr-2, ... etc), and follow proper git
etiquette (atomic commits, ...) and proper conduct too, and it should be fine !

Thanks
------
This extension was made while I was working at
[@Wisembly](https://github.com/Wisembly), and heavily used for writing our
features and integration tests. Special thanks goes to
[@lunika](https://github.com/lunika), [@rgazelot](https://github.com/rgazelot)
and [@krichprollsch](https://github.com/krichprollsch), who helped conceived
this extension, and also pushed me to open-source it.

Badges
------
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Taluu/Behapi/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Taluu/Behapi/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/Taluu/Behapi/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Taluu/Behapi/build-status/master)
