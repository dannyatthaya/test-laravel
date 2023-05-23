<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Http\Resources\CustomerResource;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
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

            $customers = Customer::when(
                $request->input('search'),
                fn ($query) => $query->where('display_name', 'LIKE', $request->input('search') . '%', 'OR')
                    ->where('name', 'LIKE', $request->input('search') . '%', 'OR')
            )->latest()->paginate(25);

            return $this->withPagination(CustomerResource::collection($customers)->response()->getData(true));
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
                'name' => 'required|string|unique:customers,name',
                'display_name' => 'required|string',
                'location' => 'required|string',
                'gender' => 'required|string|in:F,M',
                'address' => 'nullable|string',
            ]);

            Customer::create($validated);

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
                'id' => 'required|alpha_num|exists:customers,id',
            ]);

            $customer = Customer::findOrFail($id);

            return $this->success(CustomerResource::make($customer));
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
                'id' => 'required|alpha_num|exists:customers,id',
                'name' => 'required|string|unique:customers,name,' . $id,
                'display_name' => 'required|string',
                'location' => 'required|string',
                'gender' => 'required|string|in:F,M',
                'address' => 'nullable|string',
            ]);

            Customer::whereId($id)
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
                'id' => 'required|alpha_num|exists:customers,id',
            ]);

            Customer::findOrFail($id)->delete();

            return $this->success();
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
