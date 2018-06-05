# Concert-to (API)

API for concert-to, Symfony 4

## Install

Set environment variables :

```bash
cp .env.dist .env
```

Install dependencies :

```bash
composer install
```

Generate RSA keys for JWT

> Create passphrase in .env

```bash
mkdir -p config/jwt/
openssl genrsa -out config/jwt/private.pem -aes256 4096
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
```

Launch dockers :

```bash
docker-compose -f docker-compose.dev.yml up -d
```

Update database (first install) :

```bash
docker-compose -f docker-compose.dev.yml run --rm -u web bash -c "bin/console doctrine:schema:update -f" 
```

## Tests

Copy config file and set environment variables :
```bash
cp phpunit.xml.dist phpunit.xml.dist
```

Launch dockers :

```bash
docker-compose -f docker-compose.dev.yml up -d
```

Launch tests :

```bash
docker-compose -f docker-compose.dev.yml exec -u web app bash -c "bin/phpunit"
```


## Docker

### API

https://hub.docker.com/r/deuxmax/concert-to-api/

### Scrapper

https://hub.docker.com/r/deuxmax/concert-to-scrapper/