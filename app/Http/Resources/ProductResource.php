<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'displayName' => $this->display_name,
            'category' => $this->category,
            'price' => $this->price,
            'color' => $this->color,
            'orders' => OrderResource::collection($this->whenLoaded('orders')),
            'quantity' => $this->whenPivotLoaded('order_products', function () {
                return $this->pivot->quantity;
            }),
            'createdAt' => Carbon::parse($this->created_at)->format('d-m-Y H:i:s'),
            'updatedAt' => Carbon::parse($this->updated_at)->format('d-m-Y H:i:s'),
        ];
    }
}
