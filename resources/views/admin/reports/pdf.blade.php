<style>
@font-face {
    font-family: 'DejaVu Sans';
    src: url('{{ storage_path('fonts/DejaVuSans.ttf') }}') format('truetype');
}

body {
    font-family: 'DejaVu Sans', sans-serif;
}
</style>
<h2 style="text-align:center;">BÁO CÁO KHO THƯ VIỆN</h2>

<h3>I. THỐNG KÊ SÁCH</h3>
<table border="1" width="100%">
    <tr>
        <td>Tổng sách</td>
        <td>{{ $stats['totalBooks'] }}</td>
    </tr>
    <tr>
        <td>Còn lại</td>
        <td>{{ $stats['remaining'] }}</td>
    </tr>
</table>

<h3>II. CHI TIẾT SÁCH</h3>
<table border="1" width="100%">
    <tr>
        <th>STT</th>
        <th>Tên sách</th>
        <th>Tác giả</th>
        <th>so luong</th>

    </tr>
    @foreach($books as $index => $book)
    <tr>
        <td>{{ $index + 1 }}</td>
        <td>{{ $book->ten_sach }}</td>
        <td>{{ $book->tac_gia }}</td>
        <td>{{ $book->so_luong }}</td>
    </tr>
    @endforeach
</table>

<h3>III. MƯỢN SÁCH</h3>
<table border="1" width="100%">
    <tr>
        <th>Mã mượn</th>
        <th>Độc giả</th>
        <th>Ngày mượn</th>
        <th>Trạng thái</th>
    </tr>
    @foreach($borrows as $item)
<tr>
    <td>{{ $item->id ?? '' }}</td>

    <td>{{ $item->ten_sach ?? 'N/A' }}</td>

    <td>
        {{ isset($item->borrow) && $item->borrow_date
            ? \Carbon\Carbon::parse($item->borrow_date)->format('d/m/Y')
            : 'N/A' }}
    </td>

    <td>{{ $item->trang_thai ?? '' }}</td>
</tr>
@endforeach

    <style>
table {
    border-collapse: collapse;
}
td, th {
    border: 1px solid black;
    padding: 5px;
}
h3 {
    margin-top: 20px;
}
</style>
</table>