<?php
namespace Rostenkowski\Tray\Panel;


class IP extends AbstractPanel
{

	public function getTab()
	{
		$host = gethostname();
		$ip = $_SERVER['SERVER_ADDR'];

		if ($ip === '127.0.0.1') {
			$ip = "<span>$ip</span>";
		} else {
			$ip = "<span style='color: blueviolet;'>$ip</span>";
		}

		return "Host: <b>$host</b> IP: <b>$ip</b>";
	}


	public function getPanel()
	{
		return '';
	}
}
