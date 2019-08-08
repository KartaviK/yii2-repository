<?php

namespace Kartavik\Yii2;

use mhndev\yii2Repository\Exceptions\RepositoryException;
use Yii;
use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Connection;
use yii\db\Query;

/**
 * Trait RepositoryTrait
 *
 * $posts = $postRepository->findManyBy('title','title5','like');
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
 */
trait RepositoryTrait
{



    /**
     * @param array $ids
     * @param bool  $withPagination
     * @param bool  $returnArray
     * @return mixed
     */
    public function findManyByIds(array $ids, $withPagination = true, $returnArray = false)
    {
        foreach ($this->with as $relation) {
            $this->query = $this->query->with($relation);
        }

        $this->initFetch($returnArray, $this->columns);
        $this->query = $this->query->where([self::PRIMARY_KEY => $ids])->orderBy($this->orderBy);

        return $withPagination ? $this->paginate() : $this->query->all();
    }


    /**
     * @param       $field
     * @param array $values
     * @param bool  $withPagination
     * @param bool  $returnArray
     * @return array
     */
    public function findManyWhereIn($field, array $values, $withPagination = true, $returnArray = false)
    {
        foreach ($this->with as $relation) {
            $this->query = $this->query->with($relation);
        }

        $this->initFetch($returnArray, $this->columns);
        $this->query = $this->query->where([$field => $values])->orderBy($this->orderBy);

        return $withPagination ? $this->paginate() : $this->query->all();
    }




    /**
     * @param $returnArray
     * @param $columns
     */
    protected function initFetch($returnArray, $columns)
    {
        if ($columns != ['*']) {
            $this->query->select($columns);
        }

        if ($returnArray) {
            $this->query->asArray();
        }
    }

    /**
     * @param bool $withPagination
     * @param bool $returnArray
     * @return mixed
     */
    public function findAll($withPagination = true, $returnArray = false)
    {
        foreach ($this->with as $relation) {
            $this->query = $this->query->with($relation);
        }

        $this->initFetch($returnArray, $this->columns);

        $this->query = $this->query->orderBy($this->orderBy);

        return $withPagination ? $this->paginate() : $this->query->all();
    }

    /**
     * @param array $criteria
     * @param bool  $withPagination
     * @param array $with
     * @param bool  $returnArray
     * @return mixed
     */
    public function findManyByCriteria(array $criteria = [], $withPagination = true, $with = [], $returnArray = false)
    {
        if (depth($criteria) > 1) {
            array_unshift($criteria, 'and');
        }

        foreach ($this->with as $relation) {
            $this->query = $this->query->with($relation);
        }

        $this->initFetch($returnArray, $this->columns);
        $this->query = $this->query->where($criteria)->orderBy($this->orderBy);


        return $withPagination ? $this->paginate() : $this->query->all();
    }

    /**
     * @param     $id
     * @param     $field
     * @param int $count
     */
    public function inc($id, $field, $count = 1)
    {
        $entity = $this->query->where([self::PRIMARY_KEY => $id])->one();

        $entity->updateCounters([$field => $count]);
    }

    /**
     * @param     $id
     * @param     $field
     * @param int $count
     */
    public function dec($id, $field, $count = -1)
    {
        $entity = $this->query->where([self::PRIMARY_KEY => $id])->one();

        $entity->updateCounters([$field => $count]);
    }

    /**
     * @param ActiveRecord $entity
     * @param array        $data
     * @return ActiveRecord
     */
    protected function updateEntity(ActiveRecord $entity, array $data)
    {
        $entity->setAttributes($data);
        $entity->save();

        return $entity;
    }

    /**
     * @param       $id
     * @param array $data
     * @return mixed
     */
    public function updateOneById($id, array $data = [])
    {
        $entity = $this->query->where([self::PRIMARY_KEY => $id])->limit(1)->one();

        return $this->updateEntity($entity, $data);

    }

    /**
     * @param       $key
     * @param       $value
     * @param array $data
     * @return mixed
     */
    public function updateOneBy($key, $value, array $data = [])
    {
        $entity = $this->query->where([$key => $value])->limit(1)->one();

        return $this->updateEntity($entity, $data);

    }

    /**
     * @param array $criteria
     * @param array $data
     * @return mixed
     */
    public function updateOneByCriteria(array $criteria, array $data = [])
    {
        $entity = $this->query->where($criteria)->one();

        return $this->updateEntity($entity, $data);
    }


    /**
     * @param        $key
     * @param        $value
     * @param array  $data
     * @param string $operation
     * @return int number of records updated
     */
    public function updateManyBy($key, $value, array $data = [], $operation = '=')
    {
        return $this->model->updateAll($data, [$operation, $key, $value]);
    }

    /**
     * @param array $criteria
     * @param array $data
     * @return int number of records updated
     */
    public function updateManyByCriteria(array $criteria = [], array $data = [])
    {
        if (depth($criteria) > 1) {
            array_unshift($criteria, 'and');
        }

        return $this->model->updateAll($data, $criteria);
    }

    /**
     * @param array $ids
     * @param array $data
     * @return int number of records updated
     */
    public function updateManyByIds(array $ids, array $data = [])
    {
        return $this->model->updateAll($data, ['in', self::PRIMARY_KEY, $ids]);
    }


    /**
     * @param array $ids
     * @return bool
     */
    public function allExist(array $ids)
    {
        // TODO: Implement allExist() method.
    }

    /**
     * @param $id
     * @return boolean|integer number of rows deleted
     */
    public function deleteOneById($id)
    {
        $entity = $this->model->findOne([self::PRIMARY_KEY => $id]);

        return $entity->delete();
    }


    /**
     * @param        $key
     * @param        $value
     * @param string $operation
     * @return bool|int number of rows deleted
     */
    public function deleteOneBy($key, $value, $operation = '=')
    {
        $entity = $this->model->findOne([$operation, $key, $value]);

        return $entity->delete();
    }

    /**
     * @param array $criteria
     * @return boolean|integer number of rows deleted
     */
    public function deleteOneByCriteria(array $criteria = [])
    {
        $entity = $this->model->findOne($criteria);

        return $entity->delete();
    }

    /**
     * @param        $key
     * @param        $value
     * @param string $operation
     * @return bool|int number of rows deleted
     */
    public function deleteManyBy($key, $value, $operation = '=')
    {
        return $this->model->deleteAll([$operation, $key, $value]);
    }

    /**
     * @param array $criteria
     * @return boolean|integer number of rows deleted
     */
    public function deleteManyByCriteria(array $criteria = [])
    {
        if (depth($criteria) > 1) {
            array_unshift($criteria, 'and');
        }

        return $this->model->deleteAll($criteria);
    }


    /**
     * @param array $ids
     * @return boolean|integer number of rows deleted
     */
    public function deleteManyByIds(array $ids)
    {
        return $this->model->deleteAll(['in', self::PRIMARY_KEY, $ids]);
    }


    /**
     * @return mixed
     */
    public function searchByCriteria()
    {
        $search = !empty($_GET['search']) ? explode(',', $_GET['search']) : null;

        if (!empty($_GET['fields'])) {
            $fields = explode(',', $_GET['fields']);
            $this->columns($fields);
        }

        if (!empty($perPage)) {
            $this->limit($perPage);
        }

        if (!empty($_GET['with'])) {
            $with = explode(',', $_GET['with']);
            $this->with($with);
        }


        if (!empty($_GET['perPage'])) {
            $this->limit($_GET['perPage']);
        }

        if (!empty($_GET['page'])) {
            $this->offset($_GET['page'] * $this->limit);
        }


        if (!empty($search)) {
            $criteria = [];
            foreach ($search as $string) {
                $components = explode(':', $string);

                array_push($criteria, [$components[1], $components[0], $components[2]]);
            }

            return $this->findManyByCriteria($criteria);

        } else {
            return $this->findAll();
        }

    }


}
