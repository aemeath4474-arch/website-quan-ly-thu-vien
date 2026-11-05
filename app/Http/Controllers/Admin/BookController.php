<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class BookController extends Controller
{
    public function index(Request $request): View
    {
        $query = Book::with(['category','authors']);
        if ($request->filled('q')) {
            $term = trim($request->q);
            $query->where(fn($q) => $q->where('title','like',"%{$term}%")
                ->orWhere('isbn','like',"%{$term}%")
                ->orWhere('publisher','like',"%{$term}%"));
        }
        if ($request->filled('category_id')) $query->where('category_id',$request->category_id);
        if ($request->filled('status')) $query->where('status',$request->status);
        $books = $query->latest()->paginate(10)->withQueryString();
        $categories = Category::orderBy('name')->get();
        return view('admin.books.index', compact('books','categories'));
    }

    public function create(): View
    {
        return view('admin.books.create', [
            'book' => new Book(),
            'categories' => Category::orderBy('name')->get(),
            'authors' => Author::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $authorIds = $data['author_ids'] ?? [];
        unset($data['author_ids']);
        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('books','public');
        }
        $book = Book::create($data);
        $book->authors()->sync($authorIds);
        return redirect()->route('admin.books.index')->with('success','Đã thêm sách mới.');
    }

    public function edit(Book $book): View
    {
        $book->load('authors');
        return view('admin.books.edit', [
            'book' => $book,
            'categories' => Category::orderBy('name')->get(),
            'authors' => Author::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Book $book): RedirectResponse
    {
        $data = $this->validatedData($request, $book);
        $authorIds = $data['author_ids'] ?? [];
        unset($data['author_ids']);
        if ($request->hasFile('cover_image')) {
            if ($book->cover_image) Storage::disk('public')->delete($book->cover_image);
            $data['cover_image'] = $request->file('cover_image')->store('books','public');
        }
        $book->update($data);
        $book->authors()->sync($authorIds);
        return redirect()->route('admin.books.index')->with('success','Đã cập nhật thông tin sách.');
    }

    public function destroy(Book $book): RedirectResponse
    {
        if ($book->borrowOrderItems()->exists()) {
            return back()->with('error','Không thể xóa sách đã phát sinh giao dịch mượn/thuê. Bạn có thể chuyển trạng thái sang Ẩn.');
        }
        if ($book->cover_image) Storage::disk('public')->delete($book->cover_image);
        $book->authors()->detach();
        $book->delete();
        return redirect()->route('admin.books.index')->with('success','Đã xóa sách.');
    }

    private function validatedData(Request $request, ?Book $book = null): array
    {
        return $request->validate([
            'category_id'=>['required','exists:categories,id'],
            'title'=>['required','string','max:255'],
            'isbn'=>['required','string','max:20',Rule::unique('books','isbn')->ignore($book?->id)],
            'publisher'=>['nullable','string','max:150'],
            'publication_year'=>['nullable','integer','min:1000','max:'.(date('Y')+1)],
            'description'=>['nullable','string'],
            'cover_image'=>['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
            'price'=>['required','numeric','min:0'],
            'rental_price'=>['required','numeric','min:0'],
            'total_copies'=>['required','integer','min:0'],
            'available_copies'=>['required','integer','min:0','lte:total_copies'],
            'status'=>['required',Rule::in(['active','hidden'])],
            'author_ids'=>['nullable','array'],
            'author_ids.*'=>['integer','exists:authors,id'],
        ],[
            'isbn.unique'=>'ISBN này đã được sử dụng.',
            'cover_image.max'=>'Ảnh bìa không được vượt quá 2MB.',
            'available_copies.lte'=>'Số lượng còn phải nhỏ hơn hoặc bằng tổng số lượng.',
        ]);
    }
}
