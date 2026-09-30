<?php
    use function Statamic\trans as __;
?>

<!DOCTYPE html>
<html
    lang="<?php echo e(str_replace('_', '-', Statamic::cpLocale())); ?>"
    dir="<?php echo e(Statamic::cpDirection()); ?>"
>
    <head>
        <?php echo $__env->make('statamic::partials.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </head>

    <body
        <?php if($user && $user->getPreference('strict_accessibility')): ?> data-contrast="increased" <?php endif; ?>
    >
        <div
            id="statamic"
            data-page="<?php echo e(json_encode($page ?? Statamic::nonInertiaPageData())); ?>"
        >
            <div id="blade-title" data-title="
                <?php echo $__env->yieldContent('title', $title ?? __('Here')); ?> <?php echo e(Statamic::cpDirection() === 'ltr' ? '‹' : '›'); ?>

                <?php echo e(__(Statamic::pro() ? config('statamic.cp.custom_cms_name', 'Statamic') : 'Statamic')); ?>

            "></div>
            <?php echo $__env->yieldContent('content'); ?>
        </div>

        <?php echo $__env->make('statamic::partials.scripts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->yieldContent('scripts'); ?>
    </body>
</html>
<?php /**PATH /var/www/html/vendor/statamic/cms/src/Providers/../../resources/views/layout.blade.php ENDPATH**/ ?>