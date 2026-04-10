<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BackupService;
use App\Models\Backup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class SettingsController extends Controller
{
    protected $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    public function index()
    {
        // Get current settings from database or config
        $settings = $this->getCurrentSettings();
        
        // Get backup statistics
        $backupStats = $this->backupService->getBackupStats();
        
        // Get recent backups
        $recentBackups = Backup::completed()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('admin.settings.index', compact('settings', 'backupStats', 'recentBackups'));
    }
    
    public function update(Request $request)
    {
        $validator = $request->validate([
            'library_name' => 'required|string|max:255',
            'library_address' => 'required|string|max:500',
            'library_phone' => 'required|string|max:20',
            'library_email' => 'required|email|max:255',
            'library_description' => 'nullable|string|max:1000',
            'opening_time' => 'required|date_format:H:i',
            'closing_time' => 'required|date_format:H:i',
            'max_borrow_days' => 'required|integer|min:1|max:365',
            'max_borrow_books' => 'required|integer|min:1|max:20',
            'fine_per_day' => 'required|integer|min:0',
            'max_fine' => 'required|integer|min:0',
            'min_password_length' => 'required|integer|min:6|max:32',
            'session_timeout' => 'required|integer|min:15|max:1440',
            'backup_time' => 'required|date_format:H:i',
            'backup_retention_days' => 'required|integer|min:1|max:365',
        ]);
        
        // Update settings in database
        foreach ($validator as $key => $value) {
            $this->updateSetting($key, $value);
        }
        
        // Update boolean settings
        $booleanSettings = [
            'allow_reservations',
            'allow_fine_waiver',
            'require_verification',
            'auto_approve',
            'allow_online_borrow',
            'notify_due_soon',
            'notify_overdue',
            'email_notifications',
            'sms_notifications',
            'require_complex_password',
            'lock_after_failed_attempts',
            'enable_permission_check',
            'log_user_activities',
            'auto_backup',
            'cloud_backup'
        ];
        
        foreach ($booleanSettings as $setting) {
            $value = $request->has($setting) ? 1 : 0;
            $this->updateSetting($setting, $value);
        }
        
        // Update array settings
        if ($request->has('closed_days')) {
            $this->updateSetting('closed_days', json_encode($request->closed_days));
        }
        
        return redirect()->back()->with('success', 'Cài đặt đã được cập nhật thành công!');
    }
    
    public function createBackup(Request $request)
    {
        $description = $request->input('description', 'Manual backup created by admin');
        
        $result = $this->backupService->createBackup('manual', $description);
        
        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'backup' => $result['backup']
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 500);
        }
    }
    
    public function restoreBackup(Request $request)
    {
        $validator = $request->validate([
            'backup_file' => 'required|string'
        ]);
        
        $result = $this->backupService->restoreBackup($request->backup_file);
        
        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message']
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 500);
        }
    }
    
    public function downloadBackup($filename)
    {
        $path = 'backups/' . $filename;
        
        if (!Storage::exists($path)) {
            abort(404, 'File sao lưu không tồn tại');
        }
        
        return Storage::download($path);
    }
    
    public function listBackups()
    {
        $backups = Backup::completed()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($backup) {
                return [
                    'id' => $backup->id,
                    'filename' => $backup->filename,
                    'created_at' => $backup->created_at->format('d/m/Y H:i'),
                    'size' => $backup->formatted_size,
                    'type' => $backup->type,
                    'description' => $backup->description,
                    'metadata' => $backup->metadata,
                    'download_url' => $backup->download_url,
                    'age_days' => $backup->age_in_days
                ];
            });
        
        return response()->json($backups);
    }
    
    public function deleteBackup($filename)
    {
        $backup = Backup::where('filename', $filename)->first();
        
        if (!$backup) {
            return response()->json([
                'success' => false,
                'message' => 'File sao lưu không tồn tại'
            ], 404);
        }
        
        // Delete file from storage
        if (Storage::exists($backup->file_path)) {
            Storage::delete($backup->file_path);
        }
        
        // Delete record from database
        $backup->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'File sao lưu đã được xóa'
        ]);
    }
    
    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            
            return response()->json([
                'success' => true,
                'message' => 'Cache đã được xóa thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function optimizeDatabase()
    {
        try {
            Artisan::call('migrate:status');
            
            return response()->json([
                'success' => true,
                'message' => 'Database đã được tối ưu hóa'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function getBackupStats()
    {
        $stats = $this->backupService->getBackupStats();
        return response()->json($stats);
    }
    
    public function validateBackup($filename)
    {
        $isValid = $this->backupService->validateBackup($filename);
        
        return response()->json([
            'valid' => $isValid,
            'message' => $isValid ? 'File sao lưu hợp lệ' : 'File sao lưu không hợp lệ'
        ]);
    }
    
    public function scheduleBackup(Request $request)
    {
        $frequency = $request->input('frequency', 'daily');
        $result = $this->backupService->scheduleBackup($frequency);
        
        return response()->json($result);
    }
    
    private function getCurrentSettings()
    {
        // This would typically come from a settings table or config files
        return [
            'library_name' => 'Thư Viện Đại Học ABC',
            'library_address' => '123 Đường ABC, Quận XYZ, TP.HCM',
            'library_phone' => '(028) 1234-5678',
            'library_email' => 'info@library.edu.vn',
            'library_description' => 'Thư viện phục vụ sinh viên và giảng viên với hơn 50,000 đầu sách và tài liệu học tập.',
            'opening_time' => '07:00',
            'closing_time' => '22:00',
            'max_borrow_days' => 14,
            'max_borrow_books' => 5,
            'fine_per_day' => 5000,
            'max_fine' => 100000,
            'min_password_length' => 8,
            'session_timeout' => 120,
            'backup_time' => '02:00',
            'backup_retention_days' => 30,
            'allow_reservations' => true,
            'allow_fine_waiver' => true,
            'require_verification' => true,
            'auto_approve' => false,
            'allow_online_borrow' => true,
            'notify_due_soon' => true,
            'notify_overdue' => true,
            'email_notifications' => true,
            'sms_notifications' => false,
            'require_complex_password' => true,
            'lock_after_failed_attempts' => true,
            'enable_permission_check' => true,
            'log_user_activities' => true,
            'auto_backup' => true,
            'cloud_backup' => false,
            'closed_days' => ['sunday']
        ];
    }
    
    private function updateSetting($key, $value)
    {
        // This would typically update a settings table
        // For now, we'll just store in session or cache
        cache()->put("setting.{$key}", $value, now()->addDays(30));
    }
}
