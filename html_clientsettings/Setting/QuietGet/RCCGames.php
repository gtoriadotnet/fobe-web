<?php

use Fobe\Web\WebContextManager;

header("Cache-Control: no-cache");
header("Pragma: no-cache");
header("Expires: -1");
header("Last-Modified: " . gmdate("D, d M Y H:i:s T") . " GMT");

if(!WebContextManager::VerifyAccessKeyHeader()) {
	http_response_code(404);
	exit;
}
?>
{
    "DFStringUS30605p1": "rrrr7rrrr6rrrr5rrrr4rrrr3rrrr2rrrr1rrrr0",
    "DFStringUS30605p2": "____7____6____5____4___.3.x..2....1....0",
    "DFStringUS30605p3": "....7....6....5....4....3....2....1._..0",
    "DFStringUS30605p4": "____7____6____5____4xxxx3xxx_2__..1..x.0",
    "DFStringUS30605p5": "....7....6....5....4....3....2....1....0",
    "DFStringUS30605p6": "xx::7::::6::::5::::4::::3::::2::::1::::0"
}