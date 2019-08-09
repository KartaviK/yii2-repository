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

use Kartavik\Yii2\Repository\Sort;
use yii\base\Component;
use yii\db;
use yii\di;
use yii\helpers\VarDumper;

/**
 * Class Repository
 * @package Kartavik\Yii2
 * @since 2.0
 */
class Repository extends Component implements RepositoryInterface
{
    /** @var db\ActiveRecord|string */
    protected const RECORD = db\ActiveRecord::class;

    /** @var array */
    protected $with = [];

    /** @var array */
    protected $columns = ['*'];

    /** @var array|string */
    protected $orderBy = [];

    /** @var int */
    protected $limit = 100;

    /** @var int */
    protected $offset = 0;

    /** @var db\Connection */
    private $connection = db\Connection::class;

    public function __construct(db\Connection $db, array $config = [])
    {
        $this->connection = $db;

        parent::__construct($config);
    }

    /**
     * {@inheritDoc}
     *
     * @return db\ActiveRecord|string
     */
    public function recordClass(): string
    {
        return static::RECORD;
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init(): void
    {
        $this->connection = di\Instance::ensure($this->connection, db\Connection::class);

        foreach ((array)$this->recordClass()::primaryKey() as $key) {
            $this->orderBy[$key] = Sort::DESC;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function with(array $with = []): RepositoryInterface
    {
        $this->with = $with;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function columns(array $columns = ['*']): RepositoryInterface
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function offset(int $offset = 0): RepositoryInterface
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function limit(int $limit = 100): RepositoryInterface
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function orderBy(iterable $orderBy): RepositoryInterface
    {
        $order = [];

        foreach ($orderBy as $property => $sortType) {
            if (!Sort::isValid($sortType)) {
                throw new Repository\Exception("Data contain invalid order type value [{$sortType}]");
            }

            $order[$property] = $sortType;
        }

        $this->orderBy = $order;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function create(iterable $data = []): db\ActiveRecord
    {
        /** @var db\ActiveRecord $record */
        $record = $this->make($data);

        if (!$record->save()) {
            throw new Repository\Exception($this->exportErrorMessage($record));
        }

        return $record;
    }

    /**
     * {@inheritDoc}
     */
    public function make(iterable $data = []): db\ActiveRecord
    {
        /** @var db\ActiveRecord $record */
        $record = new ($this->recordClass());

        foreach ($data as $property => $value) {
            $record->setAttribute($property, $value);
        }

        return $record;
    }

    /**
     * {@inheritDoc}
     */
    public function createMany(array $records, bool $runValidation = \true): iterable
    {
        /** @var db\ActiveRecord[] $batch */
        $batch = $this->makeMany($records);

        foreach ($batch as $record) {
            if (!$record->save($runValidation)) {
                throw new Repository\Exception($this->exportErrorMessage($record));
            }
        }

        return $batch;
    }

    public function makeMany(array $records): iterable
    {
        $batch = [];

        foreach ($records as $recordData) {
            $batch[] = $this->make($recordData);
        }

        return $batch;
    }

    /**
     * {@inheritDoc}
     */
    public function findOneBy($field, $value, string $operator = '=', array $params = []): ?db\ActiveRecord
    {
        return $this->findOneByCondition([$operator, $field, $value]);
    }

    /**
     * {@inheritDoc}
     */
    public function findOneById($id): ?db\ActiveRecord
    {
        return $this->findOneByCondition($this->primaryKeyCondition((array)$id));
    }

    /**
     * {@inheritDoc}
     */
    public function findOneByCondition(array $condition = [], array $params = []): ?db\ActiveRecord
    {
        return $this->fetchOne(
            $this->apply($this->recordClass()::find())
                ->where($condition, $params)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function findManyBy($field, $value, string $operator = '=', array $params = []): array
    {
        return $this->findManyByCondition([$operator, $field, $value]);
    }

    /**
     * {@inheritDoc}
     */
    public function findManyByIds(array $ids, array $params = []): array
    {
        return $this->findManyByCondition($this->primaryKeyCondition($ids), $params);
    }

    /**
     * {@inheritDoc}
     */
    public function findManyWhereIn($field, array $values): ?array
    {
        return $this->findManyByCondition([$field => $values]);
    }

    /**
     * {@inheritDoc}
     */
    public function findManyByCondition(array $condition = [], array $params = []): array
    {
        return $this->fetchMany(
            $this->apply($this->recordClass()::find())
                ->where($condition, $params)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function findAll(): array
    {
        return $this->fetchMany(
            $this->apply($this->recordClass()::find())
        );
    }

    /**
     * {@inheritDoc}
     */
    public function updateOneById(
        $id,
        array $data = [],
        $runValidation = \true,
        array $attributeNames = \null
    ): db\ActiveRecord {
        return $this->updateRecord($this->findOneById($id), $data, $runValidation, $attributeNames);
    }

    /**
     * {@inheritDoc}
     */
    public function updateOneBy($field, $value, array $data = [], array $params = []): db\ActiveRecord
    {
        return $this->updateRecord($this->findOneBy($field, $value, '=', $params), $data);
    }

    /**
     * {@inheritDoc}
     */
    public function updateOneByCondition(array $condition, array $data = [], array $params = []): db\ActiveRecord
    {
        return $this->updateRecord($this->findOneByCondition($condition, $params), $data);
    }

    /**
     * {@inheritDoc}
     */
    public function updateManyBy($field, $value, array $data = [], $operation = '=', array $params = []): int
    {
        return $this->recordClass()::updateAll($data, [$operation, $field, $value], $params);
    }

    /**
     * {@inheritDoc}
     */
    public function updateManyByCondition(array $condition = [], array $data = [], array $params = []): int
    {
        return $this->recordClass()::updateAll($data, $condition, $params);
    }

    /**
     * {@inheritDoc}
     */
    public function updateManyByIds(array $ids, array $data = [], array $params = []): int
    {
        return $this->recordClass()::updateAll($data, ['in', $this->primaryKeyCondition($ids)], $params);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteOneById($id)
    {
        return $this->deleteRecord(
            $this->findOneById($id)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function deleteOneBy($field, $value, string $operation = '=', array $params = [])
    {
        return $this->deleteRecord(
            $this->findOneBy($field, $value, $operation, $params)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function deleteOneByCondition(array $condition = [], array $params = [])
    {
        return $this->deleteRecord(
            $this->findOneByCondition($condition)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function deleteManyBy($field, $value, string $operation = '=', array $params = [])
    {
        return $this->recordClass()::deleteAll([$operation, $field, $value], $params);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteManyByCondition(array $condition = [])
    {
        return $this->recordClass()::deleteAll($condition);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteManyByIds(array $ids)
    {
        return $this->recordClass()::deleteAll(['in', $this->primaryKeyCondition($ids)]);
    }

    /**
     * @param db\ActiveRecord $record
     * @param array        $data
     * @param bool         $runValidation
     * @param array|null   $attributeNames
     * @return db\ActiveRecord
     * @throws \Throwable
     * @throws db\StaleObjectException
     * @throws Repository\Exception
     */
    protected function updateRecord(
        db\ActiveRecord $record,
        array $data,
        bool $runValidation = \true,
        array $attributeNames = \null
    ): db\ActiveRecord {
        $record->setAttributes($data);

        if (!$record->update($runValidation, $attributeNames)) {
            throw new Repository\Exception($this->exportErrorMessage($record));
        }

        return $record;
    }

    /**
     * @param db\ActiveRecord|null $record
     * @return false|int
     * @throws \Throwable
     * @throws db\StaleObjectException
     */
    protected function deleteRecord(?db\ActiveRecord $record)
    {
        return $record === \null ?: $record->delete();
    }

    /**
     * @param db\ActiveQuery $query
     * @return db\ActiveQuery
     */
    protected function apply(db\ActiveQuery $query): db\ActiveQuery
    {
        return $query->with($this->with)
            ->select($this->columns)
            ->orderBy($this->orderBy);
    }

    /**
     * @param db\ActiveQuery $query
     * @return db\ActiveRecord|null
     */
    protected function fetchOne(db\ActiveQuery $query): ?db\ActiveRecord
    {
        return $query->one($this->connection);
    }

    /**
     * @param db\ActiveQuery $query
     * @return array|db\ActiveRecord[]
     */
    protected function fetchMany(db\ActiveQuery $query): array
    {
        return $query->all($this->connection);
    }

    /**
     * @param array $ids
     * @return array
     */
    protected function primaryKeyCondition(array $ids): array
    {
        return \array_combine((array)$this->recordClass()::primaryKey(), $ids);
    }

    /**
     * @param db\ActiveRecord $record
     * @return string
     */
    protected function exportErrorMessage(db\ActiveRecord $record): string
    {
        return VarDumper::export($record->getErrorSummary(\true));
    }
}
