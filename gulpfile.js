const gulp = require( 'gulp' );
const fs = require( 'fs' );
const $ = require( 'gulp-load-plugins' )();
const mergeStream = require( 'merge-stream' );
const pngquant = require( 'imagemin-pngquant' );
const mozjpeg = require( 'imagemin-mozjpeg' );
const named = require( 'vinyl-named' );
const webpack = require( 'webpack' );
const webpackStream = require( 'webpack-stream' );
const webpackConfig = require( './webpack.config.js' );

// Include Path for Scss
const includesPaths = [
	'./src/scss',
];

// Source directory
const srcDir = {
	scss: [
		'src/scss/**/*.scss',
	],
	js: [
		'src/js/**/*.js',
		'!src/js/**/_*.js',
	],
	jsLint: [
		'src/js/**/*.js',
	],
	img: [
		'src/img/**/*',
	],
};
// Destination directory
const destDir = {
	scss: './assets/css',
	js: './assets/js',
	img: './assets/img',
};

// Sass
gulp.task( 'sass', function() {

	return gulp.src( srcDir.scss )
		.pipe( $.plumber( {
			errorHandler: $.notify.onError( '<%= error.message %>' ),
		} ) )
		.pipe( $.sassGlob() )
		.pipe( $.sourcemaps.init() )
		.pipe( $.sass( {
			errLogToConsole: true,
			outputStyle: 'compressed',
			sourceComments: 'normal',
			sourcemap: true,
			includePaths: includesPaths,
		} ) )
		.pipe( $.sourcemaps.write( './map' ) )
		.pipe( gulp.dest( destDir.scss ) );
} );

/*
 * Bundle JS
 */
gulp.task( 'js:bundle', function() {
	const tmp = {};
	return gulp.src( [ './assets/js/*.js', '!./assets/js/_*.js' ] )
		.pipe( $.plumber( {
			errorHandler: $.notify.onError( '<%= error.message %>' ),
		} ) )
		.pipe( named() )
		.pipe( $.rename( function( path ) {
			tmp[path.basename] = path.dirname;
		} ) )
		.pipe( webpackStream( webpackConfig, webpack ) )
		.pipe( $.rename( function( path ) {
			if ( tmp[ path.basename ] ) {
				path.dirname = tmp[ path.basename ];
			} else if ( '.map' === path.extname && tmp[ path.basename.replace( /\.js$/, '' ) ] ) {
				path.dirname = tmp[ path.basename.replace( /\.js$/, '' ) ];
			}
			return path;
		} ) )
		.pipe( gulp.dest( './public/assets/js' ) );
} );

// ESLint
gulp.task( 'js:lint', () => gulp
	.src( srcDir.jsLint )
	.pipe( $.eslint( { useEslintrc: true } ) )
	.pipe( $.eslint.format() ),
);

// JS task.
gulp.task( 'js', gulp.parallel(
	'js:bundle',
	'js:lint',
) );

// Build Libraries.
gulp.task( 'copylib', function() {
	// pass gulp tasks to event stream.
	// return eventStream.merge(
	// );
} );

// Image min
gulp.task( 'imagemin', () => {
	return gulp.src( srcDir.img )
		.pipe( $.imagemin( [
			pngquant( {
				quality: '65-80',
				speed: 1,
				floyd: 0,
			} ),
			mozjpeg( {
				quality: 85,
				progressive: true,
			} ),
			$.imagemin.svgo(),
			$.imagemin.optipng(),
			$.imagemin.gifsicle(),
		] ) )
		.pipe( gulp.dest( destDir.img ) );
} );

// watch
gulp.task( 'watch', function() {
	// Make SASS
	gulp.watch( srcDir.scss, [ 'sass' ] );
	// Uglify all
	gulp.watch( srcDir.jsLint, [ 'js' ] );
	// Minify Image
	gulp.watch( srcDir.img, [ 'imagemin' ] );
} );

// Build
gulp.task( 'build', gulp.parallel( 'js', 'sass', 'imagemin' ) );

// Default Tasks
gulp.task( 'default', gulp.task( 'watch' ) );

