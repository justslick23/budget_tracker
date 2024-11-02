<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {

        $userId = auth()->id(); // Get the authenticated user's ID

        $categories = Category::where('user_id', $userId)->get(); // Fetch all categories
        return view('categories.index', compact('categories')); // Return view with categories
    }

    public function create()
    {
        return view('categories.create'); // Return create view
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255', // Validate name input
        ]);

        // Create new category
        Category::create([
            'name' => $request->name,
            'user_id' => auth()->id(), // Associate with the authenticated user
        ]);

        return redirect()->route('categories.index')->with('success', 'Category created successfully.'); // Redirect with success message
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category')); // Return edit view with category
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255', // Validate name input
        ]);

        // Update category
        $category->update([
            'name' => $request->name,
        ]);

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.'); // Redirect with success message
    }

    public function destroy(Category $category)
    {
        $category->delete(); // Delete category
        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.'); // Redirect with success message
    }
}
