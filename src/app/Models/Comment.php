<?php

namespace AbbyJanke\Blog\app\Models;

use Backpack\CRUD\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;

class Comment extends Model
{

    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'comments';
    protected $primaryKey = 'id';
    protected $fillable = ['article_id', 'comment', 'parent_id', 'approved',
      'author_name', 'author_email', 'author_id', 'author_url', 'author_ip',
    ];

    /*
    |--------------------------------------------------------------------------
    | ACCESORS
    |--------------------------------------------------------------------------
    */

    // Allow the option to have a cleaner text display the published date rather then just a date.
    public function getPublishedAttribute()
    {

      // less then 10 hours
      if(Carbon::parse($this->created_at) > Carbon::now()->subHours(10)) {
        return Carbon::parse($this->created_at)->diffForHumans();
      }

      // posted today
      if(Carbon::parse($this->created_at)->day == Carbon::now()->day) {
        return 'today at' . Carbon::parse($this->created_at)->format('h:i A');
      }

      return 'on ' . Carbon::parse($this->created_at)->format('F dS Y h:i A');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope a query to only include approved comments
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApproved($query)
    {
        return $query->where('approved', 1);
    }

    /**
     * Scope a query to only include parent comments
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeParent($query)
    {
        return $query->where('parent_id', null);
    }

    /**
     * Scope a query to only include parent comments
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeChild($query, $parentId)
    {
        return $query->where('parent_id', $parentId);
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    // get the author.
    public function author()
    {
        return $this->hasOne('App\User', 'id', 'author_id');
    }

    // get the author.
    public function article()
    {
        return $this->hasOne('AbbyJanke\Blog\app\Models\Article', 'id', 'article_id');
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    public function displayAuthorFull()
    {
        if($this->author) {
          $email = $this->author->email;
          $name = $this->author->name;
        } else {
          $email = $this->author_email;
          $name = $this->author_name;
        }

        $avatar = '<img src="'. \Gravatar::get($email).'" alt="..." class="img-circle pull-left" style="width:36px;margin:0 10px 0 0">';

        return $avatar.'<strong>'. $name .'</strong><br /><span style="font-size:.8em;">'.$email.'<br />'.$this->author_ip.'</span>';
    }

    public function responseTo()
    {
      return '<strong><a href="'.route("crud.article.edit", ["id" => $this->article_id]).'/">'.$this->article->title.'</a></strong><br />'.
      '<a href="'.route("blog.post", ["slug" => $this->article->slug]).'/">View Post</a><br />';
    }

}
