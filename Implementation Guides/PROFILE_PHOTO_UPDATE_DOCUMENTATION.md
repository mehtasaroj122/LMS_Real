# Profile Photo Management - Implementation Guide

## Overview
When a user changes their profile photo, the system now automatically:
1. **Deletes the previous photo** from the storage path and database
2. **Stores the new photo** at the same location (`storage/profile_pics/`)

## Changes Made

### 1. Updated `SettingController.php` - `update()` Method
**File:** `app/Http/Controllers/Admin/SettingController.php`

#### Key Improvements:
- **Better Path Handling:** Uses Laravel's `Storage::disk('public')` for more reliable file operations
- **Proper Path Extraction:** Correctly strips the `storage/` prefix from stored paths before deletion
- **Enhanced Logging:** Added detailed logs for debugging file operations

#### Implementation Logic:
```php
// Delete old photo if exists
if ($user->profile_photo) {
    // Extract the relative path from the stored value
    $oldPhotoPath = $user->profile_photo;
    
    // Remove 'storage/' prefix if it exists to get the path relative to public disk
    if (strpos($oldPhotoPath, 'storage/') === 0) {
        $oldPhotoPath = substr($oldPhotoPath, 8); // Remove 'storage/' prefix
    }
    
    // Delete from public disk
    if (Storage::disk('public')->exists($oldPhotoPath)) {
        Storage::disk('public')->delete($oldPhotoPath);
        \Log::info('Deleted old photo from public disk: ' . $oldPhotoPath);
    }
}

// Store new photo
$file = $request->file('profile_photo');
$filename = 'profile_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
$file->storeAs('profile_pics', $filename, 'public');
$validated['profile_photo'] = 'storage/profile_pics/' . $filename;
```

### 2. Updated `SettingController.php` - `removePhoto()` Method
**File:** `app/Http/Controllers/Admin/SettingController.php`

#### Improvements:
- Uses the same improved path handling as the `update()` method
- Properly deletes files from the public disk
- Better error handling and logging

#### Implementation:
```php
// Delete the file from storage
$photoPath = $user->profile_photo;

// Remove 'storage/' prefix if it exists to get the path relative to public disk
if (strpos($photoPath, 'storage/') === 0) {
    $photoPath = substr($photoPath, 8); // Remove 'storage/' prefix
}

// Delete from public disk
if (Storage::disk('public')->exists($photoPath)) {
    Storage::disk('public')->delete($photoPath);
    \Log::info('Deleted profile photo from public disk: ' . $photoPath);
}

// Update user record
$user->update(['profile_photo' => null]);
```

## File Storage Structure

```
storage/
├── app/
│   └── public/
│       └── profile_pics/
│           ├── profile_1_1704067200.jpg
│           ├── profile_1_1704067300.jpg  (old photo gets deleted)
│           └── profile_2_1704067400.png
```

## Database Storage
- **Table:** `users`
- **Column:** `profile_photo`
- **Format:** `storage/profile_pics/profile_{user_id}_{timestamp}.{extension}`

## How It Works

### Upload Flow:
1. User selects a new photo from the file upload input
2. JavaScript shows a preview of the selected photo
3. Form is submitted with the `profile_photo` file
4. Backend controller receives the request
5. **Old photo is deleted** from storage and path is removed from database
6. **New photo is stored** in `storage/profile_pics/`
7. Database is updated with the new photo path
8. Activity log is recorded
9. Success response is sent back to frontend

### Remove Photo Flow:
1. User clicks the "Remove Photo" button
2. System asks for confirmation
3. Photo file is deleted from storage
4. Database is updated (set to NULL)
5. Activity log is recorded
6. Frontend UI is updated to show the default user icon

## Storage Location
- **Disk:** Public disk (configured in `config/filesystems.php`)
- **Root Path:** `storage/app/public/`
- **Accessible URL:** `/storage/profile_pics/{filename}`
- **Asset Helper:** `asset('storage/profile_pics/filename')`

## Validation Rules
```php
'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
```

- Optional (nullable)
- Must be an image file
- Allowed formats: JPEG, PNG, JPG, GIF
- Maximum size: 2MB

## Activity Logging
Both operations are logged for audit trail:
- `profile_updated` - When profile photo is changed
- `profile_photo_removed` - When profile photo is removed

## Frontend Implementation
**File:** `resources/views/Admin/Settings.blade.php`

### Features:
- Real-time photo preview on selection
- Upload button to change photo
- Remove button to delete photo
- Toast notifications for success/error feedback
- Form validation before submission

### Form Submission:
- Uses AJAX with FormData (supports file uploads)
- Sends CSRF token for security
- Accepts JSON response

## Error Handling
- Try-catch blocks catch any storage exceptions
- Detailed error messages logged for debugging
- User-friendly error notifications displayed
- Validation errors are returned to frontend

## Testing Checklist
- [ ] Upload a profile photo successfully
- [ ] Verify old photo is deleted from storage
- [ ] Verify old photo path is removed from database
- [ ] Upload a new photo and confirm previous one is deleted
- [ ] Remove profile photo completely
- [ ] Verify database record is NULL when photo is removed
- [ ] Test with different image formats (JPG, PNG, GIF)
- [ ] Test with file size limits
- [ ] Verify activity logs are created
- [ ] Check browser console for any errors

## Configuration References
- Storage Disk: `config/filesystems.php` - `public` disk
- File Size: Can be adjusted in validation rule
- File Extensions: Can be modified in validation rule
- Path Format: Defined in controller logic

