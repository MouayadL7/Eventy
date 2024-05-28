<?php $__env->startComponent('mail::message'); ?>
<h1>We have received your request to reset your account password</h1>
<p>You can use the following code to recover your account:</p>

<?php $__env->startComponent('mail::panel'); ?>
<?php echo e($code); ?>

<?php echo $__env->renderComponent(); ?>

<p>The allowed duration of the code is one hour from the time the message was sent</p>
<?php echo $__env->renderComponent(); ?>
<?php /**PATH C:\Users\pc\Desktop\New folder\Eventy\resources\views/emails/send-code-email-verification.blade.php ENDPATH**/ ?>