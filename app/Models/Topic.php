<?php

namespace App\Models;

class Topic extends Model
{
    protected $fillable = ['title', 'body',  'category_id', 'excerpt', 'slug'];

    //一篇帖子下多条评论
    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    //一个话题属于一个分类
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    //一个话题属于一个作者
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //排序
    public function scopeWithOrder($query, $order)
    {
        //不同的排序，使用不同的数据读取逻辑
        switch ($order) {
            case 'recent':
                $query->recent();
                break;

            default:
                $query->recentReplied();
                break;
        }
    }

    //最后修改的排序
    public function scopeRecentReplied($query)
    {
        //当护体有新回复时，我们将编写逻辑来更新话题模型的reply_count 属性
        //此时会自动触发框架对数据模型 updated_at 时间戳的更新
        return $query->orderBy('updated_at', 'desc');
    }

    //按照创建时间排序
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }


    public function link($params = [])
    {
        return route('topics.show', array_merge([$this->id, $this->slug], $params));
    }

    public function updateReplyCount()
    {
        $this->reply_count = $this->replies->count();
        $this->save();
    }

}
