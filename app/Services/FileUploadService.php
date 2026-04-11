<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Cloudinary\Cloudinary;
use Cloudinary\Transformation\Resize;

class FileUploadService
{
    /**
     * Ensure directory path is valid and never empty
     *
     * @param string|null $directory
     * @param string $default
     * @return string
     */
    private static function ensureDirectory(?string $directory, string $default = 'images'): string
    {
        // Default must not be empty
        if (empty($default) || trim($default) === '') {
            $default = 'images';
        }
        $default = trim($default, '/\\');

        // Handle null or empty
        if (is_null($directory) || $directory === '') {
            return $default;
        }

        // Trim whitespace
        $directory = trim($directory);
        if ($directory === '' || empty($directory)) {
            return $default;
        }

        // Remove leading/trailing slashes
        $directory = trim($directory, '/\\');
        if ($directory === '' || empty($directory) || $directory === '.') {
            return $default;
        }

        // Final check - ensure it's not empty after all processing
        if (empty($directory) || trim($directory) === '') {
            return $default;
        }

        return $directory;
    }

    /**
     * Upload image file với validation và security
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param array $options
     * @return array
     * @throws \Exception
     */
    public static function uploadImage(UploadedFile $file, string $directory = 'images', array $options = []): array
    {
        // Default options
        $maxSize = $options['max_size'] ?? 2048; // KB
        $allowedMimes = $options['allowed_mimes'] ?? ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
        $resize = $options['resize'] ?? false;
        $width = $options['width'] ?? 800;
        $height = $options['height'] ?? 800;
        $disk = $options['disk'] ?? 'public';

        // Validate and sanitize directory - ensure it's never empty
        $directory = self::ensureDirectory($directory, 'images');

        // Validate file
        if (!$file->isValid()) {
            throw new \Exception('File không hợp lệ.');
        }

        // Validate MIME type
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, $allowedMimes)) {
            throw new \Exception('Định dạng file không được phép. Chỉ chấp nhận: ' . implode(', ', $allowedMimes));
        }

        // Validate file size
        $fileSize = $file->getSize() / 1024; // KB
        if ($fileSize > $maxSize) {
            throw new \Exception("Kích thước file vượt quá giới hạn ({$maxSize}KB).");
        }

        // Generate unique filename - detect real MIME to set correct extension
        // Lấy extension từ MIME thực của file, KHÔNG tin extension từ browser gửi lên
        $mimeToExt = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        ];
        $extension = $mimeToExt[$mimeType] ?? 'jpg';
        $filename = Str::uuid() . '.' . $extension;

        // Ensure directory is valid and never empty - sanitize properly
        // Store original for logging
        $originalDirectory = $directory;
        $directory = self::ensureDirectory($directory, 'images');

        // Multiple validation layers to ensure directory is never empty
        $directory = trim($directory, '/\\');
        if (empty($directory) || $directory === '' || $directory === '.' || $directory === '..') {
            \Log::warning('Directory was invalid, using default', [
                'original' => $originalDirectory,
                'after_ensure' => $directory,
            ]);
            $directory = 'images';
        }

        // Force default if still empty after all processing
        if (empty($directory) || trim($directory) === '' || strlen($directory) === 0) {
            \Log::warning('Directory was empty, forcing default', [
                'original' => $originalDirectory,
                'after_trim' => $directory,
            ]);
            $directory = 'images';
        }

        // Final validation - directory MUST have a value and be a valid string
        if (empty($directory) || !is_string($directory) || trim($directory) === '' || strlen($directory) === 0) {
            \Log::error('Directory validation failed', [
                'original' => $originalDirectory,
                'directory' => $directory,
                'type' => gettype($directory),
                'is_empty' => empty($directory),
                'trimmed' => trim($directory),
                'length' => strlen($directory ?? ''),
            ]);
            throw new \Exception('Thư mục không được để trống. Vui lòng kiểm tra cấu hình. Directory: ' . var_export($directory, true));
        }

        // Log directory value for debugging
        \Log::info('Directory validated successfully', [
            'original' => $originalDirectory,
            'final_directory' => $directory,
            'length' => strlen($directory),
            'type' => gettype($directory),
        ]);

        try {
            // Ensure directory exists - create if not exists
            try {
                if (!Storage::disk($disk)->exists($directory)) {
                    $created = Storage::disk($disk)->makeDirectory($directory, 0755, true);
                    if (!$created && !Storage::disk($disk)->exists($directory)) {
                        throw new \Exception('Không thể tạo thư mục: ' . $directory);
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Directory creation error', [
                    'directory' => $directory,
                    'error' => $e->getMessage(),
                ]);
                // Check if directory exists despite the error
                if (!Storage::disk($disk)->exists($directory)) {
                    throw new \Exception('Không thể tạo thư mục: ' . $e->getMessage());
                }
            }

            // Build final path early
            $path = $directory . '/' . $filename;

            // Validate path is not empty
            if (empty($path) || trim($path) === '' || !str_contains($path, $filename)) {
                throw new \Exception('Đường dẫn file không hợp lệ');
            }

            // Resize image if needed (requires intervention/image package)
            if ($resize && in_array($mimeType, ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'])) {
                // Check if Intervention Image is available
                if (class_exists('Intervention\Image\Facades\Image')) {
                    try {
                        $image = \Intervention\Image\Facades\Image::make($file);
                        $image->resize($width, $height, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        });

                        // Save resized image using put() - more reliable
                        $saved = Storage::disk($disk)->put($path, (string) $image->encode());
                        if (!$saved) {
                            throw new \Exception('Không thể lưu ảnh đã resize');
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Image resize failed, using original', [
                            'error' => $e->getMessage(),
                        ]);
                        // Fallback to original if resize fails
                        self::saveOriginalFile($file, $directory, $filename, $disk);
                    }
                } else {
                    // Fallback: save original file if Intervention Image not available
                    self::saveOriginalFile($file, $directory, $filename, $disk);
                }
            } else {
                // Save original file
                self::saveOriginalFile($file, $directory, $filename, $disk);
            }

            // Rebuild path to ensure consistency
            $path = $directory . '/' . $filename;

            // Final validation - ensure file was saved
            if (!Storage::disk($disk)->exists($path)) {
                \Log::error('File not saved', [
                    'path' => $path,
                    'directory' => $directory,
                    'filename' => $filename,
                ]);
                throw new \Exception('Không thể lưu file. Vui lòng kiểm tra quyền ghi file.');
            }

            // Validate path is not empty before returning
            if (empty($path) || trim($path) === '') {
                throw new \Exception('Đường dẫn file không hợp lệ.');
            }

            return [
                'success' => true,
                'path' => $path,
                'filename' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'size' => $fileSize,
                'mime_type' => $mimeType,
                'url' => Storage::disk($disk)->url($path),
            ];
        } catch (\Exception $e) {
            \Log::error('File upload error', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
                'directory' => $directory ?? 'unknown',
                'path' => $path ?? 'unknown',
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \Exception('Lỗi khi upload file: ' . $e->getMessage());
        }
    }

    /**
     * Save original file content to storage
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param string $filename
     * @param string $disk
     * @return void
     * @throws \Exception
     */
    private static function saveOriginalFile(UploadedFile $file, string $directory, string $filename, string $disk): void
    {
        // Validate and sanitize directory - MUST NOT BE EMPTY
        $directory = trim($directory, '/\\');
        if (empty($directory) || $directory === '' || $directory === '.' || $directory === '..') {
            $directory = 'images';
        }

        // Final check - directory must have a value
        if (empty($directory) || trim($directory) === '') {
            \Log::error('Directory is empty after sanitization', [
                'original_directory' => $directory,
            ]);
            throw new \Exception('Thư mục không được để trống. Vui lòng kiểm tra cấu hình.');
        }

        // Validate filename is not empty
        if (empty($filename) || trim($filename) === '') {
            throw new \Exception('Tên file không được để trống');
        }

        // Build full path
        $path = $directory . '/' . $filename;

        // Log for debugging
        \Log::info('Saving file', [
            'directory' => $directory,
            'filename' => $filename,
            'path' => $path,
            'disk' => $disk,
        ]);

        // Ensure directory exists - CRITICAL: directory must exist before saving
        try {
            if (!Storage::disk($disk)->exists($directory)) {
                $created = Storage::disk($disk)->makeDirectory($directory, 0755, true);
                if (!$created && !Storage::disk($disk)->exists($directory)) {
                    throw new \Exception('Không thể tạo thư mục: ' . $directory);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to create directory', [
                'directory' => $directory,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \Exception('Không thể tạo thư mục: ' . $e->getMessage());
        }

        // Get file content - use multiple methods for reliability
        $fileContent = null;

        // Method 1: Try getRealPath() - most reliable
        try {
            $realPath = $file->getRealPath();
            if ($realPath && file_exists($realPath) && is_readable($realPath)) {
                $fileContent = file_get_contents($realPath);
                if ($fileContent !== false && !empty($fileContent)) {
                    \Log::info('File content read from getRealPath', ['size' => strlen($fileContent)]);
                }
            }
        } catch (\Exception $e) {
            \Log::warning('getRealPath failed', ['error' => $e->getMessage()]);
        }

        // Method 2: Try getPathname()
        if (!$fileContent || empty($fileContent)) {
            try {
                $pathname = $file->getPathname();
                if ($pathname && file_exists($pathname) && is_readable($pathname)) {
                    $fileContent = file_get_contents($pathname);
                    if ($fileContent !== false && !empty($fileContent)) {
                        \Log::info('File content read from getPathname', ['size' => strlen($fileContent)]);
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('getPathname failed', ['error' => $e->getMessage()]);
            }
        }

        // Method 3: Use file->get() - Laravel's method
        if (!$fileContent || empty($fileContent)) {
            try {
                $fileContent = $file->get();
                if ($fileContent && !empty($fileContent)) {
                    \Log::info('File content read from file->get()', ['size' => strlen($fileContent)]);
                }
            } catch (\Exception $e) {
                \Log::warning('file->get() failed', ['error' => $e->getMessage()]);
            }
        }

        // Validate file content
        if (!$fileContent || empty($fileContent)) {
            \Log::error('Could not read file content', [
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
            ]);
            throw new \Exception('Không thể đọc nội dung file. Vui lòng thử lại.');
        }

        // Save file using put() - more reliable than putFileAs
        try {
            $saved = Storage::disk($disk)->put($path, $fileContent);
            if (!$saved) {
                throw new \Exception('Storage::put() returned false');
            }

            // Verify file was saved
            if (!Storage::disk($disk)->exists($path)) {
                throw new \Exception('File was not saved - verification failed');
            }

            \Log::info('File saved successfully', ['path' => $path]);
            return;
        } catch (\Exception $e) {
            \Log::error('Failed to save file', [
                'error' => $e->getMessage(),
                'directory' => $directory,
                'filename' => $filename,
                'path' => $path,
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \Exception('Không thể lưu file: ' . $e->getMessage());
        }
    }

    /**
     * Upload file (any type)
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param array $options
     * @return array
     * @throws \Exception
     */
    public static function uploadFile(UploadedFile $file, string $directory = 'files', array $options = []): array
    {
        $maxSize = $options['max_size'] ?? 5120; // 5MB default
        $allowedMimes = $options['allowed_mimes'] ?? [];
        $disk = $options['disk'] ?? 'public';

        // Validate and sanitize directory - ensure it's never empty
        $originalDirectory = $directory;
        $directory = self::ensureDirectory($directory, 'files');
        $directory = trim($directory, '/\\');
        if (empty($directory) || $directory === '' || $directory === '.' || $directory === '..') {
            $directory = 'files';
        }

        // Final validation
        if (empty($directory) || !is_string($directory) || trim($directory) === '') {
            \Log::error('Directory validation failed in uploadFile', [
                'original' => $originalDirectory,
                'directory' => $directory,
            ]);
            throw new \Exception('Thư mục không được để trống.');
        }

        // Validate file
        if (!$file->isValid()) {
            throw new \Exception('File không hợp lệ.');
        }

        // Validate MIME type if specified
        if (!empty($allowedMimes)) {
            $mimeType = $file->getMimeType();
            if (!in_array($mimeType, $allowedMimes)) {
                throw new \Exception('Định dạng file không được phép.');
            }
        }

        // Validate file size
        $fileSize = $file->getSize() / 1024; // KB
        if ($fileSize > $maxSize) {
            throw new \Exception("Kích thước file vượt quá giới hạn ({$maxSize}KB).");
        }

        // Generate unique filename
        $extension = $file->getClientOriginalExtension() ?: 'bin';
        $filename = Str::uuid() . '.' . strtolower($extension);
        $path = $directory . '/' . $filename;

        try {
            // Ensure directory exists
            try {
                if (!Storage::disk($disk)->exists($directory)) {
                    Storage::disk($disk)->makeDirectory($directory, 0755, true);
                }
            } catch (\Exception $e) {
                if (!Storage::disk($disk)->exists($directory)) {
                    throw new \Exception('Không thể tạo thư mục: ' . $e->getMessage());
                }
            }

            // Get file content and save using put() instead of putFileAs
            $fileContent = null;

            // Try getRealPath() first
            $realPath = $file->getRealPath();
            if ($realPath && file_exists($realPath) && is_readable($realPath)) {
                $fileContent = file_get_contents($realPath);
            }

            // Fallback to getPathname()
            if (!$fileContent || empty($fileContent)) {
                $pathname = $file->getPathname();
                if ($pathname && file_exists($pathname) && is_readable($pathname)) {
                    $fileContent = file_get_contents($pathname);
                }
            }

            // Last resort: use file->get()
            if (!$fileContent || empty($fileContent)) {
                $fileContent = $file->get();
            }

            // Validate file content
            if (!$fileContent || empty($fileContent)) {
                throw new \Exception('Không thể đọc nội dung file.');
            }

            // Save file using put() - more reliable than putFileAs
            $saved = Storage::disk($disk)->put($path, $fileContent);
            if (!$saved) {
                throw new \Exception('Không thể lưu file.');
            }

            // Verify file was saved
            if (!Storage::disk($disk)->exists($path)) {
                throw new \Exception('File không được lưu thành công.');
            }

            return [
                'success' => true,
                'path' => $path,
                'filename' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'size' => $fileSize,
                'mime_type' => $file->getMimeType(),
                'url' => Storage::disk($disk)->url($path),
            ];
        } catch (\Exception $e) {
            \Log::error('File upload error', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
                'directory' => $directory,
                'path' => $path,
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \Exception('Lỗi khi upload file: ' . $e->getMessage());
        }
    }

    /**
     * Delete file
     *
     * @param string $path
     * @param string $disk
     * @return bool
     */
    public static function deleteFile(string $path, string $disk = 'public'): bool
    {
        try {
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->delete($path);
            }
            return false;
        } catch (\Exception $e) {
            \Log::error('File delete error', [
                'error' => $e->getMessage(),
                'path' => $path,
            ]);
            return false;
        }
    }

    /**
     * Validate image file
     *
     * @param UploadedFile $file
     * @param array $options
     * @return array
     */
    public static function validateImage(UploadedFile $file, array $options = []): array
    {
        $maxSize = $options['max_size'] ?? 2048; // KB
        $allowedMimes = $options['allowed_mimes'] ?? ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
        $minWidth = $options['min_width'] ?? null;
        $minHeight = $options['min_height'] ?? null;
        $maxWidth = $options['max_width'] ?? null;
        $maxHeight = $options['max_height'] ?? null;

        $errors = [];

        if (!$file->isValid()) {
            $errors[] = 'File không hợp lệ.';
            return ['valid' => false, 'errors' => $errors];
        }

        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, $allowedMimes)) {
            $errors[] = 'Định dạng file không được phép.';
        }

        $fileSize = $file->getSize() / 1024; // KB
        if ($fileSize > $maxSize) {
            $errors[] = "Kích thước file vượt quá giới hạn ({$maxSize}KB).";
        }

        // Check dimensions if needed
        if ($minWidth || $minHeight || $maxWidth || $maxHeight) {
            try {
                // Check if Intervention Image is available
                if (!class_exists('Intervention\Image\Facades\Image')) {
                    $errors[] = 'Không thể kiểm tra kích thước ảnh. Vui lòng cài đặt package intervention/image.';
                } else {
                    $image = \Intervention\Image\Facades\Image::make($file);
                    $width = $image->width();
                    $height = $image->height();

                    if ($minWidth && $width < $minWidth) {
                        $errors[] = "Chiều rộng ảnh tối thiểu là {$minWidth}px.";
                    }
                    if ($minHeight && $height < $minHeight) {
                        $errors[] = "Chiều cao ảnh tối thiểu là {$minHeight}px.";
                    }
                    if ($maxWidth && $width > $maxWidth) {
                        $errors[] = "Chiều rộng ảnh tối đa là {$maxWidth}px.";
                    }
                    if ($maxHeight && $height > $maxHeight) {
                        $errors[] = "Chiều cao ảnh tối đa là {$maxHeight}px.";
                    }
                }
            } catch (\Exception $e) {
                $errors[] = 'Không thể đọc thông tin ảnh.';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Upload file lên Cloudinary
     *
     * @param UploadedFile $file
     * @param string $folder
     * @param array $options
     * @return array
     * @throws \Exception
     */
    public static function uploadToCloudinary(UploadedFile $file, string $folder = 'cccd_images', array $options = []): array
    {
        $cloudinaryUrl = env('CLOUDINARY_URL');
        if (!$cloudinaryUrl) {
            throw new \Exception('CLOUDINARY_URL chưa được cấu hình trong file .env');
        }

        try {
            $cloudinary = new Cloudinary($cloudinaryUrl);

            $uploadOptions = [
                'folder' => $folder,
                'resource_type' => 'auto',
            ];

            if (isset($options['public_id'])) {
                $uploadOptions['public_id'] = $options['public_id'];
            }

            $result = $cloudinary->uploadApi()->upload($file->getRealPath(), $uploadOptions);

            return [
                'success' => true,
                'path' => $result['secure_url'],
                'url' => $result['secure_url'],
                'public_id' => $result['public_id'],
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize() / 1024,
                'mime_type' => $file->getMimeType(),
            ];
        } catch (\Exception $e) {
            \Log::error('Cloudinary upload error', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \Exception('Lỗi khi upload lên Cloudinary: ' . $e->getMessage());
        }
    }
}

