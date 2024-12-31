# Build the image
Run this from the `<szentiras-repo-root>/docker` folder.

```
cd docker

docker build --build-arg UID=$(id -u) --build-arg GID=$(id -g) -t szentiras-dev .
```

Your local UID and GID need to be propagated to the image.

# Start the image the first time

This is just for the first start (initialization). Be sure to run this from the Szentiras repo root.
```
cd <szentiras-repo-root>

docker run -it --name szentiras-dev -v "$(pwd):/app" --net=host szentiras-dev

source docker/init.sh
```

# Use the image

```
docker start -ai szentiras-dev
```

Then, in the Docker interactive shell session, you may have to start the MySQL server:

```
service mysql start
```

To serve the website:
```
php artisan serve --port 1024
```

To "open a second terminal" to this Docker container:
```
docker exec -it szentiras-dev /bin/bash
```


# Why this version of Ubuntu?

Because for this, Python2 was still available (needed by something else :).