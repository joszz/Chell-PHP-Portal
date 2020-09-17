<?php
$mbstringEnabled = extension_loaded('mbstring');
$psrEnabled = extension_loaded('psr');
$phalconEnabled = extension_loaded('phalcon');
die(var_dump($mbstringEnabled));

?>