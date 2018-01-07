<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    /*
    *用户编辑/更新安全策略
    */
    public function update(User $currentUser, User $user)
    {
        //当前登录用户只能修改自己的信息
        return $currentUser->id === $user->id;
    }

    public function destroy(User $currentUser, User $user)
    {
        //必须是管理员，并且不能删除自己！
        return $currentUser->is_admin && $currentUser->id !== $user->id;
    }

}