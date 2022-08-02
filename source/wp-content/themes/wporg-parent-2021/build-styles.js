#!/usr/bin/env node
/* eslint-disable no-console */
/**
 * External dependencies.
 */
const chalk = require( 'chalk' );
const fs = require( 'fs' ); // eslint-disable-line id-length
const path = require( 'path' );
const { pathToFileURL } = require( 'url' );
const postcss = require( 'postcss' );
const sass = require( 'sass' );
const { sync: resolveBin } = require( 'resolve-bin' );
const { sync: spawn } = require( 'cross-spawn' );

// An importer that redirects relative URLs starting with "~" to `node_modules`.
// See https://sass-lang.com/documentation/js-api/interfaces/FileImporter.
const nodePackageImporter = {
	findFileUrl( url ) {
		if ( ! url.startsWith( '~' ) ) {
			return null;
		}
		const file = url.substring( 1 );
		let nodeModulesPath = './node_modules/';
		let pkgPath = path.resolve( process.cwd(), nodeModulesPath, path.dirname( file ) );
		// Search upwards for the file, since it could be hoisted up to the parent project.
		// If the string starts with `/node_modules`, we're at the system root and didn't find anything.
		while ( ! fs.existsSync( pkgPath ) && ! pkgPath.startsWith( '/node_modules' ) ) {
			nodeModulesPath = '../' + nodeModulesPath;
			pkgPath = path.resolve( process.cwd(), nodeModulesPath, path.dirname( file ) );
		}

		// Build new URL with the found path.
		const newUrl = pathToFileURL( path.resolve( process.cwd(), nodeModulesPath, file ) );
		return newUrl;
	},
};

/**
 * Build the JS files using webpack, if the `src` directory exists.
 *
 * @param {string} inputDir
 * @param {string} outputDir
 */
async function maybeBuildJavaScript( inputDir, outputDir ) {
	const project = path.basename( path.dirname( inputDir ) );
	// Set the src directory based on the relative location from projet root.
	process.env.WP_SRC_DIRECTORY = path.relative( __dirname, inputDir );
	const { status, stdout } = spawn(
		resolveBin( 'webpack' ),
		[
			'--config',
			path.resolve( __dirname, 'webpack.config.js' ),
			'--output-path',
			outputDir,
			'--color', // Enables colors in `stdout`.
		],
		{
			stdio: 'pipe',
		}
	);
	// Only output the webpack result if there was an issue.
	if ( 0 !== status ) {
		console.log( stdout.toString() );
		console.log( chalk.red( `Error in JavaScript for ${ project }` ) );
	} else {
		console.log( chalk.green( `JavaScript built for ${ project }` ) );
	}
}

/**
 * Build the CSS files using PostCSS, if the `postcss` directory exists.
 *
 * @param {string} inputDir
 * @param {string} outputDir
 */
async function maybeBuildSass( inputDir, outputDir ) {
	const project = path.basename( path.dirname( inputDir ) );
	if ( ! fs.existsSync( outputDir ) ) {
		fs.mkdirSync( outputDir );
	}

	const sassRe = /^[^_].*\.scss$/i;
	const files = fs.readdirSync( inputDir ).filter( ( name ) => sassRe.test( name ) );

	for ( let i = 0; i < files.length; i++ ) {
		const inputFile = path.resolve( inputDir, files[ i ] );
		const outputFile = path.resolve( outputDir, files[ i ].replace( '.scss', '.css' ) );

		const { css } = sass.compile( inputFile, {
			outFile: outputFile,
			outputStyle: 'expanded',
			sourceMap: true,
			importers: [ nodePackageImporter ],
		} );

		const result = await postcss( [
			// Enable transforms for stage 2+, explictly enable nesting (stage 1).
			require( 'postcss-preset-env' )( {
				stage: 2,
			} ),
		] ).process( css, { from: inputFile } );
		result.warnings().forEach( ( warn ) => {
			console.log( chalk.yellow( `Warning in ${ project }:` ), warn.toString() );
		} );
		fs.writeFileSync( outputFile, result.css );
	}
	console.log( chalk.green( `CSS built for ${ project }` ) );
}

async function build() {
	try {
		const outputDir = path.resolve( path.join( __dirname, 'build' ) );

		// We `await` because JS needs to be built first— the first webpack step deletes
		// the build directory, and would remove the built CSS if it was truly async.
		await maybeBuildJavaScript( path.resolve( path.join( __dirname, 'src' ) ), outputDir );
		await maybeBuildSass( path.resolve( path.join( __dirname, 'sass' ) ), outputDir );
	} catch ( error ) {
		console.log( chalk.red( `Error:` ), error.message );
	}
}

build();
