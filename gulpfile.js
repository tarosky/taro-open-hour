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

// Plumber.
let plumber = true;

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

// Style lint.
gulp.task( 'stylelint', function () {
	let task = gulp.src( srcDir.scss );
	if ( plumber ) {
		task = task.pipe( $.plumber( {
			errorHandler: $.notify.onError( '<%= error.message %>' ),
		} ) );
	}
	return task.pipe( $.stylelint( {
		reporters: [
			{
				formatter: 'string',
				console: true,
			},
		],
	} ) );
} );

// CSS task
gulp.task( 'css', gulp.parallel( 'sass', 'stylelint' ) );

/*
 * Bundle JS
 */
gulp.task( 'js:bundle', function() {
	const tmp = {};
	return gulp.src( srcDir.js )
		.pipe( $.plumber( {
			errorHandler: $.notify.onError( '<%= error.message %>' ),
		} ) )
		.pipe( named() )
		.pipe( $.rename( function( path ) {
			tmp[ path.basename ] = path.dirname;
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
		.pipe( gulp.dest( destDir.js ) );
} );

// ESLint
gulp.task( 'js:lint', () => {
	let $src = gulp.src( srcDir.jsLint );
	if ( plumber ) {
		$src = $src.pipe( $.plumber( {
			errorHandler: $.notify.onError( '<%= error.message %>' ),
		} ) );
	}
	return $src
		.pipe( $.eslint( { useEslintrc: true } ) )
		.pipe( $.eslint.format() )
		.pipe( $.eslint.failOnError() );
} );

// JS task.
gulp.task( 'js', gulp.parallel( 'js:bundle', 'js:lint' ) );

// Build Libraries.
gulp.task( 'copylib', function() {
	// pass gulp tasks to event stream.
	return mergeStream(
		gulp.src( [
			'node_modules/select2/dist/js/select2.min.js',
		] )
			.pipe( gulp.dest( 'assets/js' ) ),
		gulp.src( [
			'node_modules/select2/dist/css/select2.min.css',
		] )
			.pipe( gulp.dest( 'assets/css' ) ),
	);
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
	gulp.watch( srcDir.scss, gulp.task( 'css' ) );
	// Uglify all
	gulp.watch( srcDir.jsLint, gulp.task( 'js' ) );
	// Minify Image
	gulp.watch( srcDir.img, gulp.task( 'imagemin' ) );
} );

// Build
gulp.task( 'build', gulp.parallel( 'copylib', 'js', 'sass', 'imagemin' ) );

// Default Tasks
gulp.task( 'default', gulp.task( 'watch' ) );

// No plumber.
gulp.task( 'noplumber', ( done ) => {
	plumber = false;
	done();
} );

// Lint.
gulp.task( 'lint', gulp.series( 'noplumber', gulp.parallel( 'js:lint', 'stylelint' ) ) );
