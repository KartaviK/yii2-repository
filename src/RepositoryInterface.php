<?php
/**
 * @author Roman Varkuta <roman.varkuta@gmail.com>
 * @author majid abdolhosseini <majid8911303@gmail.com>
 * @license MIT
 * @see http://deviq.com/repository-pattern/
 * @see http://martinfowler.com/eaaCatalog/repository.html
 * @see http://shawnmc.cool/the-repository-pattern
 * @see http://stackoverflow.com/questions/16176990/proper-repository-pattern-design-in-php
 * @version 2.0
 */

declare(strict_types=1);

namespace Kartavik\Yii2;

use yii\db;
use yii\db\ActiveRecord;

/**
 * Interface RepositoryInterface
 * @package Kartavik\Yii2
 */
interface RepositoryInterface
{
    /**
     * Exist and declared ActiveRecord class name
     *
     * @return db\ActiveRecord|string
     */
    public function recordClass(): string;

    /**
     * @param array $with
     * @return self
     */
    public function with(array $with = []): RepositoryInterface;

    /**
     * @param array $columns
     * @return self
     */
    public function columns(array $columns = ['*']): RepositoryInterface;

    /**
     * @param int $limit
     * @return self
     */
    public function limit(int $limit = 10): RepositoryInterface;

    /**
     * @param array $orderBy
     * @return self
     */
    public function orderBy(array $orderBy): RepositoryInterface;

    /**
     * @param int $offset
     * @return RepositoryInterface
     */
    public function offset(int $offset = 0): RepositoryInterface;

    /**
     * @param iterable $data
     * @return db\ActiveRecord
     */
    public function create(iterable $data): db\ActiveRecord;

    /**
     * @param iterable $data
     * @return db\ActiveRecord
     */
    public function make(iterable $data): db\ActiveRecord;

    /**
     * @param array $records
     * @param bool  $runValidation
     * @return iterable|db\ActiveRecord[]
     */
    public function createMany(array $records, bool $runValidation = \true): iterable;

    /**
     * @param array $records
     * @return iterable
     */
    public function makeMany(array $records): iterable;

    /**
     * @param mixed $id
     * @return array|mixed
     * @return db\ActiveRecord|null
     */
    public function findOneById($id): ?db\ActiveRecord;

    /**
     * @param mixed  $field
     * @param mixed  $value
     * @param string $operator
     * @param array  $params
     * @return db\ActiveRecord|null
     */
    public function findOneBy($field, $value, string $operator = '=', array $params = []): ?db\ActiveRecord;

    /**
     * @param array $condition
     * @param array $params
     * @return ActiveRecord|null
     */
    public function findOneByCondition(array $condition = [], array $params = []): ?ActiveRecord;

    /**
     * @param mixed  $field
     * @param mixed  $value
     * @param string $operator
     * @param array  $params
     * @return array|db\ActiveRecord[]
     */
    public function findManyBy($field, $value, string $operator = '=', array $params = []): array;

    /**
     * @param array $ids
     * @param array $params
     * @return array|db\ActiveRecord[]
     */
    public function findManyByIds(array $ids, array $params = []): array;

    /**
     * @return array
     */
    public function findAll(): array;

    /**
     * @param mixed $id
     * @param array $data
     * @return ActiveRecord
     * @throws \Throwable
     * @throws db\StaleObjectException
     * @throws Repository\Exception
     */
    public function updateOneById($id, array $data = []): db\ActiveRecord;

    /**
     * @param mixed $field
     * @param mixed $value
     * @param array $data
     * @return ActiveRecord
     * @throws \Throwable
     * @throws db\StaleObjectException
     * @throws Repository\Exception
     */
    public function updateOneBy($field, $value, array $data = []): db\ActiveRecord;

    /**
     * @param array $condition
     * @param array $data
     * @return ActiveRecord
     * @throws \Throwable
     * @throws db\StaleObjectException
     * @throws Repository\Exception
     */
    public function updateOneByCondition(array $condition, array $data = []): db\ActiveRecord;

    /**
     * @param mixed  $field
     * @param mixed  $value
     * @param array  $data
     * @param string $operation
     * @param array  $params
     * @return int
     */
    public function updateManyBy($field, $value, array $data = [], $operation = '=', array $params = []): int;

    /**
     * @param array $condition
     * @param array $data
     * @param array $params
     * @return int
     */
    public function updateManyByCondition(array $condition = [], array $data = [], array $params = []): int;

    /**
     * @param array $ids
     * @param array $data
     * @return int
     */
    public function updateManyByIds(array $ids, array $data = []): int;

    /**
     * @param $id
     * @return bool|false|int
     * @throws \Throwable
     * @throws db\StaleObjectException
     */
    public function deleteOneById($id);

    /**
     * @param mixed $field
     * @param mixed $value
     * @param string $operation
     * @return bool|false|int
     * @throws \Throwable
     * @throws db\StaleObjectException
     */
    public function deleteOneBy($field, $value, string $operation = '=');

    /**
     * @param array $condition
     * @return false|int
     * @throws \Throwable
     * @throws db\StaleObjectException
     */
    public function deleteOneByCondition(array $condition = []);

    /**
     * @param mixed $field
     * @param mixed $value
     * @param string $operation
     * @param array $params
     * @return bool|int
     */
    public function deleteManyBy($field, $value, string $operation = '=', array $params = []);

    /**
     * @param array $criteria
     * @return boolean
     */
    public function deleteManyByCondition(array $criteria = []);

    /**
     * @param array $ids
     * @return int|mixed
     */
    public function deleteManyByIds(array $ids);
}
