<?php
namespace specIntrusive\AdoreMe\Common\Models\Eloquent;

use laravel\AdoreMe\Common\Models\Eloquent\Model;
use PhpSpec\Laravel\LaravelObjectBehavior;
use stubs\AdoreMe\Common\Traits\PhpSpecMatchersTrait;

/** @var Model $this */
class ModelSpec extends LaravelObjectBehavior
{
    use PhpSpecMatchersTrait;
}
