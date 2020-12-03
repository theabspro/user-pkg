<?php

namespace Abs\UserPkg\Controllers\Api;
use Abs\BasicPkg\Controllers\Api\BaseController;
use Abs\BasicPkg\Traits\CrudTrait;

class UserController extends BaseController {
	use CrudTrait;
	public $model = 'App\Models\Masters\Auth\User';

}
