<?php defined('_JEXEC') or die; ?>
<div class="mulen-payment">
    <a href="<?php echo ModMulenPaymentHelper::getPaymentLink($params); ?>" class="btn btn-primary">
        <?php echo JText::_('MOD_MULEN_PAYMENT_PAY_BUTTON'); ?>
    </a>
</div>
