<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $cats = Category::all();
        if ($cats->isEmpty()) {
            return response()->json(['message' => '沒有分類資料'], 404);
        }
        return response()->json($cats);
    }
}
