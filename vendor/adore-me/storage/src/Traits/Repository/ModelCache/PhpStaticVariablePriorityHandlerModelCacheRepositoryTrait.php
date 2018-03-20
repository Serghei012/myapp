<?php
namespace AdoreMe\Storage\Traits\Repository\ModelCache;

use AdoreMe\Common\Interfaces\ModelInterface;

/***************************************************************************************************************************
/*                                                                                                                         *
/*                                    !!! A T T E N T I O N !!!                                                            *
/*                                                                                                                         *
/* PHP Bug: https://bugs.php.net/bug.php?id=63911 - Ignore conflicting trait methods originating from identical sub traits *
/*                                                                                                                         *
/* ------------------------------------------------------------------------------------------------------------------------*
/*                                                                                                                         *
/* The trait "PhpStaticVariableCacheRepositoryForEloquentRepositoryTrait" cannot be used in                                *
/* "PhpStaticVariableCacheRepositoryForEloquentRepositoryWithPriorityHandlerTrait" because it will produce trait collisions*
/*                                                                                                                         *
/* ------------------------------------------------------------------------------------------------------------------------*
/*                                                                                                                         *
/* ANY CHANGES MADE HERE MUST BE ALSO REFLECTED IN                                                                         *
/* "PhpStaticVariableModelCacheRepositoryTrait"                                                                            *
/*                                                                                                                         *
/**************************************************************************************************************************/
trait PhpStaticVariablePriorityHandlerModelCacheRepositoryTrait
{
    use PriorityHandlerModelCacheRepositoryTrait;

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * Create a model instance using the storage attributes.
     *
     * @param mixed $value
     * @return ModelInterface
     */
    private function createModelFromStorage($value): ModelInterface
    {
        return $value;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * Prepare the model for being stored.
     *
     * @param ModelInterface $model
     * @return mixed|array
     */
    private function prepareModelForStorage(ModelInterface $model)
    {
        return $model;
    }
}
