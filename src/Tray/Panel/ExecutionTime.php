<?php

namespace Rostenkowski\Tray\Panel;


use Tracy\Debugger;

class ExecutionTime extends AbstractPanel
{

	function getTab()
	{
		$time = number_format((microtime(TRUE) - Debugger::$time) * 1000, 1, '.', ' ');

		return "<span title='Execution time'><b style='color: green; padding-left: 2px;'>$time ms</b></span>";
	}


	function getPanel()
	{
		return '';
	}
}
