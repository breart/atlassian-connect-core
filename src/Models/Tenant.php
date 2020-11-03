<?php

namespace AtlassianConnectCore\Models;

use DateTimeInterface;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Tenant
 *
 * @property string $id
 * @property string $addon_key
 * @property string $client_key
 * @property string $oauth_client_token
 * @property string $public_key
 * @property string $shared_secret
 * @property string $server_version
 * @property string $base_url
 * @property string $plugin_version
 * @property string $product_type
 * @property string $description
 * @property string $event_type
 * @property string $remember_token
 * @property bool $is_dummy
 * @property \Carbon\Carbon $deleted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @package AtlassianConnectCore\Models
 */
class Tenant extends Model implements \Illuminate\Contracts\Auth\Authenticatable
{
    use Authenticatable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'addon_key',
        'client_key',
        'public_key',
        'oauth_client_token',
        'shared_secret',
        'server_version',
        'plugin_version',
        'base_url',
        'product_type',
        'description',
        'event_type',
        'is_dummy'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_dummy' => 'bool'
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'is_dummy' => false
    ];

    /**
     * Tenant constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTable(config('plugin.tenant'));
    }

    /**
     * Checks whether Tenant's product type is JIRA
     *
     * @return bool
     */
    public function isJira()
    {
        return $this->product_type === 'jira';
    }

    /**
     * Checks whether Tenant's product type is Confluence
     *
     * @return bool
     */
    public function isConfluence()
    {
        return $this->product_type === 'confluence';
    }

    /**
     * Checks whether Tenant's add-on is installed
     *
     * @return bool
     */
    public function isInstalled()
    {
        return !$this->isUninstalled();
    }

    /**
     * Checks whether Tenant's add-on is uninstalled
     *
     * @return bool
     */
    public function isUninstalled()
    {
        return $this->event_type === 'uninstalled';
    }

    /**
     * Checks whether Tenant's add-on is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->event_type === 'enabled';
    }

    /**
     * Checks whether Tenant's add-on is disabled
     *
     * @return bool
     */
    public function isDisabled()
    {
        return $this->event_type === 'disabled';
    }

    /**
     * Checks whether Tenant is dummy
     *
     * @return bool
     */
    public function isDummy()
    {
        return $this->is_dummy;
    }

    /**
     * Checks whether tenant is safely deleted
     *
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deleted_at !== null;
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
