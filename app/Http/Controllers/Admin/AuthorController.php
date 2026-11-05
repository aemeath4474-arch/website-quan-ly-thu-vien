<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
class AuthorController extends Controller {
    public function index(): View { $authors=Author::withCount('books')->orderBy('name')->paginate(12); return view('admin.authors.index',compact('authors')); }
    public function create(): View { return view('admin.authors.form',['author'=>new Author()]); }
    public function store(Request $request): RedirectResponse { $data=$request->validate(['name'=>['required','string','max:150','unique:authors,name'],'biography'=>['nullable','string']]); Author::create($data); return redirect()->route('admin.authors.index')->with('success','Đã thêm tác giả.'); }
    public function edit(Author $author): View { return view('admin.authors.form',compact('author')); }
    public function update(Request $request,Author $author): RedirectResponse { $data=$request->validate(['name'=>['required','string','max:150',Rule::unique('authors')->ignore($author->id)],'biography'=>['nullable','string']]); $author->update($data); return redirect()->route('admin.authors.index')->with('success','Đã cập nhật tác giả.'); }
    public function destroy(Author $author): RedirectResponse { if($author->books()->exists()) return back()->with('error','Tác giả đang được gắn với sách, không thể xóa.'); $author->delete(); return back()->with('success','Đã xóa tác giả.'); }
}
