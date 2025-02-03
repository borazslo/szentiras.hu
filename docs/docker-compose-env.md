# Environments with docker compose

## Local development environment

The `docker-compose.yml` provides a local development environment.

Start the stack: `docker compose up -d`

### The services

#### app

It build a webserver that based on the official `php` docker image with apache and 8.2 php version.
You can find the details in the `Dockerfile`.

It mounts the whole code under the `/var/www/html`, so what you modified that appear in the local server.

The app is reachable at the `http://localhost:8080` url.

#### database

It's a mariadb instance. The database folder in a named volume: `db-data`. You can zap it with `docker compose down -v` command. Notice the `-v` parameter.

The database seed at the first run with the dump in the `tmp` folder. You can put there another dump, sql scripts as you can read it the [mariadb docker image documentation]() (Initializing the database contents section)

The hostname of the service in the docker default network is `database`, be careful to use it in the .env files.

The sql port expose to localhost, so you can use any mysql client in the localhost at 3306 port.

#### mailpit

A fancy SMTP mail catcher with mail format analyser, and with API for easy testing.

The smtp port is "localhost:1025" or in the docker network: "mailpit:1025". The web ui at "http://localhost:8025".

#### composer

It installs the php dependencies with the composer.phar that's in the repo root folder.
Changing the composer.json you should run the service again to install/update the php dependencies.

It runs and exit.

#### node

Install the nodejs dependencies what's in the package-lock.json.
You shoud run it when change the nodejs dependencies.

It runs and exit.

#### gulp

Compile the assets to public build css js folders. It uses the nodejs and the installed composer artifacts.

#### migrator

Makes the migrations on the database in the starting process. We can also use this cache warm-up and other initial processes.

#### The starting order

With the `depends_on` keywords we can controll the order of the starting process.

**Independent services**

1. The **mailpit** start without any dependency.
2. The **composer** service makes the php composer install.
3. Start the **database** with the initializaton.
   When the database finish the init process they status will be healthy.
4. The **composer** install the php dependecies. The vendor folder created in the project folder (the .gitignore responsible to not appear in the version control)
5. The **node** service install the nodejs dependecies. The node_modules folder created in the project folder (the .gitignore responsible to not appear in the version control)

**Dependent services**

1. The **gulp** compiler runs after the node and composer services finished the process and exits without any errors.
2. The **migrator** runs when the database is healthy and the composer exits without any errors.
3. The **app** started when the database is healthy and the migrator exits without any errors.

### Exposed ports

| service        | protocol | port/url              |
| --             | --       | --                    |
| app            | http     | http://localhost:8080 |
| database       | mysql    | 3306                  |
| mailpit smtp   | smtp     | 1025                  |
| mailpit web ui | http     | http://localhost:8025 |
