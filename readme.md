# WordPress.org Parent Theme, 2021 edition

## Development

### Prerequisites

* Docker
* Node/npm
* Yarn
* Composer

### Setup

1. Set up repo dependencies.

    ```bash
    yarn setup:tools
    ```

1. Build the assets. The theme can't be activated until this step is done.

    ```bash
    yarn workspaces run build
    ```

1. Run the setup script.

    ```bash
    yarn setup:wp
    ```

1. Visit site at [localhost:8898](http://localhost:8898). Note the nonstandard port, this will enable the parent theme + any child theme projects to be up at the same time.

1. Log in with username `admin` and password `password`.

### Environment management

These must be run in the project's root folder, _not_ in theme/plugin subfolders.

* Stop the environment.

    ```bash
    yarn wp-env stop
    ```

* Restart the environment.

    ```bash
    yarn wp-env start
    ```

* SSH into docker container.

    ```bash
    yarn wp-env run wordpress bash
    ```

* Run wp-cli commands. Keep the wp-cli command in quotes so that the flags are passed correctly.

    ```bash
    yarn wp-env run cli "post list --post_status=publish"
    ```

* Update composer dependencies and sync any `repo-tools` changes.

    ```bash
    yarn update:tools
    ```

### Asset management

* Build assets once: `yarn workspace wporg-parent-2021 build`
* Watch assets and build on changes: `yarn workspace wporg-parent-2021 start`

## History

This is started as a fork of [Blockbase](https://github.com/Automattic/themes/tree/trunk/blockbase). The [News Theme](https://github.com/WordPress/wporg-news-2021) was the first iteration of this generation of new sites.
