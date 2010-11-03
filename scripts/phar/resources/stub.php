<?php

namespace mageekguy\atoum\phar;

use \mageekguy\atoum\phar;

\phar::mapPhar('mageekguy.atoum.phar');

require('phar://mageekguy.atoum.phar/classes/autoloader.php');

\phar::mapPhar(phar\generator::phar);

if (PHP_SAPI === 'cli')
{
	$stub = new phar\stub(__FILE__);

	set_error_handler(function($errno, $errstring) use ($stub) {
			echo sprintf($stub->getLocale()->_('Error: %s'), $errstring) . PHP_EOL;
		}
	);

	set_exception_handler(function(\exception $exception) use ($stub) {
			echo sprintf($stub->getLocale()->_('Error: %s'), $exception->getMessage()) . PHP_EOL;
		}
	);

	$stub->run();
}

__HALT_COMPILER();