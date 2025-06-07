# Storage Path Consistency Fix

## Problem
Laravel storage had two major issues:
1. **Inconsistent directory naming** between database display names and file system paths:
   - Database: `Innovation Projects` (spaces, title case)
   - Storage: Mixed between `Innovation Projects` and `innovation-projects`

2. **UI move operations didn't move physical files**: When users moved documents between directories in the UI, only the database was updated but the physical file remained in the old location.

## Solution Implemented

### 1. Backend Path Sanitization
- Updated `DocumentController::getDirectoryPath()` to sanitize directory names for storage
- Added `sanitizeDirectoryName()` method that converts "Innovation Projects" → "innovation-projects"
- **Rule**: Storage paths are always lowercase with hyphens instead of spaces

### 2. Physical File Movement on UI Operations
- Updated `DocumentController::update()` to detect directory changes
- Added `moveDocumentFile()` method that physically moves files when directory changes
- Added automatic cleanup of empty directories after file moves
- **Result**: UI move operations now move both database records AND physical files

### 3. Migration Command
Created `storage:fix-paths` command to fix existing inconsistencies:

```bash
# Check what would be changed (safe)
php artisan storage:fix-paths --dry-run

# Actually fix the paths
php artisan storage:fix-paths
```

### 4. Directory Management
- Database stores user-friendly display names (e.g., "Innovation Projects")
- File system uses sanitized paths (e.g., "innovation-projects")
- This separation allows clean URLs while maintaining user experience

## Key Changes

### Files Modified:
1. `app/Http/Controllers/DocumentController.php`
   - Added `sanitizeDirectoryName()` method for consistent path naming
   - Updated `getDirectoryPath()` to use sanitized names for storage
   - **NEW**: Enhanced `update()` method to detect directory changes
   - **NEW**: Added `moveDocumentFile()` method to physically move files
   - **NEW**: Added `cleanupEmptyDirectory()` method for cleanup

2. `app/Http/Controllers/DirectoryController.php`
   - Added comments about name handling
   - Ensured consistent trimming of directory names

3. `app/Console/Commands/FixStoragePathConsistency.php`
   - New command to migrate existing files
   - Handles file movement and database updates
   - Cleans up empty directories

## How It Works

1. **Directory Display**: Users see "Innovation Projects" in the UI
2. **Storage Path**: Files are stored in `/innovation-projects/`
3. **File Upload**: New uploads automatically use consistent naming
4. **File Movement**: UI move operations now move physical files too
5. **Backward Compatible**: Old files were migrated automatically

## UI Move Operations (NEW)
When users move documents between directories in the UI:
1. **Database Update**: Document's `directory_id` is updated
2. **Physical File Move**: File is moved to new sanitized directory path
3. **Cleanup**: Empty directories are automatically removed
4. **Logging**: All operations are logged for debugging

## Verification
Run the dry-run command to verify no inconsistencies remain:
```bash
php artisan storage:fix-paths --dry-run
```

Should show: "Files that would be moved: 0"