<?php

namespace AtlassianConnectCore\Repositories;

use AtlassianConnectCore\Models\Tenant;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class TenantRepository
 *
 * @package AtlassianConnectCore\Repositories
 */
class TenantRepository
{
    /**
     * Create a query instance
     *
     * @param bool $withTrashed
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function query($withTrashed = true)
    {
        $query = Tenant::query();

        if($withTrashed) {
            $query->withTrashed();
        }

        return $query;
    }

    /**
     * Find by ID
     *
     * @param int $id
     *
     * @return Tenant|null
     */
    public function findById($id)
    {
        /** @var Tenant|null $model */
        $model = $this->query()
            ->find($id);

        return $model;
    }

    /**
     * Find all tenants
     *
     * @param bool $withTrashed
     *
     * @return \Illuminate\Database\Eloquent\Collection|Tenant[]
     */
    public function findAll($withTrashed = false)
    {
        return $this->query($withTrashed)
            ->latest()
            ->get();
    }

    /**
     * Find by ID
     *
     * @param array $condition
     * @param bool $withTrashed
     *
     * @return \Illuminate\Database\Eloquent\Collection|Tenant[]
     */
    public function findWhere(array $condition, $withTrashed = true)
    {
        return $this->query($withTrashed)
            ->where($condition)
            ->get();
    }

    /**
     * Find dummy tenant
     *
     * @return Tenant|null
     */
    public function findDummy()
    {
        /** @var Tenant|null $model */
        $model = $this->query(false)
            ->where(['is_dummy' => true])
            ->latest()
            ->first();

        return $model;
    }

    /**
     * Create a tenant
     *
     * @param array $attributes
     *
     * @return Tenant
     */
    public function create(array $attributes)
    {
        $model = (new Tenant())->fill($attributes);

        $model->save();

        return $model;
    }

    /**
     * Update a tenant
     *
     * @param int $id
     * @param array $attributes
     *
     * @return Tenant
     */
    public function update($id, array $attributes)
    {
        $model = $this->findById($id);

        if(!$model) {
            throw new NotFoundHttpException();
        }

        $model->update($attributes);

        return $model;
    }

    /**
     * Update a tenant by addon key
     *
     * @param string $addonKey
     * @param array $attributes
     *
     * @return Tenant
     */
    public function updateByAddonKey($addonKey, array $attributes)
    {
        $model = $this->findWhere(['addon_key' => $addonKey])->first();

        if(!$model) {
            throw new NotFoundHttpException();
        }

        return $this->update($model->id, $attributes);
    }

    /**
     * Update a tenant by addon key
     *
     * @param array $condition
     * @param array $attributes
     *
     * @return Tenant
     */
    public function updateWhere(array $condition, array $attributes)
    {
        $model = $this->findWhere($condition)->first();

        if(!$model) {
            throw new NotFoundHttpException();
        }

        return $this->update($model->id, $attributes);
    }

    /**
     * Delete a tenant by ID
     *
     * @param int $id
     *
     * @return mixed
     */
    public function delete($id)
    {
        if($model = $this->findById($id)) {
            $model->delete();
        }
    }

    /**
     * Force delete a tenant by ID
     *
     * @param int $id
     *
     * @return mixed
     */
    public function forceDelete($id)
    {
        if($model = $this->findById($id)) {
            $model->forceDelete();
        }
    }
}