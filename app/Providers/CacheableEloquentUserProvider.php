<?php
namespace App\Providers;


use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Support\Facades\Cache;

class CacheableEloquentUserProvider extends EloquentUserProvider {
    /**
     * Retrieve a user by their unique identifier.
     *  - override -
     *  with using cache.
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        return Cache::remember($this->getModel() . '_By_Id_' . $identifier, 60,
            function() use ($identifier) {
                return $this->createModel()->newQuery()->find($identifier);
            }
        );
    }
    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *  - override -
     *  with using cache.
     *
     * @param  mixed  $identifier
     * @param  string  $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        $model = $this->createModel();
        return Cache::remember($this->getModel() . '_By_Id_Token_' . $identifier, 60,
            function() use ($model, $identifier, $token) {
                return $model->newQuery()
                    ->where($model->getAuthIdentifierName(), $identifier)
                    ->where($model->getRememberTokenName(), $token)
                    ->first();
            }
        );
    }
    
    public static function clearCache($model)
    {
        Cache::forget(get_class($model) . '_By_Id_' . $model->id);
        Cache::forget(get_class($model) . '_By_Id_Token_' . $model->id);
    }
}