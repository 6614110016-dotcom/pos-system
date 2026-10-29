<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * โมดูลสินค้า (Stock) - จัดการหมวดหมู่สินค้า
 */
class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::withCount('products')
            ->when($request->q, fn ($q, $term) => $q->where('name', 'like', "%{$term}%"))
            ->orderBy('code')
            ->paginate(15)
            ->withQueryString();

        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('categories.create', ['nextCode' => Category::generateCode()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:150', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'กรุณากรอกชื่อหมวดหมู่',
            'name.unique'   => 'มีหมวดหมู่ชื่อนี้อยู่แล้ว',
        ]);

        $data['code']   = Category::generateCode();
        $data['status'] = $request->boolean('status', true);

        $category = Category::create($data);
        ActivityLog::record('create', 'Category', "เพิ่มหมวดหมู่ {$category->name}");

        return redirect()->route('categories.index')
            ->with('success', "เพิ่มหมวดหมู่ \"{$category->name}\" เรียบร้อยแล้ว");
    }

    public function edit(Category $category): View
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:150', Rule::unique('categories', 'name')->ignore($category->id)],
            'description' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'กรุณากรอกชื่อหมวดหมู่',
            'name.unique'   => 'มีหมวดหมู่ชื่อนี้อยู่แล้ว',
        ]);

        $data['status'] = $request->boolean('status');
        $category->update($data);
        ActivityLog::record('update', 'Category', "แก้ไขหมวดหมู่ {$category->name}");

        return redirect()->route('categories.index')
            ->with('success', "แก้ไขหมวดหมู่ \"{$category->name}\" เรียบร้อยแล้ว");
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'ไม่สามารถลบหมวดหมู่ที่ยังมีสินค้าอยู่ได้');
        }

        $name = $category->name;
        $category->delete();
        ActivityLog::record('delete', 'Category', "ลบหมวดหมู่ {$name}");

        return redirect()->route('categories.index')->with('success', "ลบหมวดหมู่ \"{$name}\" เรียบร้อยแล้ว");
    }
}
