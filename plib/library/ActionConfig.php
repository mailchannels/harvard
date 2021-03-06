<?php
/**
 * Created by PhpStorm.
 * User: mike
 * Date: 2016-03-13
 * Time: 9:10 PM
 */

/**
 * Class for the configuration page.
 *
 * @since  1.0
 */
class Modules_Harvard_ActionConfig implements IteratorAggregate
{

    private $config = [];

    /**
     * Modules_Harvard_ActionConfig constructor.
     */
    public function __construct()
    {
        $this->config = self::getActionConfig();
    }

    /**
     * Adds a new event/action pair to the configuration.
     *
     * @param   string  $event   Key of the event to react to.
     * @param   string  $action  Key of the action to take in case of the specified event.
     *
     * @return void
     */
    public function addAction($event, $action)
    {
        $found = false;

        foreach ($this->config as $idx => $item)
        {
            if ($item['event'] == $event)
            {
                $found = true;
                $this->config[$idx] = ['event' => $event, 'action' => $action];
            }
        }

        if (!$found)
        {
            array_push($this->config, ['event' => $event, 'action' => $action]);
        }

        self::setActionConfig($this->config);
    }

    public function removeAction($event)
    {
        foreach ($this->config as $idx => $item)
        {
            if ($item['event'] == $event)
            {
                unset($this->config[$idx]);
                break;
            }
        }

        self::setActionConfig($this->config);
    }

    /**
     * Retrieve the action configuration from key/val storage.
     *
     * @return  array  Configuration array.
     */
    private static function getActionConfig()
    {
        $json = pm_Settings::get(actionConfig);

        pm_Log::debug("getActionConfig -> $json");

        if (!isset($json))
        {
            $json = '[{"condition_name":"*"}]'; // TBD: Empty list once everything is working
        }

        return self::cleanConfig(json_decode($json, true));
    }

    /**
     * Store the action configuration to the key/val storage
     *
     * @param   array  $config  Array containing the new configuration.
     *
     * @return  array  The new configuration.
     */
    private static function setActionConfig($config)
    {
        pm_Log::debug(sprintf("setActionConfig -> %s", print_r($config, true)));
        pm_Settings::set(actionConfig, json_encode(self::cleanConfig($config)));
    }

    /**
     * Clean up a configuration array.
     *
     * @param   array  $config  The configuration to clean up.
     *
     * @return  array  Cleaned configuration.
     */
    private static function cleanConfig($config)
    {
        if (empty($config))
        {
            $config = [];
        }

        foreach ($config as $idx => $item)
        {
            if (!(isset($item['event']) && isset($item['action'])))
            {
                unset($config[$idx]);
            }
            if (! (in_array($item['event'], self::getAvailableEvents())
                    && in_array($item['action'], self::getAvailableActions()))) {
                unset($config[$idx]);
            }
        }

        return $config;
    }

    /**
     * Returns all available events.
     *
     * @return  array  Available events.
     */
    public static function getAvailableEvents()
    {
        return [
            'all',
            'tag_spamming',
            'tag_bad-recipients',
            'tag_bounce-rate',
            'tag_phishing',
            'tag_complaints',
        ];
    }

    /**
     * Returns all available actions.
     *
     * @return  array  Available actions.
     */
    public static function getAvailableActions()
    {
        return [
            'block-sender',
            'block-domain',
        ];
    }

    public static function eventDescription($tag) {
        return pm_Locale::lmsg("select-event-$tag");
    }

    public static function actionDescription($tag) {
        return pm_Locale::lmsg("select-action-$tag");
    }

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new ArrayIterator($this->config);
    }
}