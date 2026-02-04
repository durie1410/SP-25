<?php

namespace App\Http\Controllers\Librarian;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookManagementController extends Controller
{
    public function create()
    {
        $authors = Author::active()->orderBy('ten_tac_gia')->get();
        $categories = Category::orderBy('ten_the_loai')->get();

        return view('librarian.books.create', compact('authors', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        $author = Author::findOrFail($data['author_id']);

        $book = Book::create([
            // Mapping theo schema hiện tại
            'ten_sach' => $data['title'],
            'category_id' => $data['category_id'],
            'tac_gia' => $author->ten_tac_gia,
            'mo_ta' => $data['description'] ?? null,
            'nam_xuat_ban' => $data['publish_year'],
            'hinh_anh' => null,
            'trang_thai' => 'active',
        ]);

        Log::info('Librarian created book', [
            'user_id' => auth()->id(),
            'book_id' => $book->id,
        ]);

        return redirect()->route('librarian.books.edit', $book)->with('success', 'Tạo sách mới thành công.');
    }

    public function edit(Book $book)
    {
        $authors = Author::active()->orderBy('ten_tac_gia')->get();
        $categories = Category::orderBy('ten_the_loai')->get();

        // Attempt to resolve current author_id from tac_gia string (best-effort)
        $currentAuthorId = Author::where('ten_tac_gia', $book->tac_gia)->value('id');

        return view('librarian.books.edit', compact('book', 'authors', 'categories', 'currentAuthorId'));
    }

    public function update(Request $request, Book $book)
    {
        $data = $this->validatedData($request);

        $author = Author::findOrFail($data['author_id']);

        $book->update([
            'ten_sach' => $data['title'],
            'category_id' => $data['category_id'],
            'tac_gia' => $author->ten_tac_gia,
            'mo_ta' => $data['description'] ?? null,
            'nam_xuat_ban' => $data['publish_year'],
        ]);

        Log::info('Librarian updated book', [
            'user_id' => auth()->id(),
            'book_id' => $book->id,
        ]);

        return redirect()->back()->with('success', 'Cập nhật sách thành công.');
    }

    /**
     * Validation theo yêu cầu: title, author_id, category_id, description, publish_year
     */
    private function validatedData(Request $request): array
    {
        $currentYear = (int) now()->format('Y');

        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author_id' => ['required', 'integer', 'exists:authors,id'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'publish_year' => ['required', 'integer', 'min:1000', 'max:' . $currentYear],
        ]);
    }
}

