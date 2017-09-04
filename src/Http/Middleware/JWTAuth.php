<?php

namespace AtlassianConnectCore\Http\Middleware;

use AtlassianConnectCore\Services\TenantService;
use Illuminate\Support\Facades\Auth;

/**
 * Class JWTAuth
 *
 * @package AtlassianConnectCore\Http\Middleware
 */
class JWTAuth
{
    /**
     * @var TenantService
     */
    protected $tenantService;

    /**
     * JWTAuth constructor.
     *
     * @param TenantService $tenantService
     */
    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     *
     * @throws \Illuminate\Validation\UnauthorizedException
     *
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        // If we have add-on running locally we don't need to sign all requests with JWT token
        // Of course you can provide it if you want. Otherwise request will be signed automatically
        $jwt = request('jwt', request()->header('Authorization'));

        if(app()->isLocal() && !$jwt) {
            if(!$tenant = $this->tenantService->dummy()) {
                throw new \Illuminate\Validation\UnauthorizedException(
                    'You should have at least one dummy tenant to get it working locally'
                );
            }

            $jwt = \AtlassianConnectCore\Helpers\JWTHelper::create(
                $request->url(),
                $request->method(),
                $tenant->client_key,
                $tenant->shared_secret
            );

            $request->query->add(['jwt' => $jwt]);
        }

        // Authenticate user
        if(!Auth::attempt()) {
            throw new \Illuminate\Validation\UnauthorizedException();
        }

        return $next($request);
    }
}
