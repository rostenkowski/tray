<?php

namespace Rostenkowski\Tray\Panel;


use Tracy\Debugger;
use Tracy\IBarPanel;

/**
 * Stopwatch
 *
 * The Stopwatch is an advanced diagnostics execution time measurement tool.
 *
 * Features
 *
 * - The results of measurements are displayed clearly in the debugger bar.
 * - The nested measurements are supported.
 * - The repeated measurements are supported, eg. in `foreach` loops.
 *
 * Usage
 *
 * <code>
 * Stopwatch::start();
 * // do something...
 * Stopwatch::stop('something');
 * <code>
 */
final class Stopwatch implements IBarPanel
{

	/**
	 * The list of the timers.
	 *
	 * @var array[float]
	 */
	private static $timers = array();

	/**
	 * The debugger bar icon.
	 *
	 * @var array[string]
	 */
	private $icon = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAQCAYAAAAmlE46AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAVdJREFUKJF10j9IllEUBvCfr39QDEzRrEkCrVVUpEEQBxcHoamhpbXJJWhy0l0MJ1c3F0fBwUEQ+UDadBJBRM0/VCRamfk5vOfVq34euHDPufec8zznPFS2SZRx+Mi7mgqxKhxgDT8eS6yq4NeiGc/wB7u4wBUyvMF2mtSEz1gPmMU5xCx6MYzvWCw6dmI6Hv5hCydowEu04Qif8DrunmIhqm/iPV6gJU4/5gPuAQaL2XzE7+jyKoH+DaNxfxKIylhCI5Rwjg/3BpUmioHtBIWhDN04xmryaTCqjiSxX1gOah0Z6nAZXQsbDnhvk1gZp6hGTRYJ9TGIwubwE1+SWIb2aPJXkL3AuLuCGEBr4ndGsX25CLwLCEfBrZK1u13ZvFxdajETEE4wgT48R1cU/or/8j2nK9OKKbmoL7GHDfluz4JTCT0e6lu1fG9zcoVcyQe3grFAcGPXQ5RVoy+x+08AAAAASUVORK5CYII=';


	/**
	 * Starts the timer.
	 *
	 * @return void
	 */
	public static function start()
	{
		Debugger::timer();
	}


	/**
	 * Stops the timer and logs event to syslog.
	 *
	 * @param string   $name The timer name.
	 * @param string[] $tags
	 * @return integer The current timer value.
	 */
	public static function stop($name, $tags = [], $data = [])
	{
		$point = Debugger::timer();

		$measure = self::add($point, $name);

		// profile log to syslog
		openlog('stopwatch', LOG_NDELAY, LOG_USER);
		syslog(LOG_INFO, json_encode([
			'event'    => $name,
			'php'      => PHP_VERSION,
			'time'     => time(),
			'host'     => $_SERVER['HTTP_HOST'],
			'uri'      => $_SERVER['REQUEST_URI'],
			'duration' => round($measure * 1000, 1), // duration in ms
			'tags'     => $tags,
			'data'     => $data,
		]));
		closelog();

		return $measure;
	}


	/**
	 * Adds value in miliseconds to list of timers.
	 *
	 * @param  integer $time The timer value.
	 * @param  string  $name The timer name.
	 * @return integer The current value of the timer.
	 */
	private static function add($time, $name)
	{
		if (key_exists($name, self::$timers)) {
			self::$timers[$name] += $time;
		} else {
			self::$timers[$name] = $time;
		}

		return self::$timers[$name];
	}


	/**
	 * Returns the debugger bar extensions ID.
	 *
	 * @return string
	 */
	public function getId()
	{
		return 'Stopwatch';
	}


	/**
	 * Returns the debugger tab HTML code.
	 *
	 * @return string
	 */
	public function getTab()
	{
		if (empty(self::$timers)) {
			return NULL;
		}

		$sum = number_format(round(array_sum(self::$timers) * 1000, 1), 1);

		return '<span><img src="' . $this->icon . '">' . $sum . ' ms</span>';
	}


	/**
	 * Returns the debugger panel HTML code.
	 *
	 * @return string
	 */
	public function getPanel()
	{
		if (empty(self::$timers)) {
			return NULL;
		}

		$sum = number_format(round(array_sum(self::$timers) * 1000, 1), 1);
		$buff = '<h1>Stopwatch</h1>';
		$buff .= '<div>';
		$buff .= '<table style="min-width: 200px; max-width: 800px; max-height: 500px;">';
		foreach (self::$timers as $name => $value) {
			$buff .= "<tr><th>$name</th><td style='text-align: right;'>" . number_format(round($value * 1000, 1), 1) . " ms</td></tr>";
		}
		$buff .= "<tr><th style='color: green;'>&sum;</th><td style='color: green; text-align: right; border-top: 3px double #888;'>" . $sum . " ms</th></tr>";
		$buff .= '</table>';
		$buff .= '</div>';

		return $buff;
	}

}
