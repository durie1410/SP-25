<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->get('role'));
        }
        
        // Filter by status (if you have a status field)
        // Note: Users table doesn't have status field, so we'll skip this filter
        // if ($request->filled('status')) {
        //     $query->where('status', $request->get('status'));
        // }
        
        $users = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Get statistics
        $totalUsers = User::count();
        $adminUsers = User::where('role', 'admin')->count();
        $staffUsers = User::where('role', 'staff')->count();
        $regularUsers = User::where('role', 'user')->count();
        $newUsersThisMonth = User::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        return view('admin.users.index', compact(
            'users',
            'totalUsers',
            'adminUsers',
            'staffUsers',
            'regularUsers',
            'newUsersThisMonth'
        ));
    }
    
    public function create()
    {
        return view('admin.users.create');
    }
    
    public function show(User $user)
    {
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'locked_at' => $user->locked_at,
            'is_locked' => $user->isLocked(),
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            // Thông tin đăng ký độc giả
            'phone' => $user->phone,
            'province' => $user->province,
            'district' => $user->district,
            'xa' => $user->xa,
            'address' => $user->address,
            'so_cccd' => $user->so_cccd,
            'cccd_image' => $user->cccd_image,
            'ngay_sinh' => $user->ngay_sinh,
            'gioi_tinh' => $user->gioi_tinh,
        ]);
    }
    
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,staff,user',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password, // Let the model's setPasswordAttribute handle hashing
                'role' => $request->role,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle duplicate entry or other database errors
            if ($e->getCode() == 23000) {
                return redirect()->back()
                    ->withErrors(['email' => 'Email này đã được sử dụng hoặc có lỗi xảy ra khi tạo người dùng.'])
                    ->withInput();
            }
            throw $e;
        }
        
        // Assign role using Spatie Permission if you're using it
        if (method_exists($user, 'assignRole')) {
            $user->assignRole($request->role);
        }
        
        return redirect()->route('admin.users.index')
            ->with('success', 'Người dùng đã được tạo thành công!');
    }
    
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:admin,staff,user',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];
        
        // Only update password if provided
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }
        
        $user->update($updateData);
        
        // Update role using Spatie Permission if you're using it
        if (method_exists($user, 'syncRoles')) {
            $user->syncRoles([$request->role]);
        }
        
        return redirect()->route('admin.users.index')
            ->with('success', 'Người dùng đã được cập nhật thành công!');
    }
    
    public function destroy(User $user)
    {
        // Prevent locking the last admin
        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể khóa quản trị viên cuối cùng'
            ], 400);
        }

        // Prevent locking themselves
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể khóa chính mình'
            ], 400);
        }

        // Toggle lock status
        if ($user->locked_at) {
            $user->update(['locked_at' => null]);
            return response()->json([
                'success' => true,
                'message' => 'Đã mở khóa tài khoản'
            ]);
        } else {
            $user->update(['locked_at' => now()]);
            return response()->json([
                'success' => true,
                'message' => 'Đã khóa tài khoản'
            ]);
        }
    }

    public function lockUser($id)
    {
        $user = User::findOrFail($id);

        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Không thể khóa quản trị viên cuối cùng'], 403);
            }
            return back()->with('error', 'Không thể khóa quản trị viên cuối cùng');
        }
        if ($user->id === auth()->id()) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Không thể khóa chính mình'], 403);
            }
            return back()->with('error', 'Không thể khóa chính mình');
        }

        $user->locked_at = now();
        $user->save();

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Đã khóa tài khoản']);
        }
        return back()->with('success', 'Đã khóa tài khoản');
    }

    public function unlockUser($id)
    {
        $user = User::findOrFail($id);

        $user->locked_at = null;
        $user->save();

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Đã mở khóa tài khoản']);
        }
        return back()->with('success', 'Đã mở khóa tài khoản');
    }
    
    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:activate,deactivate,delete',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ'
            ], 400);
        }
        
        $userIds = $request->user_ids;
        $action = $request->action;
        
        switch ($action) {
            case 'activate':
                User::whereIn('id', $userIds)->update(['status' => 'active']);
                $message = 'Đã kích hoạt ' . count($userIds) . ' người dùng';
                break;
                
            case 'deactivate':
                User::whereIn('id', $userIds)->update(['status' => 'inactive']);
                $message = 'Đã vô hiệu hóa ' . count($userIds) . ' người dùng';
                break;
                
            case 'delete':
                // Prevent deleting all admins
                $adminCount = User::where('role', 'admin')->count();
                $adminIdsToDelete = User::whereIn('id', $userIds)->where('role', 'admin')->pluck('id');
                
                if ($adminCount - $adminIdsToDelete->count() < 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Không thể xóa tất cả quản trị viên'
                    ], 400);
                }
                
                // Chuyển các user có role 'staff' thành 'user' trước khi xóa
                User::whereIn('id', $userIds)->where('role', 'staff')->update(['role' => 'user']);
                
                User::whereIn('id', $userIds)->delete();
                $message = 'Đã xóa ' . count($userIds) . ' người dùng';
                break;
        }
        
        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }
    
    public function export(Request $request)
    {
        $query = User::query();
        
        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('role')) {
            $query->where('role', $request->get('role'));
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }
        
        $users = $query->orderBy('created_at', 'desc')->get();
        
        $filename = 'users_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, ['ID', 'Tên', 'Email', 'Vai trò', 'Trạng thái', 'Ngày tạo', 'Cập nhật cuối']);
            
            // Add data rows
            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->role,
                    $user->status ?? 'active',
                    $user->created_at->format('d/m/Y H:i'),
                    $user->updated_at->format('d/m/Y H:i'),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
