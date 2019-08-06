SDK Install
composer require developermypos/mypos-checkout-sdk

- application/config/config.php
$config['csrf_exclude_uris'] = ['forms/wtl/[0-9a-z]+', 'api\/.+', ... ];
 + 'skrill_gateway/process/complete_purchase'
