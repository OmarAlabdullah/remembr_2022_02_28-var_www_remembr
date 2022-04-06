<?php
$r = enchant_broker_init();
$dictionary0 = enchant_broker_request_dict($r, 'en_US');
$dictionary1 = enchant_broker_request_dict($r, 'nl_NL');

var_dump(enchant_dict_check($dictionary0, 'huub'));
var_dump(enchant_dict_check($dictionary1, 'huub'));
var_dump(enchant_dict_check($dictionary0, 'morgen'));
var_dump(enchant_dict_check($dictionary1, 'morgen'));
var_dump(enchant_dict_check($dictionary0, 'ape'));
var_dump(enchant_dict_check($dictionary1, 'ape'));
?>
