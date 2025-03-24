<?php
namespace App\Models;

class UserSession
{
    public $id;
    public $key;
    public $created_at;
    public $used_at;

    public $user_id;
}
