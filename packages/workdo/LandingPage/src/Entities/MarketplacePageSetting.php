<?php

namespace Workdo\LandingPage\Entities;

use Illuminate\Database\Eloquent\Model;

class MarketplacePageSetting extends Model
{
    protected $table = 'marketplace_page_settings';

    protected $fillable = [
        'name',
        'module',
        'value'
    ];
}
