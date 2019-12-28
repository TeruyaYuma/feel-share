import gulp from "gulp";
import sass from "gulp-sass";
import cleanCss from "gulp-clean-css";
import imagemin from "gulp-imagemin";
import changed from "gulp-changed";
import notify from "gulp-notify";
import plumber from "gulp-plumber";
import webpack from "webpack-stream";
import webpackConfig from "./webpack.config.js";
import eslint from "gulp-eslint";

const paths = {
    srcDir : 'src',
    dstDir : 'dist'
}


gulp.task('sass', function() {
    return gulp.src(paths.srcDir + '/scss/*.scss')
        .pipe(sass())
        .pipe(gulp.dest(paths.srcDir + '/css/'));
});
gulp.task('cleanCss', function() {
    return gulp.src(paths.srcDir + '/css/*.css')
        .pipe(cleanCss())
        .pipe(gulp.dest(paths.dstDir + '/css/'));
});
gulp.task('imagemin', function() {
    const srcGlob = paths.srcDir + '/**/*.+(jpg|jpeg|png|gif)';
    const dstGlob = paths.dstDir;
    return gulp.src(srcGlob)
        .pipe(changed(dstGlob))
        .pipe(imagemin([
            imagemin.gifsicle({interlaced: true}),
            imagemin.jpegtran({progressive: true}),
            imagemin.optipng({optimizationLevel: 5}),
            ]
        ))
        .pipe(gulp.dest(dstGlob));
});
gulp.task('build', function(){
    return gulp.src(paths.srcDir + '/js/app.js')
        .pipe(plumber({
        errorHandler: notify.onError(("Error: <%= error.message %>"))
        }))
        .pipe(webpack(webpackConfig))
        .pipe(gulp.dest(paths.dstDir + '/js/'));
});
gulp.task('eslint', function() {
    return gulp.src(['src/**/*.js'])
    .pipe(plumber({
        errorHandler: function(error) {
            const taskName = 'eslint';
            const title = '[task]' + taskName + ' ' + error.plugin;
            const errorMsg = 'error: ' + error.message;

            console.error(title + '\n' + errorMsg);

            notify.onError({
                title: title,
                message: errorMsg,
                time: 3000
            });
        }
    }))
    .pipe(eslint({ useEslintrc: true }))
    .pipe(eslint.format())
    .pipe(eslint.failOnError())
    .pipe(plumber.stop());
})

gulp.task('watch', function(){
    gulp.watch(paths.srcDir + '/js/*.js', gulp.task('build'));
    gulp.watch(paths.srcDir + '/scss/*.scss', gulp.task('sass'));
    gulp.watch(paths.srcDir + '/css/*.css', gulp.task('cleanCss'));
    gulp.watch(paths.srcDir + '/**/*', gulp.task('imagemin')); 
    gulp.watch(paths.srcDir + '**/*.js', gulp.task('eslint'));
});