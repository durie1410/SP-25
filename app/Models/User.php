<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'google_id',
        'avatar',
        'phone',
        'province',
        'district',
        'address',
        'so_cccd',
        'cccd_image',
        'ngay_sinh',
        'gioi_tinh',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoriteBooks()
    {
        return $this->belongsToMany(Book::class, 'favorites');
    }


    public function reader()
    {
        return $this->hasOne(Reader::class);
    }

    public function librarian()
    {
        return $this->hasOne(Librarian::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * Lấy hoặc tạo wallet cho user
     */
    public function getWallet()
    {
        return Wallet::getOrCreateForUser($this->id);
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'ngay_sinh' => 'date',
    ];

    /**
     * Automatically hash password when setting
     */
    public function setPasswordAttribute($value)
    {
        // Only hash if the value is not already hashed
        if (!empty($value) && !password_get_info($value)['algo']) {
            $this->attributes['password'] = Hash::make($value);
        } else {
            $this->attributes['password'] = $value;
        }
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin' || $this->hasRole('admin');
    }

    /**
     * Check if user is librarian
     */
    public function isLibrarian()
    {
        return $this->role === 'librarian' || $this->hasRole('librarian');
    }

    /**
     * Check if user is warehouse staff
     */
    public function isWarehouse()
    {
        return $this->role === 'warehouse' || $this->hasRole('warehouse');
    }

    /**
     * Check if user is staff (librarian, warehouse, or staff role)
     */
    public function isStaff()
    {
        return $this->role === 'staff' || $this->role === 'librarian' || $this->role === 'warehouse' || 
               $this->hasRole('staff') || $this->hasRole('librarian') || $this->hasRole('warehouse');
    }

    /**
     * Check if user is reader/user
     */
    public function isReader()
    {
        return $this->role === 'reader' || $this->role === 'user' || $this->hasRole('user');
    }

    /**
     * Check if user is regular user (not admin, not staff)
     */
    public function isUser()
    {
        return $this->role === 'user' || $this->hasRole('user');
    }

    /**
     * Check if user has any of the given roles
     * Wrapper để kiểm tra cả role attribute và Spatie roles
     */
    public function hasAnyOfRoles($roles)
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }
        
        // Check role attribute first
        if ($this->role && in_array($this->role, $roles)) {
            return true;
        }
        
        // Check Spatie roles (sử dụng method từ HasRoles trait)
        return parent::hasAnyRole($roles);
    }

    /**
     * Check if user can perform action (wrapper for permission check)
     */
    public function canDo($permission)
    {
        // Admin has all permissions
        if ($this->isAdmin()) {
            return true;
        }
        
        return $this->can($permission);
    }

    /**
     * Get user role name (with fallback)
     */
    public function getRoleName()
    {
        if ($this->role) {
            return $this->role;
        }
        
        $roles = $this->getRoleNames();
        return $roles->first() ?? 'user';
    }

    /**
     * Kiểm tra thông tin user có đầy đủ để mượn sách không
     */
    public function hasCompleteProfile()
    {
        return !empty($this->name) &&
               !empty($this->email) &&
               !empty($this->phone) &&
               !empty($this->so_cccd) &&
               !empty($this->ngay_sinh) &&
               !empty($this->gioi_tinh) &&
               !empty($this->address);
    }

    /**
     * Lấy danh sách các trường còn thiếu
     */
    public function getMissingFields()
    {
        $missing = [];
        
        if (empty($this->name)) {
            $missing[] = 'Họ và tên';
        }
        if (empty($this->email)) {
            $missing[] = 'Email';
        }
        if (empty($this->phone)) {
            $missing[] = 'Số điện thoại';
        }
        if (empty($this->so_cccd)) {
            $missing[] = 'Số CCCD';
        }
        if (empty($this->ngay_sinh)) {
            $missing[] = 'Ngày sinh';
        }
        if (empty($this->gioi_tinh)) {
            $missing[] = 'Giới tính';
        }
        if (empty($this->address)) {
            $missing[] = 'Địa chỉ';
        }
        
        return $missing;
    }

}
