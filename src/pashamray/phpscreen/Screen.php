<?php
/**
 * Created by PhpStorm.
 * User: ps
 * Date: 10.11.17
 * Time: 18:58
 */

namespace pashamray\phpscreen;

class Screen
{
    const SCREEN_STATUS_ACTIVE = 'active';
    const SCREEN_STATUS_INACTIVE = 'inactive';
    const SCREEN_STATUS_ERROR = 'error';

    private $log_dir;

    public function __construct($log_dir = null)
    {
        $log_dir = '/tmp';

        if(isset($log_dir)) {
            $this->log_dir = $log_dir;
        }
    }

    /**
     * Return screen sessions
     *
     * @return array
     */
    public function screenList()
    {
        $filter_empty = function ($value) {
          return strlen($value);
        };

        $screens = [];

        $output = shell_exec('screen -ls');
        $lines = explode("\n", $output);

        $lines = array_filter($lines, $filter_empty);

        array_shift($lines);
        array_pop($lines);

        foreach ($lines as $line)
        {
            $blocks = explode("\t", $line);
            $blocks = array_filter($blocks, $filter_empty);
            $blocks = array_values($blocks);
            $screen = array_shift($blocks);
            $screens[] = $screen;
        }

        return $screens;
    }

    /**
     * Write line to screen session
     *
     * @param $session
     * @param $line
     *
     * @return string
     */
    public function screenWrite($session, $line)
    {
        $output = shell_exec('screen -S '.$session.' -X stuff '.$line."\n");
        return $output;
    }

    /**
     * Read output screen
     *
     * @param $session
     * @return string
     */
    public function screenRead($session)
    {
        $log_file = $this->getLogFileName($session);
        $output = shell_exec('screen -S '.$session.' -X hardcopy '.$log_file);

        $get_log_file = file_get_contents($log_file);

        return $get_log_file;
    }

    public function screenStatus($session)
    {
        $ttys = array_map(function ($val) {
            return explode('.', $val)[1];
        }, $this->screenList());

        return in_array($session, $ttys) ? self::SCREEN_STATUS_ACTIVE : self::SCREEN_STATUS_INACTIVE;
    }

    /**
     * Create new screen
     *
     * @param $command
     * @param $name
     * @return string
     */
    public function screenRun($command, $name)
    {
        $output = shell_exec('screen -dmS '.$name.' '.$command);
        return $output;
    }

    private function getLogFileName($session)
    {
        return $this->log_dir.'/php-screen_'.$session.'.log';
    }
}