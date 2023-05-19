<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): JsonResponse
    {
        try {
            $orders = Order::with(['products', 'customer'])->paginate(25);

            return $this->withPagination(OrderResource::collection($orders)->response()->getData(true));
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'customer.id' => 'required|numeric',
                'products' => 'required|array',
                'products.*.product.id' => 'required|numeric|exists:products,id',
                'products.*.quantity' => 'required|numeric|gte:1',
            ]);

            DB::beginTransaction();

            $subtotal = collect($request->products)->sum(function ($item) {
                return $item['product']['price'] * $item['quantity'];
            });

            $latest_order_id = Order::latest('created_at')->first();

            if (isset($latest_order_id) && !empty($latest_order_id)) {
                $latest_order_id = ((int) str($latest_order_id->name)->explode('_')->last()) + 1;
            } else {
                $latest_order_id = 1;
            }

            $order = Order::create([
                'name' => 'NOTA_' . $latest_order_id,
                'customer_id' => $validated['customer']['id'],
                'subtotal' => $subtotal,
            ]);

            $products_with_quantity = collect($request->products)
                ->mapWithKeys(fn ($item) => [$item['product']['id'] => ['quantity' => $item['quantity']]]);

            $order->products()->sync($products_with_quantity);

            DB::commit();

            return $this->success();
        } catch (Exception $e) {
            DB::rollBack();

            return $this->error($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  string $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request): JsonResponse
    {
        try {
            $request->merge(['id' => $id]);

            $request->validate([
                'id' => 'required|string|exists:orders,id',
            ]);

            $order = Order::with(['products', 'customer'])->findOrFail($id);

            return $this->success(OrderResource::make($order));
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'id' => 'required|numeric|exists:orders,id',
                'customer.id' => 'required|numeric',
                'products' => 'required|array',
                'products.*.product.id' => 'required|numeric|exists:products,id',
                'products.*.quantity' => 'required|numeric|gte:1',
            ]);

            DB::beginTransaction();

            $subtotal = collect($request->products)->sum(function ($item) {
                return $item['product']['price'] * $item['quantity'];
            });

            $order = Order::find($id);
            
            $order->update([
                'customer_id' => $validated['customer']['id'],
                'subtotal' => $subtotal,
            ]);

            $products_with_quantity = collect($request->products)
                ->mapWithKeys(fn ($item) => [$item['product']['id'] => ['quantity' => $item['quantity']]]);

            $order->products()->sync($products_with_quantity);

            DB::commit();

            return $this->success();
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request): JsonResponse
    {
        try {
            $request->merge(['id' => $id]);

            $request->validate([
                'id' => 'required|string|exists:orders,id',
            ]);

            DB::beginTransaction();

            $order = Order::findOrFail($id);

            $order->products()->delete();
            $order->delete();

            DB::commit();

            return $this->success();
        } catch (Exception $e) {
            DB::rollBack();

            return $this->error($e->getMessage());
        }
    }
}
