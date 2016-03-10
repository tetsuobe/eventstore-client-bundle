EventStore client bundle
========================

GetEventStore integration for Symfony and Symfony 3

[![Build Status](https://travis-ci.org/tetsuobe/eventstore-client-bundle.svg?branch=master)](https://travis-ci.org/tetsuobe/eventstore-client-bundle)

# Usage

* via GitHub

```
"repositories": [
    {
        "type": "vcs",
    	"url": "https://github.com/tetsuobe/eventstore-client-bundle"
	}
]
```
```
"require": {
    "tetsuobe/eventstore-client-bundle": "dev-master"
}
```

* via packagist
```
composer require tetsuobe/eventstore-client-bundle
```

### Developing
Run docker container

```
docker-compose up -d
```
Log into it
```
docker exec -it eventstoreclientbundle_php_1 bash
```
And run
```
composer install
```

### Tests

Functional:
```
bin/phpunit
```
Behavioral:
```
bin/behat
```