<?php

namespace Rostenkowski\Tray\Panel;


class MemoryUsage extends AbstractPanel
{

	function getTab()
	{
		$mem = number_format(memory_get_peak_usage() / 1000000, 2, '.', ' ');

		return "<span title='The peak of allocated memory'><b style='padding-right: 2px;'>$mem MB</b></span>";
	}


	function getPanel()
	{
		return '';
	}
}
