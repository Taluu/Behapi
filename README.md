Behapi
======
Behat extension to help write describe features related to Rest APIs (and more)

PHP 7.1 and Behat 3.3, and a discoverable php-http client are required to make
this extension work.

Installing this extension is pretty easy, and there are multiple ways to do
that ; but the one exposed here is the best (this is pretty subjective), which
is via Composer. You just need to require `taluu/behapi` and an implementation 
of a http/client (providing `php-http/client-implementation ^1.0`)  and it's 
done !

Howto
-----
Add this in your behat.yml (it's for the `default` configuration but you can
use it for any configurations actually) :

```yaml
default:
  extensions:
    Behapi\Extension\Behapi:
      base_url: 'http://localhost'
```

The `base_url` is the only requirement in the config for this extension to work.

There are other configurations keys, such as which formatter to use in a debug
environment, which headers you want to output in request or response while
debugging, and the possibility to use and configure some twig options **if you
have twig installed** ; Use the `--config-reference` flag when invoking behat
to have more information on the available configuration.

After having installed the extension, you can then use the provided contexts
such as the `Behapi\Context\Rest` for the rest operations. In order to use
them, you need to use behapi's container (`@behapi.container`, see example in
the `behat.yml.dist` file), or a container capable of using behapi's container.

Some services are provided to be injected in contexts, which are the following:

- `@http.client`, which is the http client
- `@http.history`, which is a sort of a container with the last requests done
  and last responses received
- `@http.message_factory`, which is the message factory (see psr 7)
- `@http.stream_factory`, which is the stream factory (see psr 7)
- `@twig`, which is the `Twig_Environment`, if twig is installed (`null`
  otherwise)

In order to use (and customize) the `Json` context, you actually need to either
extend `Behapi\Context\AbstractJson` if you want to use something else for the
source, or extend (or use) the `Behapi\Context\Json` context, which is dependant
on the `php-http` client.

If you need to play with the request being built, or the response created when
the request is sent, you need to inject the `@http.history`, which is an
instance of `Behapi\Extension\Tools\HttpHistory`. It is automatically reseted
between scenarios (or scenarios outlines)

A documentation will be made (soon hopefully) with more details.

Contributing
------------
Contributing (issues, pull-requests) are of course always welcome ! Just be
sure to respect the standards (such as psr-2, ... etc), and follow proper git
etiquette (atomic commits, ...) and proper conduct too, and it should be fine !

Thanks
------
This extension was made while I was working at @Wisembly, and heavily used for
writing our features and integration tests. Special thanks goes to @lunika,
@rgazelot and @krichprollsch, who helped conceived this extension, and also
pushed me to open-source it.

Badges
------
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Taluu/Behapi/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Taluu/Behapi/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/Taluu/Behapi/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Taluu/Behapi/build-status/master)
