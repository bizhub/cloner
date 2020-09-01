<?php

namespace Bizhub\Cloner\Traits;

trait Cloneable
{
    /**
     * Get cloner
     *
     * @return \Bizhub\Cloner\Cloner
     */
    public function getCloner()
    {
        return resolve('cloner');
    }

    /**
     * Clone a model
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function clone($model)
    {
        return $this->getCloner()->clone($model, null, true);
    }

    /**
     * Get cloneable relationships
     * 
     * @return array
     */
    public function getCloneableRelations()
    {
        if ( ! isset($this->cloneableRelations)) return [];

        return $this->cloneableRelations;
    }

    /**
     * Set clone attributes
     *
     * @param \Illuminate\Database\Eloquent\Model $clone
     * @return void
     */
    public function cloneAttributes($clone)
    {
        return;
    }
}