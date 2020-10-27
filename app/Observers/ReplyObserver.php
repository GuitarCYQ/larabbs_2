<?php

namespace App\Observers;

use App\Models\Reply;
use App\Notifications\TopicReplied;

// creating, created, updating, updated, saving,
// saved,  deleting, deleted, restoring, restored

class ReplyObserver
{
    //统计回复数 并保存
    public function created(Reply $reply)
    {
        //命令行允许迁移时 不做这些操作
        if ( ! app()->runningInConsole()) {
            $reply->topic->updateReplyCount();

            //通知话题作者有新的评论
            $reply->topic->user->notify(new TopicReplied($reply));
        }
    }

    //防止 XSS
    public function creating(Reply $reply)
    {
        $reply->content = clean($reply->content, 'user_topic_body');
    }
    
    //监听回复删除之后 评论数也要减少
    public function deleted(Reply $reply)
    {
        $reply->topic->reply_count = $reply->topic->replies->count();

        $reply->topic->save();
    }
}