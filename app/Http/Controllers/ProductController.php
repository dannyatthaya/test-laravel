<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'search' => 'sometimes|string|nullable',
            ]);

            $products = Product::when(
                $request->input('search'),
                fn ($query) => $query->where('display_name', 'LIKE', $request->input('search') . '%', 'OR')
                    ->where('name', 'LIKE', $request->input('search') . '%', 'OR')
            )->latest()->paginate(25);

            return $this->withPagination(ProductResource::collection($products)->response()->getData(true));
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
                'name' => 'required|string|unique:products,name',
                'display_name' => 'required|string',
                'category' => 'required|string',
                'price' => 'required|numeric|gte:0',
                'color' => 'required|string',
            ]);

            Product::create($validated);

            return $this->success();
        } catch (Exception $e) {
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
                'id' => 'required|alpha_num|exists:products,id',
            ]);

            $product = Product::findOrFail($id);

            return $this->success(ProductResource::make($product));
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
                'id' => 'required|alpha_num|exists:products,id',
                'name' => 'required|string|unique:products,name,' . $id,
                'display_name' => 'required|string',
                'category' => 'required|string',
                'price' => 'required|numeric|gte:0',
                'color' => 'required|string',
            ]);

            Product::whereId($id)
                ->update(collect($validated)->except(['id'])->toArray());

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
                'id' => 'required|alpha_num|exists:products,id',
            ]);

            Product::findOrFail($id)->delete();

            return $this->success();
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
