<?php

namespace WalkerChiu\API\Models\Repositories;

use Illuminate\Support\Facades\App;
use WalkerChiu\Core\Models\Forms\FormHasHostTrait;
use WalkerChiu\Core\Models\Repositories\Repository;
use WalkerChiu\Core\Models\Repositories\RepositoryHasHostTrait;

class SettingRepository extends Repository
{
    use FormHasHostTrait;
    use RepositoryHasHostTrait;

    protected $entity;
    protected $morphType;

    public function __construct()
    {
        $this->entity = App::make(config('wk-core.class.api.setting'));
    }

    /**
     * @param String  $host_type
     * @param String  $host_id
     * @param String  $code
     * @param Array   $data
     * @param Int     $page
     * @param Int     $nums per page
     * @param Boolean $is_enabled
     * @param String  $target
     * @param Boolean $target_is_enabled
     * @param Boolean $toArray
     * @return Array|Collection
     */
    public function list($host_type, $host_id, String $code, Array $data, $page = null, $nums = null, $is_enabled = null, $target = null, $target_is_enabled = null, $toArray = true)
    {
        $this->assertForPagination($page, $nums);

        if (empty($host_type) || empty($host_id)) {
            $entity = $this->entity;
        } else {
            $entity = $this->baseQueryForRepository($host_type, $host_id, $target, $target_is_enabled);
        }
        if ($is_enabled === true)      $entity = $entity->ofEnabled();
        elseif ($is_enabled === false) $entity = $entity->ofDisabled();

        $data = array_map('trim', $data);
        $records = $entity->with(['langs' => function ($query) use ($code) {
                              $query->ofCurrent()
                                    ->ofCode($code);
                            }])
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
                                          return $query->whereHas('langs', function($query) use ($data) {
                                              $query->ofCurrent()
                                                    ->where('key', 'name')
                                                    ->where('value', 'LIKE', "%".$data['name']."%");
                                          });
                                      })
                                      ->unless(empty($data['description']), function ($query) use ($data) {
                                          return $query->whereHas('langs', function($query) use ($data) {
                                              $query->ofCurrent()
                                                    ->where('key', 'description')
                                                    ->where('value', 'LIKE', "%".$data['description']."%");
                                          });
                                      })
                                      ->unless(empty($data['remarks']), function ($query) use ($data) {
                                          return $query->whereHas('langs', function($query) use ($data) {
                                              $query->ofCurrent()
                                                    ->where('key', 'remarks')
                                                    ->where('value', 'LIKE', "%".$data['remarks']."%");
                                          });
                                      });
                            })
                          ->orderBy('updated_at', 'DESC')
                          ->get()
                          ->when(is_integer($page) && is_integer($nums), function ($query) use ($page, $nums) {
                              return $query->forPage($page, $nums);
                          });
        if ($toArray) {
            $list = [];
            foreach ($records as $record) {
                $data = $record->toArray();
                array_push($list,
                    array_merge($data, [
                        'name'        => $record->findLangByKey('name'),
                        'description' => $record->findLangByKey('description'),
                        'remarks'     => $record->findLangByKey('remarks')
                    ])
                );
            }

            return $list;
        } else {
            return $records;
        }
    }

    /**
     * @param Setting $entity
     * @param String|Array $code
     * @return Array
     */
    public function show($entity, $code)
    {
        $data = [
            'id' => $entity ? $entity->id : '',
            'basic' => []
        ];

        if (empty($entity))
            return $data;

        $this->setEntity($entity);

        if (is_string($code)) {
            $data['basic'] = [
                  'host_type'   => $entity->host_type,
                  'host_id'     => $entity->host_id,
                  'serial'      => $entity->serial,
                  'type'        => $entity->type,
                  'app_id'      => $entity->app_id,
                  'app_key'     => $entity->app_key,
                  'app_secret'  => $entity->app_secret,
                  'hash_key'    => $entity->hash_key,
                  'hash_iv'     => $entity->hash_iv,
                  'url_notify'  => $entity->url_notify,
                  'url_return'  => $entity->url_return,
                  'url_success' => $entity->url_success,
                  'url_cancel'  => $entity->url_cancel,
                  'name'        => $entity->findLang($code, 'name'),
                  'description' => $entity->findLang($code, 'description'),
                  'remarks'     => $entity->findLang($code, 'remarks'),
                  'is_enabled'  => $entity->is_enabled,
                  'updated_at'  => $entity->updated_at
            ];

        } elseif (is_array($code)) {
            foreach ($code as $language) {
                $data['basic'][$language] = [
                      'host_type'   => $entity->host_type,
                      'host_id'     => $entity->host_id,
                      'serial'      => $entity->serial,
                      'type'        => $entity->type,
                      'app_id'      => $entity->app_id,
                      'app_key'     => $entity->app_key,
                      'app_secret'  => $entity->app_secret,
                      'hash_key'    => $entity->hash_key,
                      'hash_iv'     => $entity->hash_iv,
                      'url_notify'  => $entity->url_notify,
                      'url_return'  => $entity->url_return,
                      'url_success' => $entity->url_success,
                      'url_cancel'  => $entity->url_cancel,
                      'name'        => $entity->findLang($language, 'name'),
                      'description' => $entity->findLang($language, 'description'),
                      'remarks'     => $entity->findLang($language, 'remarks'),
                      'is_enabled'  => $entity->is_enabled,
                      'updated_at'  => $entity->updated_at
                ];
            }
        }

        return $data;
    }
}
