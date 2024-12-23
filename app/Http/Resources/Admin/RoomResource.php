<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    //public function toArray(Request $request): array
    //{
        //return parent::toArray($request);
    //}

    public function toArray($request)
    {
        return [
            'id' => $this->Room_ID,
            'name' => $this->RoomType->Name,
            'price' => $this->RoomType->Price,
            'capacity' => $this->RoomType->Capacity,     
            'status' => $this->Status,
            'image' => $this->Image,
        ];
    }

}
