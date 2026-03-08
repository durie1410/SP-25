<h2 style="text-align:center">BAO CAO MUON SACH</h2>

<table border="1" width="100%" cellpadding="5">
<tr>
    <th>Ma muon</th>
    <th>Doc gia</th>
    <th>Ngay muon</th>
    <th>Trang thai</th>
</tr>

@foreach($borrows as $borrow)
<tr>
    <td>{{ $borrow->id }}</td>
    <td>{{ $borrow->reader->ten ?? '' }}</td>
    <td>{{ $borrow->ngay_muon }}</td>
    <td>{{ $borrow->trang_thai }}</td>
</tr>
@endforeach

</table>