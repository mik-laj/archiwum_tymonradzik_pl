"use strict";
var fs = require("fs");
var gulp = require("gulp");
var plugins = require("gulp-load-plugins")();
var json = JSON.parse(fs.readFileSync("./package.json"));
var browserSync = require('browser-sync');
var reload = browserSync.reload;
var args = require('yargs').argv;


var config = (function () {
	var appName = json.name;

	var path = {
		bower: "./bower_components/",
		assets: "./assets",
		static: "./web/static"
	};

	return {
		path: path,
		scss: {
			input: [ path.assets + "/scss/style.scss" ],
			include: [
				path.bower + "/font-awesome/scss/",
				path.assets + "/scss/"
			],
			output: path.static + "/css",
			watch: [ path.assets + "/scss/**.scss" ]
		},
		fonts: {
			input: [
				path.bower + "/font-awesome/fonts/*.*",
				path.assets + "/fonts/**/*.*"
			],
			output: path.static + "/fonts"
		},
		script: {
			input: [
				path.assets + "/js/*.js"
			],
			output: {
				dir: path.static + "/js",
				filename: "script.js"
			},
			watch: [ path.assets + "/js/*.js" ]
		},
		images: {
			input: [ path.assets + "/images/**" ],
			output: path.static + "/images",
			watch: [ path.assets + "/images/**" ]
		}
	};
}());

gulp.task("bower", function () {
	return plugins.bower(config.path.bower);
});

gulp.task("fonts", function () {
	return gulp.src(config.fonts.input)
		.pipe(require('gulp-debug')())
		.pipe(gulp.dest(config.fonts.output));
});

gulp.task("js", function () {
	return gulp.src(config.script.input)
		.pipe(plugins.concat(config.script.output.filename))
		.pipe(gulp.dest(config.script.output.dir))
		.pipe(reload({ stream:true }))
		.pipe(plugins.uglify())
		.pipe(plugins.rename({extname: ".min.js"}))
		.pipe(gulp.dest(config.script.output.dir))
		.pipe(reload({ stream:true }))
});

gulp.task("scss", function () {
	return gulp.src(config.scss.input)
		.pipe(plugins.sass({
			style: "expanded",
			includePaths: config.scss.include
		}))
		.pipe(plugins.autoprefixer())
		.pipe(gulp.dest(config.scss.output))
		.pipe(reload({ stream:true }))
		.pipe(plugins.rename({extname: ".min.css"}))
		.pipe(plugins.minifyCss())
		.pipe(gulp.dest(config.scss.output))
		.pipe(reload({ stream:true }));
});

gulp.task('images', function (){
	return gulp.src(config.images.input)
		.pipe(plugins.imagemin())
		.pipe(gulp.dest(config.images.output))
		.pipe(reload({ stream:true }));
});

gulp.task('serve', function (){
	// TODO: Run PHP Server
	browserSync({
		proxy: args.host || 'localhost:8080'
	});
});

gulp.task('config', function (){
	console.log(config);
})
gulp.task("watch", ['serve'], function () {

	config.scss.watch.forEach(function (path) {
		gulp.watch(path, ["scss"]);
	});

	config.script.watch.forEach(function (path) {
		gulp.watch(path, ["js"]);
	});

	config.images.watch.forEach(function (path) {
		gulp.watch(path, ["images"]);
	});
});

gulp.task("build", ["bower", "fonts", "js", "scss", "images"])
gulp.task("default", ["js", "scss", "watch"]);
