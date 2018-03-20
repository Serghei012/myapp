<?php
namespace AdoreMe\Common\Traits\Eloquent;

use AdoreMe\Common\Interfaces\Repository\PriorityHandlerRepositoryInterface;
use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * @since 2.0.0
 * @see \AdoreMe\Common\Interfaces\Repository\PriorityHandlerRepositoryInterface
 * @property bool enabled
 * @property int priority
 * @property int id
 */
trait EloquentPriorityHandlerAttributesTrait
{
    /**
     * Init the trait. Do not use "boot..." because it will booted by eloquent :/
     */
    protected function initEloquentPriorityHandlerAttributesTrait()
    {
        /** @var EloquentModel $this */
        $this->casts += [
            PriorityHandlerRepositoryInterface::PRIORITY => 'int',
            PriorityHandlerRepositoryInterface::ENABLED  => 'bool',
        ];

        $this->fillable = array_merge(
            $this->fillable,
            [
                PriorityHandlerRepositoryInterface::PRIORITY,
                PriorityHandlerRepositoryInterface::ENABLED,
            ]
        );
    }
}
