# ccxt-symfony-backend

### Install Project dependencies :
```sh
cd symfony/
```
```sh
composer install
yarn install
```

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
