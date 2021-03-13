# FreeTON Depool Dashboard and Staking DApp

[depools.extraton.io](https://depools.extraton.io)

## Preparing for development

Clone repository

```
git clone https://github.com/extraton/depools-dashboard-and-staking.git
cd depools-dashboard-and-staking
```

#### Prepare backend

Edit environment

```
vim .env
```

Install dependencies

```
composer install
```

Create database and apply migrations

```
bin/console doctrine:database:create
bin/console doctrine:migrations:migrate
```

Use next commands to sync with blockchain Pay attention that php-ton-client is currently not stable library, and some
command you need to call few times :(

* `bin/console depool:list:update {networkId} {depoolContractId}`  
  Update list of depools with stakes data.  
  networkId: 1 - main.ton.dev, 2 - net.ton.dev.  
  depoolContractId: 1, 3, 4 - version of depool(1, 2, 3).

* `bin/console depool:list:clean {networkId}`  
  Remove closed depools

* `bin/console depool:events:update {networkId}`  
  Sync depool events

* `bin/console depool:names:update {networkId}`  
  Update monikers

* `bin/console depool:stats:compile`  
  Compile statistic


* `bin/console depool:query:cache`  
  Cache all data for frontend

Start [symfony web server](https://symfony.com/doc/current/setup/symfony_server.html)

```
symfony server:start -d
symfony proxy:start
symfony proxy:domain:attach depools
```

#### Prepare frontend

Create local ssl certs and put into `/etc/ssl/127.0.0.1-key.pem /etc/ssl/127.0.0.1.pem`.  
I use [mkcert](https://github.com/FiloSottile/mkcert) for that.

Run dev-server

```
cd front
yarn install
yarn run serve
```

Go to [depools.wip](https://depools.wip).

## Preparing for production

### Build image

Copy and edit environment for prod

```
cp .env .env.prod
vim .env.prod
```

Build image

```
bash ./deploy/build.sh {gitTag}
```

Change image tag in kubernetes.yaml if needed.  
Apply kubernetes config. Pay attention if you don't have image locally kubernetes will try to take it from docker hub
with our .env. Actually that's wrong and .env file should be injected by kubernetes secret and if someone would like to
contribute to solve this problem we will be really glad.
```
kubectl apply -f ./deploy/kubernetes.yaml
```
Go inside one of pods, create database and apply migrations
```
kubectl exec -it {podName} -- /bin/sh
cd /var/www/html/
/usr/bin/php ./bin/console doctrine:database:create
/usr/bin/php ./bin/console doctrine:migrations:migrate
```
