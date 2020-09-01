<?php

namespace Bizhub\Cloner;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Cloner
{
    /**
     * Cloned models
     *
     * @var array
     */
    protected $cloned = [];

    /**
     * Ignore circular models
     * 
     * @var boolean
     */
    protected $ignoreCircular = false;

    /**
     * Clone a model
     *
     * @param Model $model
     * @param Model|null $relation
     * @param boolean $circular
     * @return Model
     */
    public function clone(Model $model, $relation = null, $circular = false)
    {
        if ($this->isCloned($model)) {
            return $this->getCloned($model);
        }

        if ($this->ignoreCircular && $circular) {
            return $model;
        }

        $clone = $this->cloneModel($model);

        if ($relation) {
            $relation->save($clone);
        } else {
            $clone->save();
        }

        $this->addCloned($model, $clone);

        $this->cloneRelations($model, $clone);
        
        return $clone;
    }

    /**
     * Partial clone
     *
     * @param Model $model
     * @return Model
     */
    public function partialClone(Model $model)
    {
        $this->setCircular(true);

        return $this->clone($model);
    }

    /**
     * Clone a model
     *
     * @param Model $model
     * @return Model
     */
    protected function cloneModel(Model $model)
    {
        $clone = $model->replicate();

        if (method_exists($model, 'cloneAttributes')) {
            $model->cloneAttributes($clone);
        }

        return $clone;
    }

    /**
     * Clone relationships
     * 
     * @param Model $model
     * @param Model $clone
     * @return Cloner
     */
    protected function cloneRelations($model, $clone)
    {
        if (method_exists($model, 'getCloneableRelations')) {
            $relationNames = $model->getCloneableRelations();

            foreach ($relationNames as $relationName) {
                $customMethodName = 'clone' . Str::of($relationName)->camel()->ucfirst() . 'Relation';

                if (method_exists($model, $customMethodName)) {
                    $model->$customMethodName($clone);
                } else {
                    $this->cloneRelation($model, $relationName, $clone);
                }
            }
        }

        return $this;
    }

    /**
     * Clone a relation
     *
     * @param Model $model
     * @param string $relationName
     * @param Model $clone
     * @return Cloner
     */
    protected function cloneRelation($model, $relationName, $clone)
    {
        $relation = $model->$relationName();

        if (is_a($relation, 'Illuminate\Database\Eloquent\Relations\HasMany')) {
            $relation->get()->each(function($related) use($clone, $relationName){
                $this->clone($related, $clone->$relationName());
            });
        }

        return $this;
    }

    /**
     * Set ignore circular
     * 
     * @param boolean $value
     * @return Cloner
     */
    public function setCircular($value)
    {
        $this->ignoreCircular = $value;

        return $this;
    }

    /**
     * Is cloned
     *
     * @param Model $model
     * @return boolean
     */
    public function isCloned(Model $model)
    {
        return isset($this->cloned[get_class($model)][$model->id]);
    }

    /**
     * Get cloned model
     *
     * @param Model $model
     * @return Model|null
     */
    public function getCloned(Model $model)
    {
        return $this->cloned[get_class($model)][$model->id];
    }

    /**
     * Add/register cloned model
     *
     * @param Model $originalModel
     * @param Model $clonedModel
     * @return void
     */
    protected function addCloned(Model $originalModel, Model $clonedModel)
    {
        $this->cloned[get_class($originalModel)][$originalModel->id] = $clonedModel;
    }
}