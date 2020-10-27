<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmailContract
{
    use MustVerifyEmailTrait;

    use Notifiable {
        notify as protected laravelNotify;
    }

    // laravel-permission 提供的权限和角色
    use HasRoles;

    public function notify($instance)
    {
        //如果要通知的人是当前用户，就不必通知了！
        if ($this->id == Auth::id()) {
            return;
        }

        //只有数据库类型通知才需提醒，之际发送Email 或者其他的都 Pass
        if (method_exists($instance, 'toDatabase')){
            $this->increment('notification_count');
        }

        $this->laravelNotify($instance);
    }

    /**
     *使用MustVerifyEmailTrait
     * hasVerifiedEmail() 检测用户 Email 是否已认证；
     * markEmailAsVerified() 将用户标示为已认证；
     * sendEmailVerificationNotification() 发送 Email 认证的消息通知，触发邮件的发送；
     * getEmailForVerification() 获取发送邮件地址，提供这个接口允许你自定义邮箱字段。
     */

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','introduction','avatar',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    //用户与话题时一对多的关系 一个用户有多个话题
    public function topics()
    {
        return $this->hasMany(Topic::class);
    }

    public function isAuthorOf($model)
    {
        return $this->id == $model->user_id;
    }

    //一个用户可以拥有多条评论
    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    //已读通知 清空通知
    public function markAsRead()
    {
        $this->notification_count = 0;
        $this->save();
        $this->unreadNotifications->markAsRead();
    }

    public function setPasswordAttribute($value)
    {
        //如果值的长度等于60即认为值已经做过加密的情况
        if (strlen($value) != 60){
            //不等于60 做加密处理
            $value = bcrypt($value);
        }

        $this->attributes['password'] = $value;
    }

    public function setAvatarAttribute($path)
    {
        //如果不是‘http’ 子串开头，那就是从后台上传的，需要补全url
        if ( ! \Str::startsWith($path,'http')) {

            //拼接完整的URL
            $path = config('app.url') . "/uploads/images/avatars/$path";
        }

        $this->attributes['avatar'] = $path;
    }

}
