<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit');
        $name = $request->input('name');
        $show_product = $request->input('show_product');

        if ($id) {
            $productCategory = ProductCategory::with(['products'])->find($id);

            if ($productCategory) {
                return ResponseFormatter::success(
                    $productCategory,
                    'Data kategori berhasil diambil'    
                );
            } else {
                return ResponseFormatter::error(
                    null,
                    'data kategori gagal diambil',
                    404
                );
            }
        }

        $productCategory = ProductCategory::query();
        if ($name) {
            $productCategory->where('name', 'like', '%'.$name.'%');
        }

        if ($show_product) {
            $productCategory->with('products');
        }

        return ResponseFormatter::success(
            $productCategory->paginate($limit),
            'Data list kategori berhasil diambil'    
        );

    }
}
