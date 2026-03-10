@extends('layouts.admin')

@section('content')

<h3>Quản lý người dùng</h3>

<table class="table table-bordered">

<tr>
<th>ID</th>
<th>Tên</th>
<th>Email</th>
<th>Trạng thái</th>
<th>Hành động</th>
</tr>

@foreach($users as $user)

<tr>

<td>{{ $user->id }}</td>
<td>{{ $user->name }}</td>
<td>{{ $user->email }}</td>

<td>

@if($user->status == 'active')
<span class="badge bg-success">Active</span>

@elseif($user->status == 'pending')
<span class="badge bg-warning">Pending</span>

@else
<span class="badge bg-danger">Locked</span>
@endif

</td>

<td>

@if($user->status == 'pending')

<a href="{{ route('admin.users.approve',$user->id) }}" class="btn btn-success btn-sm">
Duyệt
</a>

@endif

@if($user->status == 'locked')

<a href="{{ route('admin.users.unlock',$user->id) }}" class="btn btn-primary btn-sm">
Mở khóa
</a>

@else

<a href="{{ route('admin.users.lock',$user->id) }}" class="btn btn-danger btn-sm">
Khóa
</a>

@endif

</td>

</tr>

@endforeach

</table>

@endsection