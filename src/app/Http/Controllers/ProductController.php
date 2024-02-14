<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected Model $model;

    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    public function store(Request $request)
    {
        $request = $request->all();

        $product = $this->model->create($request);

        return response()->json([
            'data' => $product,
            'message' => 'item has been added successfully'
        ]);
    }
    public function index(Request $request)
    {
        $products = $this->model->all();
        return response()->json([
            'data' => $products,
        ]);
    }

    public function show(Product $product)
    {

        return response()->json([
            'data' => $product,
        ]);
    }

    public function delete(Product $product)
    {
        $product->delete();
        return response()->json([
            'message' => 'item has been added successfully'
        ]);
    }
}
