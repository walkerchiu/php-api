<?php

namespace WalkerChiu\API\Models\Repositories;

use Illuminate\Support\Facades\App;
use WalkerChiu\Core\Models\Forms\FormHasHostTrait;
use WalkerChiu\Core\Models\Repositories\Repository;
use WalkerChiu\Core\Models\Repositories\RepositoryHasHostTrait;
use WalkerChiu\Core\Models\Services\PackagingFactory;

class SettingRepository extends Repository
{
    use FormHasHostTrait;
    use RepositoryHasHostTrait;

    protected $instance;



    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->instance = App::make(config('wk-core.class.api.setting'));
    }

    /**
     * @param String  $host_type
     * @param String  $host_id
     * @param String  $code
     * @param Array   $data
     * @param Bool    $is_enabled
     * @param String  $target
     * @param Bool    $target_is_enabled
     * @param Bool    $auto_packing
     * @return Array|Collection|Eloquent
     */
    public function list(?string $host_type, ?string $host_id, string $code, array $data, $is_enabled = null, $target = null, $target_is_enabled = null, $auto_packing = false)
    {
        if (
            empty($host_type)
            || empty($host_id)
        ) {
            $instance = $this->instance;
        } else {
            $instance = $this->baseQueryForRepository($host_type, $host_id, $target, $target_is_enabled);
        }
        if ($is_enabled === true)      $instance = $instance->ofEnabled();
        elseif ($is_enabled === false) $instance = $instance->ofDisabled();

        $data = array_map('trim', $data);
        $repository = $instance->with(['langs' => function ($query) use ($code) {
                                $query->ofCurrent()
                                      ->ofCode($code);
                                }])
                                ->whereHas('langs', function ($query) use ($code) {
                                    return $query->ofCurrent()
                                                 ->ofCode($code);
                                })
                                ->when($data, function ($query, $data) {
                                    return $query->unless(empty($data['id']), function ($query) use ($data) {
                                                return $query->where('id', $data['id']);
                                            })
                                            ->unless(empty($data['type']), function ($query) use ($data) {
                                                return $query->where('type', $data['type']);
                                            })
                                            ->unless(empty($data['serial']), function ($query) use ($data) {
                                                return $query->where('serial', $data['serial']);
                                            })
                                            ->unless(empty($data['app_id']), function ($query) use ($data) {
                                                return $query->where('app_id', $data['app_id']);
                                            })
                                            ->unless(empty($data['app_token']), function ($query) use ($data) {
                                                return $query->where('app_token', $data['app_token']);
                                            })
                                            ->unless(empty($data['app_key']), function ($query) use ($data) {
                                                return $query->where('app_key', $data['app_key']);
                                            })
                                            ->unless(empty($data['function_id']), function ($query) use ($data) {
                                                return $query->where('function_id', $data['function_id']);
                                            })
                                            ->unless(empty($data['url_notify']), function ($query) use ($data) {
                                                return $query->where('url_notify', 'LIKE', "%".$data['url_notify']."%");
                                            })
                                            ->unless(empty($data['url_return']), function ($query) use ($data) {
                                                return $query->where('url_return', 'LIKE', "%".$data['url_return']."%");
                                            })
                                            ->unless(empty($data['url_success']), function ($query) use ($data) {
                                                return $query->where('url_success', 'LIKE', "%".$data['url_success']."%");
                                            })
                                            ->unless(empty($data['url_cancel']), function ($query) use ($data) {
                                                return $query->where('url_cancel', 'LIKE', "%".$data['url_cancel']."%");
                                            })
                                            ->unless(empty($data['name']), function ($query) use ($data) {
                                                return $query->whereHas('langs', function ($query) use ($data) {
                                                    $query->ofCurrent()
                                                          ->where('key', 'name')
                                                          ->where('value', 'LIKE', "%".$data['name']."%");
                                                });
                                            })
                                            ->unless(empty($data['description']), function ($query) use ($data) {
                                                return $query->whereHas('langs', function ($query) use ($data) {
                                                    $query->ofCurrent()
                                                          ->where('key', 'description')
                                                          ->where('value', 'LIKE', "%".$data['description']."%");
                                                });
                                            })
                                            ->unless(empty($data['remarks']), function ($query) use ($data) {
                                                return $query->whereHas('langs', function ($query) use ($data) {
                                                    $query->ofCurrent()
                                                          ->where('key', 'remarks')
                                                          ->where('value', 'LIKE', "%".$data['remarks']."%");
                                                });
                                            });
                                    })
                                ->orderBy('updated_at', 'DESC');

        if ($auto_packing) {
            $factory = new PackagingFactory(config('wk-api.output_format'), config('wk-api.pagination.pageName'), config('wk-api.pagination.perPage'));
            $factory->setFieldsLang(['name', 'description', 'remarks']);
            return $factory->output($repository);
        }

        return $repository;
    }

    /**
     * @param Setting       $instance
     * @param String|Array  $code
     * @return Array
     */
    public function show($instance, $code): array
    {
        $data = [
            'id' => $instance ? $instance->id : '',
            'basic' => []
        ];

        if (empty($instance))
            return $data;

        $this->setEntity($instance);

        if (is_string($code)) {
            $data['basic'] = [
                  'host_type'   => $instance->host_type,
                  'host_id'     => $instance->host_id,
                  'serial'      => $instance->serial,
                  'type'        => $instance->type,
                  'app_id'      => $instance->app_id,
                  'app_key'     => $instance->app_key,
                  'app_secret'  => $instance->app_secret,
                  'hash_key'    => $instance->hash_key,
                  'hash_iv'     => $instance->hash_iv,
                  'url_notify'  => $instance->url_notify,
                  'url_return'  => $instance->url_return,
                  'url_success' => $instance->url_success,
                  'url_cancel'  => $instance->url_cancel,
                  'name'        => $instance->findLang($code, 'name'),
                  'description' => $instance->findLang($code, 'description'),
                  'remarks'     => $instance->findLang($code, 'remarks'),
                  'is_enabled'  => $instance->is_enabled,
                  'updated_at'  => $instance->updated_at
            ];

        } elseif (is_array($code)) {
            foreach ($code as $language) {
                $data['basic'][$language] = [
                      'host_type'   => $instance->host_type,
                      'host_id'     => $instance->host_id,
                      'serial'      => $instance->serial,
                      'type'        => $instance->type,
                      'app_id'      => $instance->app_id,
                      'app_key'     => $instance->app_key,
                      'app_secret'  => $instance->app_secret,
                      'hash_key'    => $instance->hash_key,
                      'hash_iv'     => $instance->hash_iv,
                      'url_notify'  => $instance->url_notify,
                      'url_return'  => $instance->url_return,
                      'url_success' => $instance->url_success,
                      'url_cancel'  => $instance->url_cancel,
                      'name'        => $instance->findLang($language, 'name'),
                      'description' => $instance->findLang($language, 'description'),
                      'remarks'     => $instance->findLang($language, 'remarks'),
                      'is_enabled'  => $instance->is_enabled,
                      'updated_at'  => $instance->updated_at
                ];
            }
        }

        return $data;
    }
}
