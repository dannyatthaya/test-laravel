<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'subtotal' => $this->subtotal,
            'customer' => CustomerResource::make($this->whenLoaded('customer')),
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'quantity' => $this->whenPivotLoaded('order_products', function () {
                return $this->pivot->quantity;
            }),
            'createdAt' => Carbon::parse($this->created_at)->format('d-m-Y H:i:s'),
            'updatedAt' => Carbon::parse($this->updated_at)->format('d-m-Y H:i:s'),
        ];
    }
}
