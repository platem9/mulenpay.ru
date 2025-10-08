<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

?>

<p><?= Loc::getMessage('MULEN_PAY_REDIRECT_MESSAGE') ?></p>
<script>
    window.location.href = '<?= $params['paymentUrl'] ?>';
</script>
