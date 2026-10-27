<?php

namespace Modules\CustomField\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomFieldsModuleList extends Model
{
    use HasFactory;

    protected $fillable = [];
    protected $table = 'custom_fields_module_list';

    protected static function newFactory()
    {
        return \Modules\CustomField\Database\factories\CustomFieldsModuleListFactory::new();
    }

    public function sub_module(){

    }

}
