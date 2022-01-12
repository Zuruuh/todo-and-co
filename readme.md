# Todo And Co

Todo&amp;Co is a web app to manage tasks for your team.

## Prerequisites

The entire project was built using docker containers, which explains why it is the only "required" software dependency.

- Docker 20^
- Docker-compose 1.29.\*
- Make (Recommended to use project makefile)

Already containerized software like PHP, Composer, Symfony CLI, etc... Is not required to run the app, as you can run commands directly from the container, although it is highly recommended to optimize workflow.

## Installation

To install the project, you can just use the makefile and run the `install` recipe.

```bash
make install
```

If you need help about the available commands in the makefile, you can use the following command to display a help menu.

```bash
make help
# Or even simpler
make
```

If you do not have `make` installed on your computer, you can still run all the commands manually, but this might take more time than normal.

## Testing

### Libraries

The app is being tested using [PHPunit](https://phpunit.readthedocs.io/en/9.5/) for the unit and integration tests, while [Mink](https://mink.behat.org/) manages functionnal tests directly in a containerized chrome browser.

### Run tests

You can use the `makefile` to run tests and separate them by groups.

```bash
# Available tests options
make test # Will run all tests and collect coverage using Xdebug. Generated code coverage html files can be found in the "coverage" dir.
make unit # Will run all tests marked with "unit" group.
make e2e # Will run all in-browser tests marked with "e2e" group.
```

## Profiling/Performances

Project can be profiled using [Blackfire](https://blackfire.io). To do so, you will need to create yourself an account (if you don't already have one), and get your server &amp; client credentials.  
You can grab your ids and tokens from the [Credentials](https://blackfire.io/my/settings/credentials) tab on [blackfire.io](https://blackfire.io). Once you have them, create yourself a `.env.local` file at the root of your project, and add your credentials to it.  
Here is an example to show the naming each secret should have:

```ini
BLACKFIRE_SERVER_ID=server-id # Replace this with your server id.
BLACKFIRE_SERVER_TOKEN=server-token # Do the same for all the following secrets.

BLACKFIRE_CLIENT_ID=client-id
BLACKFIRE_CLIENT_TOKEN=client-token
```

To profile the app, you can either use the chromium/firefox plugin [(docs)](https://blackfire.io/docs/profiling-cookbooks/profiling-http-via-browser), or use the blackfire container and curl. We are here going to detail the use of the container.  
If your containers are setup and running correctly, you should have a blackfire container running. We will call it the `client`. This is the one you are going to use to make your profiling requests. The other blackfire instance will be running on our php server, we will call it the `server`. The way blackfire works is that you request from your client to your server, the server will collect data, and send them back to your client.

A custom shell script can be found in the `scripts` directory; you can use it to profile the server. Here is a very simple example.

```bash
scripts/profile.sh nginx # Here nginx is our nginx container name in docker-compose
```

## Contributing

As this is an open-source project, you can contribute to it by following the guide &amp; guidelines in the [contributing.md](./contributing.md) file.
