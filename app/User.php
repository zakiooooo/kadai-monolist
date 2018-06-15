<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    
        public function items()
    {
        return $this->belongsToMany(Item::class)->withPivot('type')->withTimestamps();
    }

    public function want_items()
    {
        return $this->items()->where('type', 'want');
    }

    public function want($itemId)
    {
        // Is the user already "want"?
        $exist = $this->is_wanting($itemId);

        if ($exist) {
            // do nothing
            return false;
        } else {
            // do "want"
            $this->items()->attach($itemId, ['type' => 'want']);
            return true;
        }
    }

    public function dont_want($itemId)
    {
        // Is the user already "want"?
        $exist = $this->is_wanting($itemId);

        if ($exist) {
            // remove "want"
            \DB::delete("DELETE FROM item_user WHERE user_id = ? AND item_id = ? AND type = 'want'", [\Auth::user()->id, $itemId]);
        } else {
            // do nothing
            return false;
        }
    }

    public function is_wanting($itemIdOrCode)
    {
        if (is_numeric($itemIdOrCode)) {
            $item_id_exists = $this->want_items()->where('item_id', $itemIdOrCode)->exists();
            return $item_id_exists;
        } else {
            $item_code_exists = $this->want_items()->where('code', $itemIdOrCode)->exists();
            return $item_code_exists;
        }
    }
}
