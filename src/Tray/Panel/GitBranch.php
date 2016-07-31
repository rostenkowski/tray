<?php
/**
 * Created by PhpStorm.
 * User: spot
 * Date: 4/21/14
 * Time: 11:57 PM
 */

namespace Rostenkowski\Tray\Panel;


class GitBranch extends AbstractPanel
{

	private $baseDir;


	public function __construct($baseDir)
	{
		$this->baseDir = $baseDir;
	}


	function getTab()
	{
		$branch = implode('/', array_slice(explode('/', file_get_contents("$this->baseDir/.git/HEAD")), 2));

		return "Git: <b>$branch</b>";
	}


	function getPanel()
	{
		return '';
	}
}
