<?php
error_reporting(E_ALL ^ E_NOTICE);
define ("TEST_HOME", __DIR__);
define ("CODE_HOME", dirname(__DIR__));

require CODE_HOME . "/lib/Ntentan.php";

\ntentan\Ntentan::$cacheMethod = 'volatile';
\ntentan\Ntentan::$home = CODE_HOME;
