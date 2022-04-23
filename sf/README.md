# ccxt-symfony-backend

### Install Project dependencies :
```sh
cd symfony/
```
```sh
composer install
yarn install
```

### Generate JWT SSL keys:
```sh
php bin/console lexik:jwt:generate-keypair --skip-if-exists
```

Your keys will land in `config/jwt/private.pem` and `config/jwt/public.pem` (unless you configured a different path)

### Create database :
```sh
bin/console doctrine:database:create
# or
bin/console d:d:c
```

### Database migration :
```sh
bin/console doctrine:migrations:migrate
# or
bin/console d:m:m
```

### Start Mercure Hub :
```sh
docker-compose up
```