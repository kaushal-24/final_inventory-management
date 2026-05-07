<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::with(['parent'])->withCount('products');
        
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }
        
        if ($request->filled('parent') && $request->parent !== '') {
            if ($request->parent === 'top') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $request->parent);
            }
        }
        
        $categories = $query->paginate(15);
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        if (!Auth::user()->canEditEverything()) {
            abort(403, 'Unauthorized action.');
        }
        $parentCategories = Category::whereNull('parent_id')->get();
        return view('categories.create', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->canEditEverything()) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'name' => 'required|unique:categories|max:255',
            'description' => 'nullable',
            'parent_id' => 'nullable|exists:categories,id'
        ]);

        Category::create($request->validated());

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function show(Category $category)
    {
        // Get products from this category and all its children
        $categoryIds = Category::where('id', $category->id)
            ->orWhere('parent_id', $category->id)
            ->pluck('id');

        $products = Product::with(['category', 'supplier'])
            ->whereIn('category_id', $categoryIds)
            ->get();

        $totalStock = $products->sum('quantity');
        $totalValue = $products->sum(fn($p) => $p->price * $p->quantity);

        return view('categories.show', compact('category', 'products', 'totalStock', 'totalValue'));
    }

    public function edit(Category $category)
    {
        if (!Auth::user()->canEditEverything()) {
            abort(403, 'Unauthorized action.');
        }
        $parentCategories = Category::whereNull('parent_id')->where('id', '!=', $category->id)->get();
        return view('categories.edit', compact('category', 'parentCategories'));
    }

    public function update(Request $request, Category $category)
    {
        if (!Auth::user()->canEditEverything()) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'name' => 'required|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable',
            'parent_id' => 'nullable|exists:categories,id'
        ]);

        $category->update($request->validated());

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can delete categories.');
        }
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }
}
