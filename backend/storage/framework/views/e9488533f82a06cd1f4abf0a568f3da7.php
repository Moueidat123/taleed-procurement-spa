<?php
    use function Statamic\trans as __;
?>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width">
<meta name="robots" content="noindex,nofollow">
<meta name="color-scheme" content="<?php echo e($user?->preferredColorMode() ?? 'auto'); ?>">

<?php if(Statamic::pro() && config('statamic.cp.custom_favicon_url')): ?>
    <?php echo $__env->make('statamic::partials.favicon', ['favicon_url' => config('statamic.cp.custom_favicon_url')], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php else: ?>
    <link rel="icon" type="image/png" href="<?php echo e(Statamic::cpViteAsset('img/favicon-32x32.png')); ?>" sizes="32x32">
    <link rel="icon" type="image/png" href="<?php echo e(Statamic::cpViteAsset('img/favicon-16x16.png')); ?>" sizes="16x16">
    <link rel="apple-touch-icon" href="<?php echo e(Statamic::cpViteAsset('img/apple-touch-icon.png')); ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo e(Statamic::cpViteAsset('img/favicon.ico')); ?>">
<?php endif; ?>

<script>
    (function () {
        let mode = <?php echo ($userMode = $user?->preferredColorMode()) ? "'" . $userMode . "'" : 'null'; ?>;
        if (!mode) mode = localStorage.getItem('statamic.color_mode') ?? 'auto';
        if (mode === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches) mode = 'dark';
        if (mode === 'dark') document.documentElement.classList.add('dark');

        let contrast = <?php echo $user?->getPreference('strict_accessibility') ? "'increased'" : "'auto'"; ?>;
        if (contrast === 'auto' && window.matchMedia('(prefers-contrast: more)').matches) contrast = 'increased';
        if (contrast === 'increased') document.documentElement.setAttribute('data-contrast', 'increased');
    })();
</script>

<?php echo e(Statamic::cpViteScripts()); ?>


<?php if(Statamic::pro() && config('statamic.cp.custom_css_url')): ?>
    <link href="<?php echo e(config('statamic.cp.custom_css_url')); ?>?v=<?php echo e(Statamic::version()); ?>" rel="stylesheet">
<?php endif; ?>

<?php $__currentLoopData = Statamic::availableExternalStyles(request()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <link href="<?php echo e($url); ?>" rel="stylesheet">
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php $__currentLoopData = Statamic::availableStyles(request()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $package => $paths): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php $__currentLoopData = $paths; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $path): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <link href="<?php echo e(Statamic::vendorPackageAssetUrl($package, $path, 'css')); ?>" rel="stylesheet">
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<style id="theme-colors">
    :root {
        <?php echo e(\Statamic\CP\Color::cssVariables()); ?>


        &.dark {
            <?php echo e(\Statamic\CP\Color::cssVariables(dark: true)); ?>

        }
    }
</style>

<?php echo $__env->yieldPushContent('head'); ?>
<?php /**PATH /var/www/html/vendor/statamic/cms/src/Providers/../../resources/views/partials/head.blade.php ENDPATH**/ ?>