# Prevent.Global Mobile App Api

![Deploy to Zenbox](https://github.com/Kocik/prevent-global-api/workflows/Deploy%20to%20Zenbox/badge.svg)

A backend for the Mobile app, it is a bridge between the app and the Parser. It receives and parsists files and informaiton about the user.

## Install and run
```
# install dependencies
composer install --prefer-dist --no-progress --no-suggest
# start dev server
php -S localhost:8000 -t public_html/ &
```
## Tests
There are newman tests avaliable in `tests` folder. Run following command to run them (installed `newman 4.6` is required):
```
newman run tests/newman/postman_collection.json -e tests/newman/localhost.postman_environment.json
```

## Deployment to Zenbox server
Use Git Actions workflow

## How to access files uploaded to the service
Use ftp to access the files. Access is restricted only to required personel.
