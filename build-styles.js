/* eslint-disable no-console */
/**
 * External dependencies.
 */
const autoprefixer = require( 'autoprefixer' );
const { dirname, resolve } = require( 'path' );
const fs = require( 'fs' ); // eslint-disable-line id-length
const { pathToFileURL } = require( 'url' );
const postcss = require( 'postcss' );
const rtlcss = require( 'rtlcss' );
const sass = require( 'sass' );

// An importer that redirects relative URLs starting with "~" to `node_modules`.
// See https://sass-lang.com/documentation/js-api/interfaces/FileImporter.
const nodePackageImporter = {
	findFileUrl( url ) {
		if ( ! url.startsWith( '~' ) ) {
			return null;
		}
		const file = url.substring( 1 );
		let nodeModulesPath = './node_modules/';
		let path = resolve( process.cwd(), nodeModulesPath, dirname( file ) );
		// Search upwards for the file, since it could be hoisted up to the parent project.
		// If the string starts with `/node_modules`, we're at the system root and didn't find anything.
		while ( ! fs.existsSync( path ) && ! path.startsWith( '/node_modules' ) ) {
			nodeModulesPath = '../' + nodeModulesPath;
			path = resolve( process.cwd(), nodeModulesPath, dirname( file ) );
		}

		// Build new URL with the found path.
		const newUrl = pathToFileURL( resolve( process.cwd(), nodeModulesPath, file ) );
		return newUrl;
	},
};

/**
 * Write a PostCSS Result to the given file destination.
 *
 * @param {string} outputFile The file path to write to.
 *
 * @return {Function} The callback used in the promise resolution.
 */
function writePostCSSResult( outputFile ) {
	return ( res ) => {
		res.warnings().forEach( ( warn ) => {
			console.warn( warn.toString() );
		} );
		fs.writeFile( outputFile, res.css, ( writeError ) => {
			if ( writeError ) {
				console.log( writeError );
			}
		} );
	};
}

/**
 * Build the CSS for the theme. First run sass to compile down to plain CSS, then run PostCSS to apply
 * autoprefixer. If the `--no-rtl` flag is passed, that's all. If not, another PostCSS process is run to
 * apply rtlcss and autoprefixer to the Sass output, and save that to the `[file]-rtl.css` file.
 *
 * This assumes your main sass file will be `sass/style.scss`, and should be built into `build/style.css`.
 * It will also build any other top-level Sass files (unless they start with `_`) into `build`.
 */
try {
	const inputDir = resolve( 'sass' );
	const outputDir = resolve( 'build' );
	const skipRTL = process.argv.slice( 2 ).includes( '--no-rtl' );

	if ( ! fs.existsSync( outputDir ) ) {
		fs.mkdirSync( outputDir );
	}

	const sassRe = /^[^_].*\.scss$/i;
	const files = fs.readdirSync( inputDir ).filter( ( name ) => sassRe.test( name ) );

	for ( let i = 0; i < files.length; i++ ) {
		const inputFile = resolve( inputDir, files[ i ] );
		const outputFile = resolve( outputDir, files[ i ].replace( '.scss', '.css' ) );

		const result = sass.compile( inputFile, {
			outFile: outputFile,
			outputStyle: 'expanded',
			sourceMap: true,
			importers: [ nodePackageImporter ],
		} );

		const css = result.css;

		// Build LTR file.
		postcss( [ autoprefixer ] ).process( css, { from: outputFile } ).then( writePostCSSResult( outputFile ) );

		// Build RTL file if needed.
		if ( ! skipRTL ) {
			postcss( [ rtlcss, autoprefixer ] )
				.process( css, { from: outputFile } )
				.then( writePostCSSResult( outputFile.replace( '.css', '-rtl.css' ) ) );
		}
	}
} catch ( error ) {
	console.log( error.message );
}
