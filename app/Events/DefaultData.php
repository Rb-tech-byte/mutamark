<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class DefaultData
{
    use Dispatchable;

    public int $company_id;
    public $user_module;
    public $workspace_id;

    public function __construct(
        int $company_id,
        $user_module = null,
        $workspace_id = 0
    ) {
        $this->company_id = $company_id;
        $this->user_module = $user_module;
        $this->workspace_id = $workspace_id ?? 0;
    }

    public function __get($name)
    {
        if ($name === 'workspace_id') {
            return $this->workspace_id ?? 0;
        }
        return null;
    }
}
