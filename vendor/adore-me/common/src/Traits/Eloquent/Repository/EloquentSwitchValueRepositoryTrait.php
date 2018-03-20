<?php
namespace AdoreMe\Common\Traits\Eloquent\Repository;

use AdoreMe\Common\Helpers\ArrayHelper;
use AdoreMe\Common\Interfaces\ModelInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Collection;

/**
 * @property EloquentModel model
 */
trait EloquentSwitchValueRepositoryTrait
{
    /** @var bool */
    protected $sqlCheckConstrainsAtEndOfStatementOrTransaction = true;

    /**
     * Init the trait.
     *
     * @param $sqlCheckConstrainsAtEndOfStatementOrTransaction
     */
    protected function initEloquentSwitchValueRepositoryTrait(
        bool $sqlCheckConstrainsAtEndOfStatementOrTransaction = null
    ) {
        // Use the defined value only if not null. If is null then we attempt to guess the driver type.
        // For now, only sqlite is the one with "false" as value.
        if (! is_null($sqlCheckConstrainsAtEndOfStatementOrTransaction)) {
            $this->sqlCheckConstrainsAtEndOfStatementOrTransaction = $sqlCheckConstrainsAtEndOfStatementOrTransaction;
        } else if (in_array($this->model->getConnection()->getDriverName(), ['sqlite'])) {
            $this->sqlCheckConstrainsAtEndOfStatementOrTransaction = false;
        } else {
            $this->sqlCheckConstrainsAtEndOfStatementOrTransaction = true;
        }
    }

    /**
     * Because some SQL engines like SQLite are not fully SQL compliant, we might not be able to
     * update priorities, when the attribute is unique, without throwing unique constrain error.
     * Therefore we have 2 functions, one for SQL compliant, and 2nd a hacking work around for those SQLs.
     *
     * @param string $column
     * @param int $modelId
     * @param int $newValue
     * @param int $minValue
     * @param int $maxValue
     * @param bool $isIncrement
     * @param Collection $modifiedModelCollection
     * @return bool
     */
    public function switchValue(
        string $column,
        int $modelId,
        int $newValue,
        int $minValue,
        int $maxValue,
        bool $isIncrement,
        Collection $modifiedModelCollection
    ): bool {
        if ($this->sqlCheckConstrainsAtEndOfStatementOrTransaction) {
            return $this->switchValueForSqlThatCheckConstrainsAtEndOfStatementOrTransaction(
                $column,
                $modelId,
                $newValue,
                $minValue,
                $maxValue,
                $isIncrement,
                $modifiedModelCollection
            );
        } else {
            return $this->switchValueForSqlThatDoesNotCheckConstrainsAtEndOfStatementOrTransaction(
                $column,
                $modifiedModelCollection
            );
        }
    }

    /**
     * To be used for mysql generally.
     * It is to be used when as database we use an sql capable to do constrain checks at end of statements,
     * or at end of transaction.
     * The reason behind this is that value can be unique, and cannot be easily swapped.
     *
     * @param string $column
     * @param int $modelId
     * @param int $newValue
     * @param int $minValue
     * @param int $maxValue
     * @param bool $isIncrement
     * @param Collection $collection
     * @return bool
     */
    protected function switchValueForSqlThatCheckConstrainsAtEndOfStatementOrTransaction(
        string $column,
        int $modelId,
        int $newValue,
        int $minValue,
        int $maxValue,
        bool $isIncrement,
        Collection $collection
    ): bool {
        $updatedAt  = Carbon::now()->toDateTimeString();
        $connection = $this->model->getConnection();
        $tableName  = $this->model->getTable();
        $ids        = implode(',', ArrayHelper::findAttributesFromCollection('id', $collection));
        $connection->transaction(
            function ($connection) use (
                $column,
                $tableName,
                $modelId,
                $newValue,
                $minValue,
                $maxValue,
                $isIncrement,
                $updatedAt,
                $ids
            ) {
                /** @var $connection \Illuminate\Database\MySqlConnection */
                /** @noinspection SqlDialectInspection */
                /** @noinspection SqlNoDataSourceInspection */
                // Reset the value of id, to null.
                $connection->statement('UPDATE ' . $tableName . ' SET ' . $column . ' = null WHERE id = ?', [$modelId]);

                $operation = $isIncrement ? '+' : '-';
                $ordering  = $isIncrement ? 'DESC' : 'ASC';

                /** @noinspection SqlDialectInspection */
                /** @noinspection SqlNoDataSourceInspection */
                $sql = <<<SQL
UPDATE $tableName
SET $column = $column $operation 1, updated_at = ?
WHERE $column >= ? AND $column <= ?
AND id IN ($ids)
ORDER BY $column $ordering
SQL;
                $connection->statement(
                    $sql,
                    [
                        $updatedAt,
                        $minValue,
                        $maxValue,
                    ]
                );

                /** @noinspection SqlDialectInspection */
                /** @noinspection SqlNoDataSourceInspection */
                // Set the correct value of id.
                $connection->statement(
                    'UPDATE ' . $tableName . ' SET ' . $column . ' = ?, updated_at = ? WHERE id = ?',
                    [
                        $newValue,
                        $updatedAt,
                        $modelId,
                    ]
                );
            }
        );

        return true;
    }

    /**
     * To be used by sql lite in generally.
     * It is because some sql (like sql lite) does not handle constrains at end of statement, or transaction,
     * but during each update.
     * This makes harder to update value when is unique in database.
     *
     * @param string $column
     * @param Collection $collection
     * @return bool
     */
    protected function switchValueForSqlThatDoesNotCheckConstrainsAtEndOfStatementOrTransaction(
        string $column,
        Collection $collection
    ): bool {
        $updatedAt  = Carbon::now()->toDateTimeString();
        $connection = $this->model->getConnection();
        $tableName  = $this->model->getTable();
        $connection->transaction(
            function ($connection) use ($column, $tableName, $collection, $updatedAt) {
                /** @var $connection \Illuminate\Database\MySqlConnection */
                $affectedIds = [];
                $sqlQueries  = [];

                /** @var ModelInterface|EloquentModel $item */
                foreach ($collection as $item) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $itemId        = $item->id;
                    $itemValue     = $item->{$column};
                    $affectedIds[] = $itemId;

                    /** @noinspection SqlDialectInspection */
                    /** @noinspection SqlNoDataSourceInspection */
                    $sqlQueries[] = 'UPDATE ' . $tableName . ' SET updated_at = "' . $updatedAt
                        . '", ' . $column . ' = "' . $itemValue . '" WHERE id = ' . $itemId . ' LIMIT 1';
                }

                /** @noinspection SqlDialectInspection */
                /** @noinspection SqlNoDataSourceInspection */
                // Reset the values to null, so we don't have issues with constrains.
                $connection->statement(
                    'UPDATE '
                    . $tableName
                    . ' SET '
                    . $column
                    . ' = null WHERE id IN ('
                    . implode(',', $affectedIds)
                    . ')'
                );

                /** @noinspection SqlDialectInspection */
                /** @noinspection SqlNoDataSourceInspection */
                foreach ($sqlQueries as $sqlQuery) {
                    $connection->statement($sqlQuery);
                }
            }
        );

        return true;
    }
}
