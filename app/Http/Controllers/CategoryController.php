<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    //Creating new product Category
    public function store(Request $request){
        $request->validate([
            'name'  => 'required|string|unique:categories,name',
            'descriptions' => 'required|string'
        ],
        [
            'name.unique'  => 'Category with the same name already exist please try again'
        ]);

        $category = Category::create([
                    'name'  => $request->name,
                    'descriptions' => $request->descriptions
        ]);


        return response()->json([
                'status'  => 'success',
                'message'  => 'category creted successfully!',
                'data'     => $category
        ], 201);
    }


    //updating existing product category
    public function update(Request $request, $id){
        $category = Category::find($id);
        if(!$category){
            return response()->json([
                'message'  => 'category not found'
            ], 404);
        }
        $fields = $request->validate([
            'name'  => 'sometimes|string|unique:categories,name',
            'descritions' => 'sometimes|string'
        ]);

        $category->update($fields);

        return response()->json([
            'message'  => 'category updated successfully!',
            'update fields'  => array_keys($fields)
        ], 200);
    }

    //Deleting Category
    public function destroy($id){
         $category = Category::find($id);
        if(!$category){
            return response()->json([
                'message'  => 'category not found'
            ], 404);


    }

    $category->delete();

    return response()->json([
            'message'  => 'category deleted successfully!'
           
        ], 200);
    }
    
    //Get category list

    public function index(){
        $categories = Category::all();


        if($categories->isEmpty()){
            return response()->json([
                'status'  => false,
                'message'  => 'NO Category records found'
            ], 200);
        }

        return response()->json([
            'status' => true,
            'count'  => $categories->count(),
            'data'  => $categories
        ], 200);
    }

}
