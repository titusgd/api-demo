<?php

namespace App\Models\Ticket;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;
use App\Models\Account\User;

class Slider extends Model
{
    use HasFactory;

    protected $table = 'ticket_sliders';

    protected $fillable = [
        'name',
        'image_id',
        'editor',
        'status',
        'type',
        'link',
        'city',
        'tag',
        'date_from',
        'date_to',
        'sort',
        'created_at'
    ];

    protected $casts = [
        'use' => 'boolean',
        'status' => 'boolean',
    ];

    protected $appends = ['editor', 'content'];

    // public function getEditorAttribute()
    // {
    //     // dd($this->created_at->format('Y/m/d H:i:s'));

    //     return [
    //         'name' => $this->editor_name,
    //         // 'date' => explode(' ', str_replace('-','/',$this->editor_date)),
    //         'date' => explode(' ', $this->created_at->format('Y/m/d H:i:s')),
    //     ];
    // }


    public function getContentAttribute()
    {
        return [
            'type' => $this->type,
            'link' => $this->changeNull($this->link),
            'city' => json_decode($this->city, true),
            'tag' => json_decode($this->tag, true),
            'date' => [
                'from' => $this->changeNull($this->date_from),
                'to' => $this->changeNull($this->date_to)
            ]
        ];
    }

    public function dataFormat()
    {
        $user_info = User::find($this->editor);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->changeImageNull($this->image),
            'editor' => [
                'name' => $user_info['name'],
                'date' => explode(' ', $this->created_at->format('Y/m/d H:i:s'))
            ],
            'use' => $this->status,
            'content' => $this->content
        ];
    }
    public function dataPublicFormat()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->changeImageNull($this->image),
            // 'editor'=>$this->editor,
            // 'use'=>$this->status,
            'content' => $this->content
        ];
    }

    private function changeNull($data)
    {
        return (!empty($data)) ? $data : "";
    }

    private function changeImageNull($data){
        return (!empty($data)) ? 'https://erp-dev.*.com.tw'.$data : "";
        

    }
}
