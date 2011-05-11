<?php

$extensionPath = t3lib_extMgm::extPath('content_replacer');

return array(
	'tx_contentreplacer_repository_term' => $extensionPath . 'Classes/Repository/class.tx_contentreplacer_repository_term.php',
	'tx_contentreplacer_service_abstractparser' => $extensionPath . 'Classes/Service/class.tx_contentreplacer_service_abstractparser.php',
	'tx_contentreplacer_service_spanparser' => $extensionPath . 'Classes/Service/class.tx_contentreplacer_service_spanparser.php',
);

?>