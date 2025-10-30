<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function category_dropdown()
    {
        $parents = Category::whereNull('parent_id')->select('id', 'category_name')->get();
        return response()->json(['data' => $parents], 200);
    }

    public function sub_category_dropdown($parentId)
    {
        $children = Category::where('parent_id', $parentId)->select('id', 'category_name')->get();
        return response()->json(['data' => $children], 200);
    }
}
