<?php $__env->startSection('head_insert'); ?>

<?php $__env->startSection('title'); ?> <?php echo \Layout\Website\Services\PageService::Page()->title; ?> <?php $__env->stopSection(); ?>

<title><?php echo $__env->yieldContent('title'); ?></title>

<link rel="icon" type="image/png" href="/theme_jamalouki/images/favicon.png"/>
<?php echo \Layout\Website\Components\ChartBeat::html(); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('theme_metatags'); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('theme_extra_js'); ?>
    <!-- Common JS links -->
    <?php echo $__env->make('theme::includes.js_extra', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('theme_css_links'); ?>
    
    <!-- Site CSS links -->
    <?php echo $__env->make('theme::includes.css_includes', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>
    <!-- Page CSS links -->
    <?php echo $__env->yieldContent('page_css_links'); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('theme_js_links'); ?>
    

    <!-- Site JS links -->
    <?php echo $__env->make('theme::includes.js_includes', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>
    <!-- Page JS links -->
    <?php echo $__env->yieldContent('page_js_links'); ?>
<?php $__env->stopSection(); ?>



<?php $__env->startSection('theme_content'); ?>

<style>
.error-page{display:block;margin:30px auto 130px;text-align:center;color:#000}
.error-page .title-1{font-size:50px;line-height:60px}
.error-page .title-2{font-size:22px;line-height:32px}
.error-page .title-2 a{color:#D63B8D;display:inline-table}
</style>

    <?php echo $__env->make('theme::includes.header', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>
    
    <div class="error-page">
        <div class="title-1">خطأ 404</div>
        <div class="title-2">الصفحة غير موجودة</div>
        <div class="title-2">اضغط <a href="/" title="موقع جمالكِ" >هنا</a> للذهاب للصفحة الرئيسية</div>
    </div>
    
<!--    <script>
        setTimeout(function(){
            window.location.href= '<?php echo \Layout\Website\Services\ThemeService::ConfigValue('APP_URL'); ?>'
        },2000)
    </script>-->
    
    <?php echo $__env->make('theme::includes.footer', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>

<?php $__env->stopSection(); ?>


<?php echo $__env->make('theme::includes.web_layout', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>