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

use Kartavik\Yii2\Repository\Exception;
use Kartavik\Yii2\Repository\Sort;
use mhndev\yii2Repository\Exceptions\RepositoryException;
use yii\base\Component;
use yii\data\Pagination;
use yii\db;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\di;
use yii\helpers\VarDumper;

/**
 * Class Repository
 *
 * ```php
 * $postRepository->findManyBy(
 *      'title',
 *      'title5',
 *      'like'
 * );
 * ```
 *
 * $posts = $postRepository->findManyBy('title','title5');
 *
 * $posts = $postRepository->findManyByIds([1,2,3]);
 *
 * $posts = $postRepository->findManyWhereIn('text',['text1','text2']);
 *
 * $posts = $postRepository->findManyByCriteria([
 *           ['like', 'title','title'] , ['=','text','text1']
 * ]);
 *
 * $posts = $postRepository->findOneById(2);
 *
 * $postRepository->updateOneById(2, ['title'=>'new new']);
 *
 * $postRepository->updateManyByIds([1,2,3], ['title'=>'new new new']);
 *
 * $postRepository->updateManyBy('title','salam', ['text'=>'sssssssssss'], 'like');
 *
 * $postRepository->updateManyByCriteria([['like','title','salam'],['like','text','text2']], ['text'=>'salam']);
 *
 * $postRepository->deleteManyByIds([2,3]);
 *
 * $postRepository->deleteManyBy('title','title5','like');
 *
 * $posts = $postRepository->findManyWhereIn('title',['salam','salam2'], false);
 *
 * @package Kartavik\Yii2
 * @since 2.0
 */
class Repository extends Component implements RepositoryInterface
{
    use RepositoryTrait;

    public const PRIMARY_KEY = 'id';

    /** @var db\Connection|string|array */
    public $connection = db\Connection::class;

    /** @var db\ActiveRecord|string|array */
    public $record = db\ActiveRecord::class;

    /**
     * @var ActiveRecord
     */
    protected $model;

    /**
     * @var Query
     */
    protected $query;

    /**
     * @var array
     */
    protected $with = [];

    /**
     * @var array
     */
    protected $columns = ['*'];

    /**
     * @var array|string
     */
    protected $orderBy = [];

    /**
     * @var int
     */
    protected $limit = 10;

    /**
     * @var int
     */
    protected $offset = 0;

    /** @var int */
    protected $fetchMode = \PDO::FETCH_ASSOC;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init(): void
    {
        $this->connection = di\Instance::ensure($this->connection, db\Connection::class);
        $this->connection = di\Instance::ensure($this->record, db\ActiveRecord::class);

        foreach ((array)$this->record::primaryKey() as $key) {
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

    public function fetchMode($fetchMode = \PDO::FETCH_ASSOC): RepositoryInterface
    {
        $this->fetchMode = $fetchMode;

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
    public function create(iterable $data = []): ActiveRecord
    {
        /** @var ActiveRecord $record */
        $record = new $this->record();

        foreach ($data as $property => $value) {
            $record->setAttribute($property, $value);
        }

        if (!$record->save()) {
            throw new Repository\Exception($this->formErrorMessage($record));
        }

        return $record;
    }

    /**
     * {@inheritDoc}
     */
    public function make(iterable $data = []): ActiveRecord
    {
        /** @var ActiveRecord $record */
        $record = new $this->record();

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
        /** @var ActiveRecord[] $batch */
        $batch = [];

        foreach ($records as $recordData) {
            $batch[] = new $this->record((array)$recordData);
        }

        foreach ($batch as $record) {
            if (!$record->save($runValidation)) {
                throw new Repository\Exception($this->formErrorMessage($record));
            }
        }

        return $batch;
    }

    /**
     * {@inheritDoc}
     */
    public function findOneById($id)
    {
        return $this->fetchOne(
            $this->apply($this->record::find())
                ->where(
                    \array_combine((array)$this->record::primaryKey(), \is_array($id) ? $id : [$id])
                )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function findOneBy($condition, array $params = [])
    {
        return $this->fetchOne(
            $this->apply($this->record::find())
                ->where($condition, $params)
        );
    }

    public function findManyBy($condition, array $params = [])
    {
        return $this->fetchMany(
            $this->apply($this->record::find())
                ->where($condition, $params)
        );
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
     * @return array|false
     * @throws db\Exception
     */
    protected function fetchOne(db\ActiveQuery $query)
    {
        return $query->createCommand($this->connection)->queryOne($this->fetchMode);
    }

    /**
     * @param db\ActiveQuery $query
     * @return array
     * @throws db\Exception
     */
    protected function fetchMany(db\ActiveQuery $query)
    {
        return $query->createCommand($this->connection)->queryAll($this->fetchMode);
    }

    protected function formErrorMessage(ActiveRecord $record): string
    {
        return VarDumper::export($record->getErrorSummary(\true));
    }
}
