<?php

namespace WalkerChiu\API\Models\Entities;

use WalkerChiu\Core\Models\Entities\LangTrait;
use WalkerChiu\Core\Models\Entities\UuidEntity;

class Setting extends UuidEntity
{
    use LangTrait;



    /**
     * Create a new instance.
     *
     * @param Array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('wk-core.table.api.settings');

        $this->fillable = array_merge($this->fillable, [
            'host_type', 'host_id',
            'serial', 'type',
            'app_id', 'app_token', 'app_key', 'app_secret', 'function_id',
            'hash_key', 'hash_iv',
            'url_notify', 'url_return', 'url_success', 'url_cancel'
        ]);

        parent::__construct($attributes);
    }

    /**
     * Get it's lang entity.
     *
     * @return Lang
     */
    public function lang()
    {
        if (
            config('wk-core.onoff.core-lang_core')
            || config('wk-api.onoff.core-lang_core')
        ) {
            return config('wk-core.class.core.langCore');
        } else {
            return config('wk-core.class.api.settingLang');
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function langs()
    {
        if (
            config('wk-core.onoff.core-lang_core')
            || config('wk-api.onoff.core-lang_core')
        ) {
            return $this->langsCore();
        } else {
            return $this->hasMany(config('wk-core.class.api.settingLang'), 'morph_id', 'id');
        }
    }

    /**
     * Get the owning host model.
     */
    public function host()
    {
        return $this->morphTo();
    }
}
