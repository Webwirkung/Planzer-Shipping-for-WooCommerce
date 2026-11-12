<?php
// wp eval-file set-status.php <id> <status>
wc_get_order((int) $args[0])->update_status($args[1]);
echo 'ok';
