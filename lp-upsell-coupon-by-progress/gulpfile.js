const gulp = require('gulp');
const zip = require('gulp-zip');
const del = require('del');

// Clean release directory
gulp.task('clean', function () {
    return del(['release/**/*']);
});

// Copy plugin files to release directory
gulp.task('copy', function () {
    return gulp.src([
        '**/*',
        '!node_modules/**',
        '!release/**',
        '!gulpfile.js',
        '!package.json',
        '!package-lock.json',
        '!.gitignore',
        '!.git/**',
        '!*.md',
        '!.DS_Store'
    ], {
        base: '.',
        dot: true
    })
        .pipe(gulp.dest('release/lp-upsell-coupon-by-progress'));
});

// Create zip file
gulp.task('zip', function () {
    return gulp.src('release/lp-upsell-coupon-by-progress/**/*', {
        base: 'release'
    })
        .pipe(zip('lp-upsell-coupon-by-progress.zip'))
        .pipe(gulp.dest('release'));
});

// Clean up temporary files
gulp.task('cleanup', function () {
    return del(['release/lp-upsell-coupon-by-progress']);
});

// Main release task
gulp.task('release', gulp.series('clean', 'copy', 'zip', 'cleanup'));
