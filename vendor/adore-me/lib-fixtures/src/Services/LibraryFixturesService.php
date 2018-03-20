<?php
namespace AdoreMe\Library\Fixtures\Services;

use AdoreMe\Common\Interfaces\ModelInterface;
use AdoreMe\Common\Interfaces\Repository\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Symfony\Component\Console\Input\ArrayInput as ConsoleArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class LibraryFixturesService
{
    const RELATIVE_PATH = 'tests' . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'templates';
    // Config fields.
    const CONFIG_REPOSITORY_CLASS                   = 'repository_class';
    const CONFIG_MODEL_INIT                         = 'model_init';
    const CONFIG_MODEL_INIT_COMMAND                 = 'command';
    const CONFIG_MODEL_INIT_COMMAND_PARAMETERS      = 'command_parameters';
    const CONFIG_REPOSITORY_INIT                    = 'repository_init';
    const CONFIG_REPOSITORY_INIT_COMMAND            = 'command';
    const CONFIG_REPOSITORY_INIT_COMMAND_PARAMETERS = 'command_parameters';
    const CONFIG_ITEMS                              = 'items';

    /** @var Application */
    protected $application;

    /** @var ValidationFactory */
    protected $validationFactory;

    /** @var array */
    protected $validationRules = [
        '*'                                  => [
            'required',
            'array',
        ],
        '*.' . self::CONFIG_REPOSITORY_CLASS => [
            'required',
            'string',
            'repository_class_must_be_instantiable',
        ],

        '*.' . self::CONFIG_MODEL_INIT                                                      => [
            'array',
        ],
        '*.' . self::CONFIG_MODEL_INIT . '.*'                                               => [
            'required',
            'array',
        ],
        '*.' . self::CONFIG_MODEL_INIT . '.*.' . self::CONFIG_MODEL_INIT_COMMAND            => [
            'required',
            'string',
        ],
        '*.' . self::CONFIG_MODEL_INIT . '.*.' . self::CONFIG_MODEL_INIT_COMMAND_PARAMETERS => [
            'sometimes',
            'array',
        ],

        '*.' . self::CONFIG_REPOSITORY_INIT                                                           => [
            'array',
        ],
        '*.' . self::CONFIG_REPOSITORY_INIT . '.*'                                                    => [
            'required',
            'array',
        ],
        '*.' . self::CONFIG_REPOSITORY_INIT . '.*.' . self::CONFIG_REPOSITORY_INIT_COMMAND            => [
            'required',
            'string',
        ],
        '*.' . self::CONFIG_REPOSITORY_INIT . '.*.' . self::CONFIG_REPOSITORY_INIT_COMMAND_PARAMETERS => [
            'sometimes',
            'array',
        ],

        '*.' . self::CONFIG_ITEMS        => [
            'required',
            'array',
        ],
        '*.' . self::CONFIG_ITEMS . '.*' => [
            'required',
            'key_value_array_collection',
        ],
    ];

    /** @var array */
    protected $repositories = [];

    /**
     * LibraryFixturesService constructor.
     *
     * @param Application $application
     * @param ValidationFactory $validationFactory
     */
    public function __construct(Application $application, ValidationFactory $validationFactory)
    {
        $this->application       = $application;
        $this->validationFactory = $validationFactory;
    }

    /**
     * Attempt to apply the given template name.
     *
     * @param string $template
     * @return array
     * @throws \Exception
     */
    public function apply(string $template): array
    {
        $file = base_path(self::RELATIVE_PATH . DIRECTORY_SEPARATOR . $template . '.php');
        if (! file_exists($file)) {
            $validator = $this->makeValidator([], []);
            throw new ValidationException(
                $validator,
                ['Cannot find template configuration. Make sure this file exist: ' . $file]
            );
        }

        // Get the configuration for this fixtures.
        /** @var array $config */
        /** @noinspection PhpIncludeInspection */
        $config = include $file;

        if (! is_array($config) || empty($config)) {
            $validator = $this->makeValidator([], []);
            throw new ValidationException($validator, ['Invalid configuration: must return an non empty array.']);
        }

        // Validate the configuration.
        $rules     = $this->validationRules;
        $validator = $this->makeValidator($config, $rules);
        if ($validator->fails()) {
            throw new ValidationException($validator, $validator->errors()->getMessages());
        }

        $this->migrateRefresh();

        return $this->parseAndExecuteConfig($config);
    }

    /**
     * Reset the database.
     */
    public function resetDatabase()
    {
        $this->migrateRefresh();
    }

    /**
     * Parse and execute config.
     *
     * @param array $config
     * @return array
     */
    protected function parseAndExecuteConfig(array $config): array
    {
        $status    = true;
        $debugInfo = [];

        foreach ($config as $key => $value) {
            $debugRow           = [];
            $repositoryClass    = $value[self::CONFIG_REPOSITORY_CLASS] ?? null;
            $modelCommands      = $value[self::CONFIG_MODEL_INIT] ?? [];
            $repositoryCommands = $value[self::CONFIG_REPOSITORY_INIT] ?? [];
            $items              = $value[self::CONFIG_ITEMS] ?? [];
            $repository         = $this->makeRepositoryClass($repositoryClass);
            $debugKeyIdentifier = '#' . $key . ', repository: ' . $repositoryClass;

            list($statusModel, $debugModelCommands) = $this->parseAndExecuteRepositoryOrModelInitCommands(
                $repository->createNewModel(),
                $modelCommands,
                'model init'
            );
            list ($statusRepository, $debugRepositoryCommands) = $this->parseAndExecuteRepositoryOrModelInitCommands(
                $repository,
                $repositoryCommands,
                'repository init'
            );

            if (! $statusModel || ! $statusRepository) {
                $status = false;
            }

            $debugRow[$debugKeyIdentifier] = $debugModelCommands + $debugRepositoryCommands;

            foreach ($items as $itemKey => $attributes) {
                try {
                    $model = $repository->createNewModel($attributes);
                    if ($model instanceof Model) {
                        $model->forceFill($attributes);
                    }

                    $repository->saveModel($model);

                    $debugRow[$debugKeyIdentifier][] = 'item #' . $itemKey . ' created';
                } catch (\Exception $e) {
                    $status                          = false;
                    $debugRow[$debugKeyIdentifier][] = 'item #' . $itemKey . ' creation failed: ' . $e->getMessage();
                }
            }

            $debugInfo[] = $debugRow;
        }

        return [$status, $debugInfo];
    }

    /**
     * Create validator.
     *
     * @param array $attributes
     * @param array $rules
     * @return Validator
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function makeValidator(array $attributes, array $rules): Validator
    {
        $messages = [
            'key_value_array_collection'            => trans(
                'lib-fixtures::validation.key_value_array_collection'
            ),
            'repository_class_must_be_instantiable' => trans(
                'lib-fixtures::validation.repository_class_must_be_instantiable'
            ),
        ];

        /** @var Validator $validator */
        $validator = $this->validationFactory->make($attributes, $rules, $messages);

        /** @noinspection PhpUnusedParameterInspection */
        $validator->addExtension(
            'key_value_array_collection',
            function (
                /** @noinspection PhpUnusedParameterInspection */$attribute,
                $value,
                $parameters,
                $validator
            ) {
                return is_array($value) && count(array_filter(array_keys($value), 'is_string')) !== 0;
            }
        );

        /** @noinspection PhpUnusedParameterInspection */
        $validator->addExtension(
            'repository_class_must_be_instantiable',
            function (
                /** @noinspection PhpUnusedParameterInspection */$attribute,
                $value,
                $parameters,
                $validator
            ) {
                /** @var Validator $validator */
                try {
                    $this->makeRepositoryClass($value);

                    return true;
                } catch (\Exception $e) {
                    $errorMessage = $e->getMessage();
                }

                /** @noinspection PhpUnusedParameterInspection */
                $validator->addReplacer(
                    'repository_class_must_be_instantiable',
                    function (
                        /** @noinspection PhpUnusedParameterInspection */$message,
                        $attribute,
                        $rule,
                        $parameters
                    ) use ($errorMessage) {
                        return str_replace([':error_message'], [$errorMessage], $message);
                    }
                );

                return false;
            }
        );

        return $validator;
    }

    /**
     * Make repository class, or throw exception.
     *
     * @param string $class
     * @return RepositoryInterface
     */
    protected function makeRepositoryClass(string $class): RepositoryInterface
    {
        if (! array_key_exists($class, $this->repositories)) {
            $this->repositories[$class] = $this->application->make($class);
        }

        return $this->repositories[$class];
    }

    /**
     * Prepare database, by running migrations.
     */
    protected function migrateRefresh()
    {
        $kernel = $this->application->make(ConsoleKernel::class);
        $input  = new ConsoleArrayInput(['command' => 'migrate:refresh']);
        $status = $kernel->handle($input, new ConsoleOutput(ConsoleOutput::VERBOSITY_QUIET));
        $kernel->terminate($input, $status);
    }

    /**
     * Parse and execute init model commands.
     *
     * @param ModelInterface|RepositoryInterface $object
     * @param array $initCommands
     * @param string $type
     * @return array
     */
    protected function parseAndExecuteRepositoryOrModelInitCommands($object, array $initCommands, string $type): array
    {
        $status    = true;
        $debugInfo = [];

        foreach ($initCommands as $itemKey => $attributes) {
            $command      = $attributes[self::CONFIG_REPOSITORY_INIT_COMMAND] ?? null;
            $parameters   = $attributes[self::CONFIG_REPOSITORY_INIT_COMMAND_PARAMETERS] ?? [];
            $debugMessage = $type . ' :: command #' . $itemKey . ' (' . $command . ') ';

            if (! method_exists($object, $command)) {
                $debugMessage .= 'failed to execute: command does not exist';
                $debugInfo[]  = $debugMessage;

                continue;
            }

            try {
                // Execute the command, without parameters.
                if (empty($parameters)) {
                    $object->$command();
                } else { // Execute the command, with parameters.
                    $object->$command(...$parameters);
                }

                $debugMessage .= 'successfully executed';
            } catch (\Exception $e) {
                $status       = false;
                $debugMessage = 'failed to execute: ' . $e->getMessage();
            }

            $debugInfo[] = $debugMessage;
        }

        return [$status, $debugInfo];
    }
}
