<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $bookId = $this->route('book') ? $this->route('book')->id : null;

        return [
            'ten_sach' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'nha_xuat_ban_id' => 'nullable|exists:publishers,id',
            'tac_gia' => 'required|string|max:255',
            'nam_xuat_ban' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'hinh_anh' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'mo_ta' => 'nullable|string|max:2000',
            'gia' => 'nullable|numeric|min:0',
            'trang_thai' => 'required|in:active,inactive',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'ten_sach.required' => 'Tên sách là bắt buộc.',
            'ten_sach.max' => 'Tên sách không được vượt quá 255 ký tự.',
            'category_id.required' => 'Thể loại là bắt buộc.',
            'category_id.exists' => 'Thể loại không tồn tại.',
            'nha_xuat_ban_id.required' => 'Nhà xuất bản là bắt buộc.',
            'nha_xuat_ban_id.exists' => 'Nhà xuất bản không tồn tại.',
            'tac_gia.required' => 'Tác giả là bắt buộc.',
            'tac_gia.max' => 'Tên tác giả không được vượt quá 255 ký tự.',
            'nam_xuat_ban.required' => 'Năm xuất bản là bắt buộc.',
            'nam_xuat_ban.integer' => 'Năm xuất bản phải là số nguyên.',
            'nam_xuat_ban.min' => 'Năm xuất bản không được nhỏ hơn 1900.',
            'nam_xuat_ban.max' => 'Năm xuất bản không được lớn hơn năm hiện tại.',
            'hinh_anh.image' => 'File phải là hình ảnh.',
            'hinh_anh.mimes' => 'Hình ảnh phải có định dạng: jpeg, png, jpg, gif.',
            'hinh_anh.max' => 'Kích thước hình ảnh không được vượt quá 2MB.',
            'mo_ta.max' => 'Mô tả không được vượt quá 2000 ký tự.',
            'gia.numeric' => 'Giá phải là số.',
            'gia.min' => 'Giá không được âm.',
            'trang_thai.required' => 'Trạng thái là bắt buộc.',
            'trang_thai.in' => 'Trạng thái không hợp lệ.',
        ];
    }
}























