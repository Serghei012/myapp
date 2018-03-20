# Change log

## `2.0.18`
- Updated trait `HttpClientTrait` to return correct http method in error. Previously hardcoded to `POST`.
- Reverted method `createOldModel` from `EloquentModelProviderRepositoryTrait` to version from `2.0.3`. Reason is that the new version was using `setRawAttributes`, meaning that if an attribute was an array, it should have been provided as `json` instead of `array`. Of in fact, when you're using `oldModel` the attributes are already casted to whatever u need.

## `2.0.17`
- Fixed issue with `switchValue`, which was changing items not part of the given collection.

## `2.0.16`
- Fixed bug/typo: Made method `switchValue` public, so it can be accessed from outside of repository, via service.

## `2.0.15`
- Enhanced `switchValue` code, by creating a new trait, `SwitchValueTrait` that should be used by service, method `switchValueForOne`.

## `2.0.14`
- Extracted from `EloquentPriorityHandlerRepositoryTrait`, to `EloquentSwitchValueRepositoryTrait`, a new method `switchValue`. Is the same `switchPriority` mysql logic, but abstracted for any column name. 

## `2.0.13`
- Added new interface `DefaultAttributesInterface` that will be used in combination with `ModelInterface`.

## `2.0.12`
- Added new trait `DefaultAttributesTrait`. This trait allows you to set an default value to an attribute, that will not be saved in database. On $model->{$key} the default value will be provided only if the attribute has no value, eg: is null. 

## `2.0.11`
- Added additional context when using `HttpClientTrait`.

## `2.0.10`
- Fixed function that init `HttpClientTrait`.

## `2.0.9`
- Added `HttpClientHelper` and `HttpClientTrait` models. There are used to communicate via http between our applications, eg: microservices.

## `2.0.8`
- Fixed method `findAttributesFromCollection` from `ArrayHelper` to return all found values, not only unique values.

## `2.0.7`
- Fixed issue with method `replaceModel` from `EloquentRepositoryTrait`, which was removing the non fillable attributes from model.

## `2.0.6`
- Removed unnecessary code from `EloquentTransientAttributesTrait`, that would remove transient attributes from appends.

## `2.0.5`
- Updated `findOneById` to accept anything. An id is not limited to integer numbers, can also be alpha numeric, or anything else. The only change for this release is to remove the `int` type hinting. 

## `2.0.4`
- Added more automated tests for library. Functionality is not affected.
- Fixed `ArrayHelper::filterValuesOrKeysWithNullData()` so it only removes null values, not empty ones as well (empty string). In before was limited to a single level array, now it works with multi dimensional arrays.
- Removed `ArrayHelper::checkIfArrayIsIdenticalOrThrowException`  as this was only used for testing. This is no longer used.
- More fixes to `EloquentTransientAttributesTrait`, regarding transient attribute in appends. It will now crash the execution if an transient attribute is set in `appends` but it does not have the value set, nor has an mutator.  This is identical to the `Eloquent` functionality, that also crash for the same reason.

## `2.0.3`
- Fixed issue with `EloquentTransientAttributesTrait` that would remove transient attribute from appends list, if it had no value set, but it had a get mutator. Now, the get mutator will be used to return value, when calling `toArray()`.

## `2.0.2`
- Updated `PhpSpecMatchersTrait` to auto-generate presenter, if not found.

## `2.0.1`
- Added small PhpDoc to help IDE for auto-completion.

## `2.0.0`
- Backwards incompatible with `1.0.2`.
- Main feature for this release can be found in this ticket: [LICM-5](https://jira.adoreme.com/browse/LICM-5).
- Basically we now have an general interface and general model returned by this interface. Specific interfaces for eloquent or non persistent model, are removed. 
- Created new model interface: `AdoreMe\Common\Interfaces\ModelInterface`.
 - This interface implements: `ArrayAccess`, `Arrayable`, `Jsonable`, `JsonSerializable`.
- All models returned by repository interfaces implements `ModelInterface`.
- `NonPersistentModel` now implements `ModelInterface` by default.
- Deleted:
 - `AdoreMe\Common\Interfaces\EloquentRepositoryInterface`. Use instead `AdoreMe\Common\Interfaces\Repository\ModelProviderRepositoryInterface`.
 - `AdoreMe\Common\Interfaces\NonPersistentRepositoryInterface`. Use instead `AdoreMe\Common\Interfaces\Repository\ModelProviderRepositoryInterface`.
- Moved:
 - `AdoreMe\Common\Interfaces\EloquentRepositoryWithPriorityHandlerInterface` to `AdoreMe\Common\Interfaces\Repository\PriorityHandlerRepositoryInterface`.
 - `AdoreMe\Common\Traits\EloquentRepositoryTrait` to `AdoreMe\Common\Traits\Eloquent\Repository\EloquentRepositoryTrait`.
 - `AdoreMe\Common\Traits\EloquentTransientAttributesTrait` to `AdoreMe\Common\Traits\Eloquent\EloquentTransientAttributesTrait`.
 - `AdoreMe\Common\Traits\EloquentRepositoryWithPriorityHandlerTrait` to `AdoreMe\Common\Traits\Eloquent\Repository\EloquentPriorityHandlerRepositoryTrait`.
 - `AdoreMe\Common\Traits\NonPersistentModelModelProviderRepositoryTrait` to `AdoreMe\Common\Traits\NonPersistentModel\Repository\NonPersistentModelModelProviderRepositoryTrait`.
- New files:
 - Interfaces:
   - `AdoreMe\Common\Interfaces\Repository\ModelProviderRepositoryInterface`.
   - `AdoreMe\Common\Interfaces\Repository\PriorityHandlerRepositoryInterface`.
   - `AdoreMe\Common\Interfaces\Repository\RepositoryInterface`.
 - Traits:
   - `AdoreMe\Common\Traits\Eloquent\EloquentPriorityHandlerAttributesTrait`.
     - To be used on models that should have `priority` and `enabled` attributes.
   - `AdoreMe\Common\Traits\Eloquent\Repository\EloquentModelProviderRepositoryTrait`.
     - To be used with `AdoreMe\Common\Interfaces\Repository\ModelProviderRepositoryInterface`.
     - Basically is an repository that provides a model. Nothing else. You cannot persist it.
- New stubs:
 - `stubs\AdoreMe\Common\Interfaces\FixturesInterface`.
   - Contain an interface used to define fixtures for specIntrusive tests.
 - `stubs\AdoreMe\Common\Models\FixturesAbstract`.
   - Contain an abstract class used to define fixtures for specIntrusive tests.
 - `stubs\AdoreMe\Common\Traits\PhpSpecMatchersTrait`.
   - Contain a list of common used matchers for out php spec tests.
- Added more unit and functional tests.

## `1.0.2`
- Added new function `findAttributesFromCollection` in ArrayHelper.

## `1.0.1`
- Added stubs, that can be used to test other libraries:
- `stubs\AdoreMe\Common\Application\Laravel` - used to instantiate a testing instance of laravel, via PhpSpec, and using configuration `phpspecIntrusive.yml`.
- `stubs\AdoreMe\Common\Traits\PhpSpecMockEloquentTrait` - to mock eloquent models using PhpSpec
  - Do note: The function let cannot be used because of issue https://github.com/phpspec/phpspec/issues/966
  - Instead, copy paste the `let` function in your spec class.

## `1.0.0`
- Backwards incompatible with `0.0.2`.
- Made compatible with `Laravel 5.4`.
- Updated `ProviderHelper`'s method `app` to be compatible with `Laravel 5.4`. Accepted parameters were changed.
- Major changes into `EloquentRepositoryInterface`:
  - Renamed method `create` to `createModel`.
  - New method `createNewModel` that should provide a new instance of model with its data dirty, and ready to be saved into database.
  - New method `createOldModel` similar with `createNewModel` but that should not have a dirty status. Designed for when the model will be hydrated using outside sources, for example a caching storage. The model should behave as if was loaded from database.
  - New method `updateModel`. Updates the model in database.
  - New method `replaceModel`. It will completely replace the mode into database, only keeping the id.
  - Renamed method `destroy` to `deleteModel`, and it now returns a boolean state, instead of returning `$this`.
  - Renamed parameters.
  - Removed unused/undefined methods: `massUpdate`, `findPage` and `findPageBy` that were not defined in the trait.
- Changed return comments in `FireEventInterface` to `void`.
- Better comment description for methods from `NonPersistentRepositoryInterface`.
- Updated `EloquentRepositoryTrait` according to the changes made in `EloquentRepositoryInterface`.
  - Also removed useless functions `findAllBy` and `findAll`. These were relics from original port of trait from `adoreme/nawe` into this library.
  - Fixed logic behind `findOne...` methods. Before it was retrieving the first element from `Collection` instead of first element from database. This created a huge overhead for when you wanted to retrieve only one item from a huge table.
- Fixed possible issue with `EloquentRepositoryWithPriorityHandlerTrait` method `updateAndHandlePriority` where the attributes from model were updated via `forceFill`, thus ignoring the settings for guarded attributes. Now the attributes are changed via `fill` method.
- Removed function `fireEvent` from `FireEventTrait`. You should use instead:
  ```php
  
  if (! is_null($this->getEventDispatcher())) {
     $this->getEventDispatcher()->dispatch($event, $payload, $halt);
  }

  ```
- Fixed issue with `NonPersistentRepositoryTrait` method `createNewModel` that was returning a non dirty model, similar to `createOldModel`.

## `0.0.2`
- Backwards incompatible with `0.0.1`:
  - Renamed function `storage_path` from `ProviderHelper` according to psr into `storagePath`.
  - Renamed `ArrHelper` into `ArrayHelper`
- New files:
  - Exceptions:
    - `AdoreMe\Common\Exceptions\ResourceConflictException`.
  - Interfaces:
    - `AdoreMe\Common\Interfaces\EloquentRepositoryWithPriorityHandler`.
      - Designed to be used in combination with trait `EloquentRepositoryWithPriorityHandler`.
      - It extends `EloquentRepositoryInterface`.
      - Designed to be used **instead of** `EloquentRepositoryInterface` in models that have priority attribute.
  - Models:
    - `AdoreMe\Common\Models\HeaderBag`.
      - Designed to be used for testing purpose.
      - Used to pass various headers to AbstractResourceController, at http response.
      - Used to test caching loops, as opposed to having an debug log.
  - Traits:
    - `AdoreMe\Common\Traits\EloquentRepositoryWithPriorityHandler`.
      - Designed to handle priority of models.
      - Throws `ResourceConflictException` when the priority is already in use by another model.
      - Cannot accept priorities <= 0.
      - Supports customization via `bootEloquentRepositoryWithPriorityHandlerTrait` function:
        - Error messages.
        - Attributes that are unique in database. It does not allow duplicates programmatically, instead of waiting for MySQL to crash.
        - Unique attributes can be configured to have a maximum number of characters.
        - The `switchPriority` method can be configured to let it know that the SQL used it checks constrains at end of statement and/or transaction (MySQL) or is crappy imitation that is not fully SQL compliant (SQLite). Because of these differences of approach, the SQL code is different, and in case of SQLite for example, is very expensive. Hence why the same code was not used for MySQL.
- Updated composer to let it know that the release is compatible only with `Laravel 5.2` and `Larave 5.3`.
- Code cleanup and minor bug fixing: wrong caps, wrong comments, forgotten returns.
- Added experimental code in `ProviderHelper` to be able to provide custom models while using make function.
  - This is also used to mock the models for testing, where normal mocking is not available.
- New functions for `ArrayHelper`:
  - `implodeWithKeyAndValue`
    - Designed to be used for debugging.
  - `checkIfArrayIsIdenticalOrThrowException` - self explanatory.
- Added usage for `HeaderBag` model into `HttpHelper`. and added new functions:
  - `setHeaderBag`
  - `getHeaderBag`
  - Usage:
    - `HttpHelper::getHeaderBag()->some_random_header = 'value';`
    - Upon `AbstractResourceController` responding in frontend, the new header will be observed.
- Added new function `constructIdentifierForMethodAndArguments` into `ObjectHelper`:
  - Designed to generate an unique identifier for a method call with given arguments.
  - Used to generate unique strings for cache storage of the method result.
- Added new function `respondWithConflict` into `AbstractResourceController`.
  - Designed to be used when the create/update request cannot be fulfilled because of resource conflict.
  - Used by new trait `EloquentRepositoryWithPriorityHandler` to throw exceptions when the priority is already in use by another model.

## `0.0.1`
- Initial commit
- Added models, helpers, interfaces, exceptions and traits that were common between Adore Me's laravel applications.
  - Exceptions:
    - `AdoreMe\Common\Exceptions\UnableToRetrieveDirtyStatusOfObjectException`
      - used by `NonPersistentModel`, to throw exception when $model->isDirty() cannot be fulfilled.
    - `AdoreMe\Common\Exceptions\UnexpectedItemObjectTypeInCollectionException`
      - used by `ObjectHelper`, to throw exception when unexpected object type is found in a `Collection`.
    - `AdoreMe\Common\Exceptions\UnexpectedObjectInstanceException`
      - used by `ObjectHelper`, to throw exception when unexpected object type is found.
  - Helpers:
    - `AdoreMe\Common\Helpers\ArrHelper`
      - A collections of helper functions for Arrays.
    - `AdoreMe\Common\Helpers\HttpHelper`
      - A collection of helper functions for Http responses/controllers.
    - `AdoreMe\Common\Helpers\ObjectHelper`
      - A collection of helper functions for Objects.
    - `AdoreMe\Common\Helpers\ProviderHelper`
      - A collection of helper functions, which provides objects. Emulates laravel's `env`, `app` and `storage_app` methods.
      - Designed to be used in code where we need to instantiate an object inside the model via laravel app container.
  - Http:
    - `AdoreMe\Common\Controllers\AbstractResourceController`
      - Abstract controller, containing functions to get client's remote ip, device code, user agent, and json responders json for various http codes: success, error, server error, etc.
  - Interfaces:
    - `AdoreMe\Common\EloquentRepositoryInterface`
      - Designed to be used in combination with trait `EloquentRepositoryTrait`.
      - Used to define repositories that use `Illuminate\Database\Eloquent\Model`.
    - `AdoreMe\Common\FireEventInterface`
      - Designed to be used in combination with trait `FireEventTrait`.
      - Used to define models that can dispatch events via using an outside `Dispatcher` model.
    - `AdoreMe\Common\NonPersistentRepositoryInterface`
      - Designed to be used in combination with trait `NonPersistentRepositoryTrait`.
      - Used to define repositories that use `NonPersistentModel`.
  - Models:
    - `AdoreMe\Common\Models/NonPersistentModel`
      - Abstract class used to manipulate data, similar with `Illuminate\Database\Eloquent\Model`.
  - Traits:
    - `AdoreMe\Common\Helpers\EloquentRepositoryTrait`
      - Designed to be used in combination with interface `EloquentRepositoryInterface`.
      - Repository functions for `Illuminate\Database\Eloquent\Model`.
    - `AdoreMe\Common\Helpers\EloquentTransientAttributesTrait`
      - Used to implement transient (temporary) attributes on `Illuminate\Database\Eloquent\Model` models.
        ```php
        use AdoreMe\Common\Helpers\EloquentTransientAttributesTrait;

        class SomeModel extends Illuminate\Database\Eloquent\Model
        {
            use EloquentTransientAttributesTrait;

            protected $transientAttributes = [
                'transient_attribute_1',
                'transient_attribute_2',
                ...
                'transient_attribute_n',
            ];
        }
        ```
      - This allows for example to set an attribute on an `Illuminate\Database\Eloquent\Model` model, and allow model saving, without crashing with error, that column "transient_attribute_1" does not exist.
        ```php
        $model->transient_attribute_1 = 'value';
        var_dump($model->save());
        true
        ```
    - `AdoreMe\Common\Helpers\FireEventTrait`
      - Designed to be used in combination with interface `FireEventInterface`.
      - Allows model to fire events.
    - `AdoreMe\Common\Helpers\NonPersistentRepositoryTrait`
      - Repository functions for `NonPersistentModel`.
- For mode info about models, just read them :)