# Change log

## `1.0.0`
- Backwards incompatible with `0.0.3`.
- Updated to use version `1.0.0` of `adore-me/logger` library.
- Basically with this change, this library depends of `laravel` :( , via `adore-me/logger`.
- Re-arranged code (nothing was changed, just re-arranged).

## `0.0.3`
- Fixed `makeMany` function of `FactoryAbstract`.
- Updated to use version `0.0.2` of `adore-me/logger` library.
- Added translations for error messages, in `ModelAbstract`.
- New files:
  - Exceptions:
    - `AdoreMe\Factory\Exceptions\UnrecognizableConditionTypeException`
    - `AdoreMe\Factory\Exceptions\UnexpectedConditionException`
  - Helpers:
    - `AdoreMe\Factory\Helpers\FactoryHelper`
    - `AdoreMe\Factory\Helpers\ConditionHelper`
    - `AdoreMe\Factory\Helpers\OperatorHelper`
  - Models:
    - `AdoreMe\Factory\Models\CombinationModelAbstract`
    - `AdoreMe\Factory\Models\ConditionFactoryAbstract`
    - `AdoreMe\Factory\Models\ConditionModelAbstract`
- Mew method `adminValidateAllConfigLevels` in interface and trait, that should fully validate the config array passed to factory.
  - Lets say we have an config with 
  ```php
  $config = ['a' => 'test', 'b' => ['config' => 'another_test']]
  ```
  - `setConfig` function can handle the "a" and "b" validation, but the "b" additional config might not be handled, because the "b" was not yet executed, and might not even be executed due to various conditions, or is expensive, and needs to be called only when is really needed.
  - This function is to be used when we want to validate api call with an array of data, and validate that all the config is valid, so we can save into database.
  - This function is never to be called during normal execution, but only for admin purpose validation.
- Fixed issue with `'required' = false;` validation, that would crash when the attribute is not provider, and the attribute validation has no default value.
- Added ability to get config, without the defaults. This is useful when we want to persist in database the configuration, and we don't want to save defaults as well, because those defaults might change in time, but we WANT in code to use the newly changed defaults.
- Updated `type` validator to accept array or types, in addition to a single string. This helps having multiple types as validation.

## `0.0.2`
- Backwards compatible with `0.0.1`.
- Added new validator attribute `to_cents`, used to convert the given amount from dollars to cents, by multiplying with 100.

## `0.0.1`
- Initial commit
- Added models, helpers, interfaces, exceptions and traits that were common between Adore Me's laravel applications.
  - Exceptions:
    - `AdoreMe\Factory\Exceptions\ClassToMakeDoesNotExistsException`
    - `AdoreMe\Factory\Exceptions\InvalidConfigException`
  - Interfaces:
    - `AdoreMe\Factory\Interfaces\FactoryInterface`
    - `AdoreMe\Factory\Interfaces\ModelInterface`
  - Models:
    - `AdoreMe\Factory\Models\FactoryAbstract`
    - `AdoreMe\Factory\Models\ModelAbstract`
- For mode info about models, just read them :)
