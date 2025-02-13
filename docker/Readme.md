# Build the image
Run this from the `<szentiras-repo-root>` folder.

```sh
docker build --build-arg UID=$(id -u) --build-arg GID=$(id -g) -t szentiras-dev . -f docker/Dockerfile
```

Your local UID and GID need to be propagated to the image.

# Start the image the first time

This is just for the first start (initialization). Be sure to run this from the Szentiras repo root.

```sh
docker run -it --name szentiras-dev -v "$(pwd):/app" --net=host szentiras-dev

source docker/init.sh
```

# Use the image

```sh
docker start -ai szentiras-dev
```

Then, in the Docker interactive shell session, you may have to start the MySQL server:

```sh
service mysql start
```

Then, you need to start the indexer service:

```
service sphinxsearch start
```

To serve the website:

```sh
php artisan serve --port 1024
```

To "open a second terminal" to this Docker container:

```sh
docker exec -it szentiras-dev /bin/bash
```

To connect to the database setting the right character encoding:

```sh
mysql -u homestead -p
# password: secret
SET character_set_client = 'utf8mb4';
SET character_set_connection = 'utf8mb4';
SET character_set_results = 'utf8mb4';
```

To reindex:

```sh
indexer --config /etc/sphinxsearch/sphinx.conf --all --rotate
```

# Why this version of Ubuntu?

Because for this version, Python2 was still available (needed by something else :).


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

The database seed at the first run with the dump in the `tmp` folder. You can put there another dump, sql scripts as you can read it the [mariadb docker image documentation](https://hub.docker.com/_/mariadb) (Initializing the database contents section)

The hostname of the service in the docker default network is `database`, be careful to use it in the .env files.

The sql port expose to localhost, so you can use any mysql client in the localhost at 3306 port.

#### sphinx

Sphinxsearch indexer. The config files are in `deploy` folder, there are `__ENV_VAR__` placehoders in them.
The `docker/sphinx/start.sh` changes the placeholders to the environment variable's vaules, initializes the index files.
In `dev` environment the folder of data files mounted into the container in `production` environment this folder is persisted to a named volume to avoid loss between container restarts.

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
3. The **sphinx** started when the database is healthy and the migrator exits without any errors.
4. The **app** started when the database is healthy and the migrator exits without any errors.

### Exposed ports

| service        | protocol | port/url              |
| --             | --       | --                    |
| app            | http     | http://localhost:8080 |
| database       | mysql    | 3306                  |
| mailpit smtp   | smtp     | 1025                  |
| mailpit web ui | http     | http://localhost:8025 |
