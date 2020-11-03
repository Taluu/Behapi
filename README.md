Behapi
======
Behat extension to help write describe features related to HTTP APIs.

PHP 7.3, Behat 3.5 and a discoverable php-http client are required to make
this extension work.

Installing this extension requires you to require `taluu/behapi` and an
implementation of a http client (providing
`psr/http-client-implementation ^1.0`,
`psr/http-factory-implementation ^1.0` and
`psr/http-message-implementation ^1.0`).

This still requires the interop container, while Behat doesn't have 1st class
support for the psr-11.

You can find some examples on the `examples/` directory.

Howto
-----
Add this in your behat.yml (it's for the `default` configuration but you can
use it for any configurations actually) :

```yaml
default:
  suites:
    main:
      paths: ['%paths.base%/features']
      services: '@Behapi\Container'
      autowire: true

      contexts:
        #- List
        #- your
        #- contexts
        #- here

        # examples :
        - Behapi\Http\RequestContext: ~
        - Behapi\Http\ResponseContext: ~

  extensions:
    Behapi\Behapi:
      base_url: 'http://localhost'
```

The `base_url` is the only requirement in the config for this extension to work.

There are other configurations keys, such as which formatter to use in a debug
environment, which headers you want to output in request or response while
debugging ; Use the `--config-reference` flag when invoking behat to have more
information on the available configuration.

After having installed the extension, you can then use the provided contexts
such as the `Behapi\Http\RequestContext` for the http api operations. In order
to use them, you need to use behapi's container (`@Behapi\Container`), or a
container capable of using behapi's container.

Some services are provided to be injected in contexts, which are the following:

- `@Behapi\Http\PluginClientBuilder`, which will build a
  `Http\Client\Common\PluginClient` when needed
- `@Behapi\HttpHistory\History`, which is a sort of a container with the last
  requests done and last responses received
- `@Http\Message\MessageFactory`
- `@Http\Message\StreamFactory`

*Note:* You don't really need to bother with the services names, as they are
compatible with behat's auto-wiring feature.

In order to enable the Json assertions, you need to use the
`Behapi\Context\Json` context. Note that if you use the json context, you
should have used the client provided by the client builder used in the
`Behapi\Http\RequestContext` context.

If you need to play with the request being built, or the response created when
the request is sent, you need to inject the `@Behapi\HttpHistory\History`. It is
automatically reseted between scenarios (and scenarios outlines)

A documentation will be made (soon hopefully) with more details.

Contributing
------------
Contributing (issues, pull-requests) are of course always welcome ! Be sure to
respect the standards (such as psr-2, ... etc), follow proper git etiquette
(atomic commits, ...), proper conduct too and it should be fine !

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
[![Type Coverage](https://shepherd.dev/github/Taluu/Behapi/coverage.svg)](https://shepherd.dev/github/Taluu/Behapi)
