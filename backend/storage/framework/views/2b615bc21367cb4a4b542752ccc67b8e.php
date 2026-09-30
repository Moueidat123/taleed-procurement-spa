<?php
    use function Statamic\trans as __;
?>

<inertia-link href="<?php echo e($item->url()); ?>" class="flex items-center gap-2 sm:gap-3 <?php echo e($item->isActive() ? 'active' : ''); ?>">
    <?php echo Statamic::svg('icons/updates', 'size-4 shrink-0') ?>
    <span v-pre><?php echo e(__($item->name())); ?></span>
    <updates-badge class="-ml-1.5"></updates-badge>
</inertia-link>
<?php /**PATH /var/www/html/vendor/statamic/cms/src/Providers/../../resources/views/nav/updates.blade.php ENDPATH**/ ?>