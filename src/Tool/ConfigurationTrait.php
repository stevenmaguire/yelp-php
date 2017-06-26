<?php

namespace Stevenmaguire\Yelp\Tool;

trait ConfigurationTrait
{
    /**
     * Retrives the value of a given property from the client.
     *
     * @param  string  $property
     *
     * @return mixed|null
     */
    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }

        return null;
    }

    /**
     * Maps legacy configuration keys to updated keys.
     *
     * @param  array   $configuration
     *
     * @return array
     */
    protected function mapConfiguration(array $configuration)
    {
        array_walk($configuration, function ($value, $key) use (&$configuration) {
            $newKey = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $key))));
            $configuration[$newKey] = $value;
        });

        return $configuration;
    }

    /**
     * Parse configuration using defaults
     *
     * @param  array $configuration
     * @param  array $defaults
     *
     * @return mixed
     */
    protected function parseConfiguration($configuration = [], $defaults = [])
    {
        $configuration = array_merge($defaults, $this->mapConfiguration($configuration));

        array_walk($configuration, [$this, 'setConfig']);

        return $this;
    }

    /**
     * Attempts to set a given value.
     *
     * @param mixed   $value
     * @param string  $key
     *
     * @return mixed
     */
    protected function setConfig($value, $key)
    {
        $setter = 'set' . ucfirst($key);

        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } elseif (property_exists($this, $key)) {
            $this->$key = $value;
        }

        return $this;
    }
}
