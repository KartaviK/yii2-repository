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

/**
 * Interface RepositoryInterface
 * @package Kartavik\Yii2
 */
interface RepositoryInterface
{
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
     * @param mixed $id
     * @return array|mixed
     * @throws db\Exception
     */
    public function findOneById($id);

    /**
     * @param mixed $condition
     * @param array $params
     * @return array|mixed
     * @throws db\Exception
     */
    public function findOneBy($condition, array $params = []);

    /**
     * @param mixed $condition
     * @param array $params
     * @return array|mixed
     * @throws db\Exception
     */
    public function findManyBy($condition, array $params = []);


    /**
     * @param array $ids
     * @param bool  $withPagination
     * @param bool  $returnArray
     * @return mixed
     */
    public function findManyByIds(array $ids, $withPagination = true, $returnArray = false);

    /**
     * @param bool $withPagination
     * @param bool $returnArray
     * @return mixed
     * @internal param $ $
     */
    public function findAll($withPagination = true, $returnArray = false);

    /**
     * @param array $criteria
     * @param bool  $withPagination
     * @param array $with
     * @param $
     * @param bool  $returnArray
     * @return mixed
     */
    public function findManyByCriteria(array $criteria = [], $withPagination = true, $with = [], $returnArray = false);


    /**
     * @param       $id
     * @param array $data
     * @return boolean
     */
    public function updateOneById($id, array $data = []): bool;

    /**
     * @param       $key
     * @param       $value
     * @param array $data
     * @return boolean
     */
    public function updateOneBy($key, $value, array $data = []): bool;

    /**
     * @param array $criteria
     * @param array $data
     * @return boolean
     */
    public function updateOneByCriteria(array $criteria, array $data = []): bool;

    /**
     * @param        $key
     * @param        $value
     * @param array  $data
     * @param string $operation
     * @return bool
     */
    public function updateManyBy($key, $value, array $data = [], $operation = '='): bool;

    /**
     * @param array $criteria
     * @param array $data
     * @return boolean
     */
    public function updateManyByCriteria(array $criteria = [], array $data = []): bool;


    /**
     * @param array $ids
     * @param array $data
     * @return bool
     */
    public function updateManyByIds(array $ids, array $data = []): bool;

    /**
     * @param $id
     * @return boolean
     */
    public function deleteOneById($id): bool;


    /**
     * @param array $ids
     * @return bool
     */
    public function allExist(array $ids): bool;

    /**
     * @param        $key
     * @param        $value
     * @param string $operation
     * @return bool
     */
    public function deleteOneBy($key, $value, $operation = '='): bool;

    /**
     * @param array $criteria
     * @return boolean
     */
    public function deleteOneByCriteria(array $criteria = []): bool;

    /**
     * @param        $key
     * @param        $value
     * @param string $operation
     * @return bool
     */
    public function deleteManyBy($key, $value, $operation = '='): bool;

    /**
     * @param array $criteria
     * @return boolean
     */
    public function deleteManyByCriteria(array $criteria = []): bool;

    /**
     * @return mixed
     */
    public function searchByCriteria();


    /**
     * @param array $ids
     * @return mixed
     */
    public function deleteManyByIds(array $ids);


    /**
     * @param     $id
     * @param     $field
     * @param int $count
     * @return
     */
    public function inc($id, $field, $count = 1);

    /**
     * @param     $id
     * @param     $field
     * @param int $count
     * @return
     */
    public function dec($id, $field, $count = 1);
}
