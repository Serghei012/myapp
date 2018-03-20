<?php
namespace AdoreMe\Logger\Models;

use AdoreMe\Common\Interfaces\ModelInterface;
use AdoreMe\Common\Models\NonPersistentModel;
use AdoreMe\Logger\Helpers\LogHelper;
use Psr\Log\LogLevel;

/**
 * @property string level
 * @property string message
 * @property array context
 */
class Entry extends NonPersistentModel implements ModelInterface
{
    const LEVEL   = 'level';
    const MESSAGE = 'message';
    const CONTEXT = 'context';

    const EXISTING_ATTRIBUTES = [
        self::LEVEL,
        self::MESSAGE,
        self::CONTEXT,
    ];

    protected $defaultAttributesAndValues = [
        self::LEVEL   => LogLevel::ERROR,
        self::CONTEXT => [],
    ];

    /**
     * Entry constructor.
     *
     * @param array $attributes
     * @throws \Exception
     */
    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            if (! in_array($key, self::EXISTING_ATTRIBUTES, true)) {
                throw new \Exception(
                    'Unknown attribute "' . $key . '". Accepted values: ' . json_encode(self::EXISTING_ATTRIBUTES)
                );
            }
        }

        parent::__construct($attributes);
    }

    /**
     * Set the message on model.
     * Made it so to make sure the message is an string, and not object.
     *
     * @param string $level
     * @return Entry
     */
    public function setMessageAttribute(string $level): self
    {
        $this->attributes[self::MESSAGE] = $level;

        return $this;
    }

    /**
     * Set the level on model.
     * Made it so to make sure the message is an string, and is part of existing levels.
     *
     * @param string $level
     * @return Entry
     * @throws \Exception
     */
    public function setLevelAttribute(string $level): self
    {
        if (! in_array($level, LogHelper::LEVELS)) {
            throw new \Exception(
                'Expected a level from list: ' . json_encode(LogHelper::LEVELS) . '. "' . $level . '" received.'
            );
        }

        $this->attributes[self::LEVEL] = $level;

        return $this;
    }
}
