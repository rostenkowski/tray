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
	 * Default data
	 *  
	 * @var array
	 */
	private static $commonData = [];

	private static $indexName = 'perf';

	/**
	 * The list of the timers.
	 *
	 * @var array[float]
	 */
	private static $timers = array();


	/**
	 * Starts the timer.
	 *
	 * @param $name
	 */
	public static function start($name)
	{
		Debugger::timer($name);
	}


	public static function setCommonData($data = [])
	{
		self::$commonData = $data;
	}


	public static function setIndexName($name)
	{
		self::$indexName = $name;
	}


	/**
	 * Stops the timer and logs event to syslog.
	 *
	 * @param string $name The timer name.
	 * @return float The current timer value.
	 */
	public static function stop($name, $data = [])
	{
		$point = Debugger::timer($name);

		$measure = self::add($point, $name);

		$tags = [];
		if (isset($data['tags'])) {
			$tags = $data['tags'];
			unset($data['tags']);
		}

		syslog(LOG_INFO, json_encode([

			'type' => self::$indexName,
			'tag'  => explode(' ', $name) + $tags,
			'dur'  => round($measure * 1000, 1), // duration in ms
			'mem'  => memory_get_peak_usage(),
			'php'  => PHP_VERSION,
			'uri'  => $_SERVER['REQUEST_URI'],
			'time' => time(),
			'host' => $_SERVER['HTTP_HOST'],
			'data' => array_merge(self::$commonData, $data),

		]));

		return $measure;
	}


	/**
	 * Adds value in miliseconds to list of timers.
	 *
	 * @param  integer $time The timer value.
	 * @param  string $name The timer name.
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
			return null;
		}

		$sum = number_format(round(array_sum(self::$timers) * 1000, 1), 1);

		return '<span>(' . $sum . ' ms)</span>';
	}


	/**
	 * Returns the debugger panel HTML code.
	 *
	 * @return string
	 */
	public function getPanel()
	{
		if (empty(self::$timers)) {
			return null;
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
