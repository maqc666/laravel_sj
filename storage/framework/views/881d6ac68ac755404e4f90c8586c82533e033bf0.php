<!doctype html>
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-08-24
 * Time: 19:44
 */

<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="X-UA-Compatible" content="ie=edge"

        <title>文件上传</title>

        <style>
            body{
                margin:0px;
                padding:0px;
                font-size:14px;
            }

           .box{
               width:800px;
               margin: 0 auto;
           }

        </style>

</head>
<body>
<div class="box">
<form method="post"  action="<?php echo e(route('up')); ?>" enctype="multipart/form-data">

   <?php echo csrf_field(); ?>

    <p>
        <input type="file" name="pic" id="">

    </p>
    <p>
        <input type="submit" value="文件上传">
    </p>

</form>
</div>



</body>

</html><?php /**PATH D:\wamp64\www\laravel\resources\views/index.blade.php ENDPATH**/ ?>