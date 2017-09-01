<?php

namespace AtlassianConnectCore\Services;

use AtlassianConnectCore\Models\Tenant;
use AtlassianConnectCore\Repositories\TenantRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class TenantService
 *
 * @package AtlassianConnectCore\Services
 */
class TenantService
{
    /**
     * @var TenantRepository
     */
    protected $repository;

    /**
     * TenantService constructor.
     *
     * @param TenantRepository $tenantRepository
     */
    public function __construct(TenantRepository $tenantRepository)
    {
        $this->repository = $tenantRepository;
    }

    /**
     * Create or update a tenant
     *
     * @param bool $withTrashed
     *
     * @return \Illuminate\Database\Eloquent\Collection|Tenant[]
     */
    public function all($withTrashed = false)
    {
        return $this->repository->findAll($withTrashed);
    }

    /**
     * Create or update a tenant
     *
     * @param array $attributes
     *
     * @return Tenant
     */
    public function createOrUpdate(array $attributes)
    {
        /** @var Tenant|null $model */
        $model = $this->repository
            ->findWhere(['client_key' => $attributes['client_key']])
            ->first();

        if(!$model) {
            return $this->repository->create($attributes);
        }

        return $this->repository->update($model->id, $attributes);
    }

    /**
     * Update state
     *
     * @param string $clientKey
     * @param string $eventType
     *
     * @return Tenant
     */
    public function updateState($clientKey, $eventType)
    {
        return $this->repository->updateWhere(['client_key' => $clientKey], ['event_type' => $eventType]);
    }

    /**
     * Find a tenant by client key
     *
     * @param string $clientKey
     * @param bool $withTrashed
     *
     * @return Tenant|null
     */
    public function findByClientKey($clientKey, $withTrashed = true)
    {
        return $this->repository
            ->findWhere(['client_key' => $clientKey], $withTrashed)
            ->first();
    }

    /**
     * Find a tenant by client key
     *
     * @param string $clientKey
     * @param bool $withTrashed
     *
     * @return Tenant
     *
     * @throws NotFoundHttpException
     */
    public function findByClientKeyOrFail($clientKey, $withTrashed = true)
    {
        if($tenant = $this->findByClientKey($clientKey, $withTrashed)) {
            return $tenant;
        }

        throw new NotFoundHttpException();
    }

    /**
     * Find not-dummied tenants
     *
     * @return \Illuminate\Database\Eloquent\Collection|Tenant[]
     */
    public function findReals()
    {
        return $this->repository->findWhere(['is_dummy' => false], false);
    }

    /**
     * Delete the tenant
     *
     * @param int $id
     *
     * @return mixed
     */
    public function delete($id)
    {
        if(config('plugin.safeDelete')) {
            return $this->repository->delete($id);
        }

        return $this->repository->forceDelete($id);
    }

    /**
     * Get dummy tenant
     *
     * @return Tenant|null
     */
    public function dummy()
    {
        return $this->repository->findDummy();
    }

    /**
     * Make tenant dummied
     *
     * @param int $id Tenant ID
     *
     * @return Tenant|null
     */
    public function makeDummy($id)
    {
        if(!$this->repository->findById($id)) {
            throw new NotFoundHttpException();
        }

        return $this->repository->updateWhere(['id' => $id], ['is_dummy' => true]);
    }
}