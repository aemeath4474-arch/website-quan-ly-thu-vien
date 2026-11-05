<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
class CategoryController extends Controller {
    public function index(): View { $categories=Category::withCount('books')->orderBy('name')->paginate(12); return view('admin.categories.index',compact('categories')); }
    public function create(): View { return view('admin.categories.form',['category'=>new Category()]); }
    public function store(Request $request): RedirectResponse { $data=$request->validate(['name'=>['required','string','max:100','unique:categories,name'],'description'=>['nullable','string']]); Category::create($data); return redirect()->route('admin.categories.index')->with('success','Đã thêm thể loại.'); }
    public function edit(Category $category): View { return view('admin.categories.form',compact('category')); }
    public function update(Request $request,Category $category): RedirectResponse { $data=$request->validate(['name'=>['required','string','max:100',Rule::unique('categories')->ignore($category->id)],'description'=>['nullable','string']]); $category->update($data); return redirect()->route('admin.categories.index')->with('success','Đã cập nhật thể loại.'); }
    public function destroy(Category $category): RedirectResponse { if($category->books()->exists()) return back()->with('error','Thể loại đang có sách, không thể xóa.'); $category->delete(); return back()->with('success','Đã xóa thể loại.'); }
}
