let gulp = require('gulp');

var run = {
	less        : require( 'gulp-less' ),
	concat      : require( 'gulp-concat' ),
	css_minify  : require( 'gulp-csso' ),
	css_beutify : require( 'gulp-cssbeautify' ),
	js_minify   : require( 'gulp-uglify' ),
	js_beutify  : require( 'gulp-jsbeautifier' ),
	plumber     : require( 'gulp-plumber' ),
	path        : require( 'path' ),
};

let name        = "debug-bar-rewrite-rules";
let directory   = run.path.resolve('./assets');


function less_watcher() {
	return gulp.watch([ directory+"/*.less" ], less_builder);
}

function css_watcher() {
	return gulp.watch([ directory+"/*.min.css" ], css_beutifier);
}

function js_watcher() {
	return gulp.watch([ directory+"/*.dev.js" ], gulp.parallel(js_uglify, js_beutify));
}

function less_builder () {
	return  gulp.src(directory+"/*.less")
				.pipe(run.plumber())
				.pipe(run.less())
				.pipe(run.concat(name + '.min.css'))
				.pipe(run.css_minify())
				.pipe(run.plumber.stop())
				.pipe(gulp.dest(directory+"/"))
}

function css_beutifier() {
	return gulp.src(directory+"/*.min.css")
				.pipe(run.plumber())
				.pipe(run.concat(name + '.css'))
				.pipe(run.css_beutify())
				.pipe(run.plumber.stop())
				.pipe(gulp.dest(directory+"/"))
}


function js_uglify(){
	return gulp.src(directory+"/*.dev.js")
			.pipe(run.plumber())
			.pipe(run.concat(name + '.min.js'))
			.pipe(run.js_minify())
			.pipe(run.plumber.stop())
			.pipe(gulp.dest(directory+"/"))
}

function js_beutify(){
	return gulp.src(directory+"/*.dev.js")
		.pipe(run.plumber())
		.pipe(run.concat(name + '.js'))
		.pipe(run.js_beutify())
		.pipe(run.plumber.stop())
		.pipe(gulp.dest(directory+"/"))
}

}

exports.default = gulp.parallel(
	gulp.parallel( less_watcher, css_watcher, js_watcher),
	gulp.parallel( less_builder, js_beutify, js_uglify ),
);
