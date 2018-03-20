<?php
namespace specIntrusive\AdoreMe\Common\Models\NonPersistent;

use laravel\AdoreMe\Common\Models\NonPersistent\Model;
use PhpSpec\Laravel\LaravelObjectBehavior;
use stubs\AdoreMe\Common\Traits\PhpSpecMatchersTrait;

/** @var Model $this */
class ModelSpec extends LaravelObjectBehavior
{
    use PhpSpecMatchersTrait;
}
