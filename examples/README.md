Examples on how to run Behapi
=============================

To run the tests and see what behapi is capable of, you can do the following
commands (this expects you to run on localhost:8001, change for your needs) :

```bash
$ composer install
$ php -S localhost:8001 &
$ vendor/bin/behat
```

Some tests are failing because they are meant to : it is to show off the
possibilities. Be careful, as **most** of these steps are *experimental*.

They are made for behapi's developement, and do not show you how to write good
steps.
