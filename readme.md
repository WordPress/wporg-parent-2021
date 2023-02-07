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

1. Start the local environment.

    ```bash
    yarn wp-env start
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

If you want to test changes to the parent theme against a child theme in another repository, you can follow the instructions in the [Main theme readme](https://github.com/WordPress/wporg-main-2022/).

## Design

### Layout

By default, the main content width is `680px` and wide width is `1160px`.

### Colors

These colors map to the values used in Figma.

- In block attributes (patterns, templates, etc), use the slug directly for a value, ex `"backgroundColor":"blueberry-1"`, though some attributes will use the var format, ex `"text":"var:preset|color|white"`.
- In CSS, the custom property is generated using the format `--wp--preset--color--${slug}`, ex, `--wp--preset--color--charcoal-1`, `--wp--preset--color--white`.

| Name           | slug           | Hex     |
|----------------|----------------|---------|
| Charcoal 0     | `charcoal-0`     | #1a1919 |
| Charcoal       | `charcoal-1`     | #1e1e1e |
| Charcoal 2     | `charcoal-2`     | #23282d |
| Charcoal 3     | `charcoal-3`     | #40464d |
| Charcoal 4     | `charcoal-4`     | #656a71 |
| Charcoal 5     | `charcoal-5`     | #979aa1 |
| Light Grey     | `light-grey-1`   | #d9d9d9 |
| Light Grey 2   | `light-grey-2`   | #f6f6f6 |
| White          | `white`          | #ffffff |
| Dark Blueberry | `dark-blueberry` | #1d35b4 |
| Deep Blueberry | `deep-blueberry` | #213fd4 |
| Blueberry      | `blueberry-1`    | #3858e9 |
| Blueberry 2    | `blueberry-2`    | #7b90ff |
| Blueberry 3    | `blueberry-3`    | #c7d1ff |
| Blueberry 4    | `blueberry-4`    | #eff2ff |
| Pomegrade      | `pomegrade-1`    | #e26f56 |
| Pomegrade 2    | `pomegrade-2`    | #ffb7a7 |
| Pomegrade 3    | `pomegrade-3`    | #ffe9de |
| Acid Green     | `acid-green-1`   | #33f078 |
| Acid Green 2   | `acid-green-2`   | #c7ffdb |
| Acid Green 3   | `acid-green-3`   | #e2ffed |
| Lemon          | `lemon-1`        | #fff972 |
| Lemon 2        | `lemon-2`        | #fffcb5 |
| Lemon 3        | `lemon-3`        | #fffdd6 |

### Fonts

#### Font families

These three font families are available to switch in the editor, for blocks that support custom font family (ex, Headings).

Example of the generated property: `var(--wp--preset--font-family--eb-garamond)`

| Name        | Slug          | Font family                  |
|-------------|---------------|------------------------------|
| EB Garamond | `eb-garamond` | `'EB Garamond', serif`       |
| Inter       | `inter`       | `'Inter', sans-serif`        |
| Monospace   | `monospace`   | `'IBM Plex Mono', monospace` |

#### Font sizes

These settings can be used in the editor & should also apply the correct line-height and switch to small-screen values (at 600px). The line heights are applied based on block classes, ex `.has-heading-1-font-size` applies the heading-1 line height too.

Example of the generated property: `--wp--preset--font-size--extra-small`

| Name        | Slug          | Size / LH   | Small Size / LH |
|-------------|---------------|-------------|-----------------|
| Extra Small | `extra-small` |  12px/1.67  | _no change_     |
| Small       | `small`       |  14px/1.71  | _no change_     |
| Normal      | `normal`      |  16px/1.875 | _no change_     |
| Large       | `large`       |  20px/1.7   | _no change_     |
| Extra Large | `extra-large` |  24px/1.58  | 20px/1.5        |
| Huge        | `huge`        |  32px/1.5   | _no change_     |
| Heading 6   | `heading-6`   |  22px/1.27  | 18px/1.22       |
| Heading 5   | `heading-5`   |  26px/1.3   | 20px/1.2        |
| Heading 4   | `heading-4`   |  30px/1.33  | 22px/1.09       |
| Heading 3   | `heading-3`   |  36px/1.28  | 26px/1.15       |
| Heading 2   | `heading-2`   |  50px/1.2   | 30px/1.07       |
| Heading 1   | `heading-1`   |  70px/1.05  | 52px/1.08       |
| CTA Heading | `heading-cta` | 120px/1     | 52px/1.08       |

### Spacing

The responsive/scaling size values are still being updated, but for now they are as follows.

| Name         | Slug         | Value                                   |
|--------------|--------------|-----------------------------------------|
| 3X-Small     | `10`         |  10px                                   |
| 2X-Small     | `20`         |  20px                                   |
| X-Small      | `30`         |  30px                                   |
| Small        | `40`         |  40px                                   |
| Medium       | `50`         | clamp( 40px, calc( 100vw / 16 ), 60px ) |
| Large        | `60`         | clamp( 20px, calc( 100vw / 18 ), 80px ) |
| X-Large      | `70`         | 100px                                   |
| 2X-Large     | `80`         | clamp( 80px, calc( 100vw / 7 ), 120px ) |
| 3X-Large     | `90`         | clamp( 80px, calc( 100vw / 7 ), 160px ) |
| Edge Spacing | `edge-space` | 80px; <890px = 20px                     |

## History

This is started as a fork of [Blockbase](https://github.com/Automattic/themes/tree/trunk/blockbase). The [News Theme](https://github.com/WordPress/wporg-news-2021) was the first iteration of this generation of new sites.
