# Phase 4 Completion Report: Admin PDF Course Material Management

**Date:** October 10, 2026  
**Project:** Noras API  
**Phase:** 4 - PDF Course Material Management  
**Status:** ✅ COMPLETED

---

## Executive Summary

Phase 4 successfully implements secure PDF course material management with full admin control and read-only student access. All 107 automated tests pass (239 assertions). PDF upload security includes MIME validation, file signature verification, and private storage. Version tracking ensures replaced materials don't break future AI-generated study content. Authorization correctly enforces Phase 2 account status checks.

---

## What Was Implemented

### 1. Database Schema

**Table:** `course_materials`

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint (PK) | Primary key |
| `course_offering_id` | bigint (FK) | Links to specific course offering |
| `uploaded_by` | bigint (FK) | Admin who uploaded (users.id) |
| `title` | string(255) | Display title (required) |
| `description` | text | Optional description |
| `original_filename` | string | User's original filename |
| `stored_filename` | string (unique) | UUID-based server filename |
| `file_path` | string | Full storage path |
| `file_size` | bigint | Size in bytes |
| `mime_type` | string | MIME type (application/pdf) |
| `status` | enum('draft', 'published') | Publication status |
| `version` | integer | Version number (starts at 1) |
| `replaces_material_id` | bigint (FK, nullable) | Links to replaced material |
| `timestamps` | datetime | created_at, updated_at |

**Indexes:**
- `course_offering_id` - Fast filtering by offering
- `status` - Fast published/draft queries
- `(course_offering_id, status)` - Composite for student queries
- `uploaded_by` - Admin activity tracking

**Foreign Keys:**
- `course_offering_id` → `course_offerings.id` (cascade delete)
- `uploaded_by` → `users.id` (restrict delete - preserve audit trail)
- `replaces_material_id` → `course_materials.id` (null on delete)

**Relationships:**
- CourseMaterial `belongsTo` CourseOffering
- CourseMaterial `belongsTo` User (uploader)
- CourseMaterial `belongsTo` CourseMaterial (replaces)
- CourseMaterial `hasMany` CourseMaterial (replacedBy)
- CourseOffering `hasMany` CourseMaterial

---

### 2. Admin API Endpoints

All admin endpoints require `auth:sanctum` + `admin` middleware.

#### **POST /api/v1/admin/materials** - Upload PDF
**Request:** multipart/form-data
- `course_offering_id` (required, integer, exists)
- `title` (required, string, max 255)
- `description` (optional, string, max 2000)
- `file` (required, file, PDF only, max 50MB)

**Security Checks:**
- MIME type validation (`application/pdf`)
- File signature verification (checks for `%PDF` magic bytes)
- Size limit enforced (50MB configurable)
- UUID-based filename generation (prevents path traversal)
- Stored outside public web root (`storage/app/private/`)

**Response:** 201 Created
```json
{
  "status": "success",
  "message": "Course material uploaded successfully",
  "data": {
    "material": {
      "id": 1,
      "title": "Week 1 Lecture Notes",
      "status": "draft",
      "version": 1,
      "file_size": 524288,
      "course_offering": {...}
    }
  }
}
```

**Transaction Safety:**
- Database and file operations wrapped in transaction
- File cleanup on database failure
- Rollback on any exception

#### **GET /api/v1/admin/materials** - List Materials
**Query Parameters:**
- `course_offering_id` (optional) - Filter by offering
- `status` (optional) - Filter by draft/published
- `uploaded_by` (optional) - Filter by admin
- `search` (optional) - Search title and filename

**Pagination:** 20 per page

**Response:** 200 OK with pagination metadata

#### **GET /api/v1/admin/materials/{id}** - View Material
**Response:** 200 OK with full material details including relationships

#### **PUT /api/v1/admin/materials/{id}** - Update Metadata
**Request:** application/json
- `title` (optional, string)
- `description` (optional, string)

**Note:** Cannot change file, course_offering_id, or uploader

**Response:** 200 OK with updated material

#### **POST /api/v1/admin/materials/{id}/publish** - Publish Material
Marks material as published, making it visible to students.

**Business Rule:** Cannot publish already published material (400 error)

**Response:** 200 OK

#### **POST /api/v1/admin/materials/{id}/unpublish** - Unpublish Material
Marks material as draft, hiding from students.

**Business Rule:** Cannot unpublish already draft material (400 error)

**Response:** 200 OK

#### **POST /api/v1/admin/materials/{id}/replace** - Replace PDF
**Request:** multipart/form-data
- `file` (required, PDF)
- `title` (optional, inherits from old if not provided)
- `description` (optional, inherits from old if not provided)

**Versioning Logic:**
1. Creates **new material record** (not in-place update)
2. Increments version number
3. Links to original via `replaces_material_id`
4. New material starts as **draft** (must be published separately)
5. Old material remains in database (audit trail + future AI reference)

**Why This Design:**
- Prevents AI-generated content from appearing current after replacement
- Maintains complete version history
- Allows rollback if needed
- Admin explicitly publishes new version

**Response:** 201 Created with new material

#### **DELETE /api/v1/admin/materials/{id}** - Delete Material
Permanently deletes material record and file.

**Transaction Safety:**
- Deletes database record first
- Then deletes file from storage
- Rollback on failure

**Response:** 200 OK

---

### 3. Student API Endpoints

All student endpoints require `auth:sanctum` + `approved` middleware.

#### **GET /api/v1/materials** - List Published Materials
**Filters:**
- Only `status = 'published'`
- `course_offering_id` (optional query param)
- `search` (optional - title/filename)

**No pagination** - students typically filter to small subsets

**Response:** 200 OK with array of published materials

#### **GET /api/v1/materials/{id}** - View Published Material
**Access Control:** Only published materials accessible

**Response:** 200 OK or 404 if draft/not found

#### **GET /api/v1/materials/{id}/download** - Download PDF
**Security:**
- Serves through authorized controller endpoint
- Does not expose internal file paths
- Sets proper Content-Type and Content-Disposition headers
- Only published materials downloadable

**Response:** 
- Success: PDF file download with original filename
- 404 if material not found, draft, or file missing

---

### 4. File Storage Security

**Storage Configuration:**
- **Disk:** `local` (Laravel's default private disk)
- **Root:** `storage/app/private/` (outside public web root)
- **Path Pattern:** `course-materials/{uuid}.pdf`
- **Filename Generation:** UUID v4 (prevents collisions and guessing)

**Security Measures:**

1. **MIME Type Validation**
   - Laravel validates `mimes:pdf` rule
   - Max size enforced server-side (50MB)

2. **File Signature Verification**
   ```php
   private function isValidPdf($file): bool {
       $handle = fopen($file->getRealPath(), 'r');
       $header = fread($handle, 4);
       fclose($handle);
       return strpos($header, '%PDF') === 0;
   }
   ```

3. **Path Traversal Prevention**
   - Server generates all filenames (no user input)
   - UUID format prevents directory traversal attempts
   - Storage::disk('local') handles path sanitization

4. **No Direct File Execution**
   - Files stored with `.pdf` extension only
   - Served through Laravel controller (not direct web access)
   - Content-Type header explicitly set to `application/pdf`

5. **Authorized Downloads Only**
   - All downloads require authentication
   - Middleware enforces account status checks
   - Controller verifies publication status

**Upload Size Limit:** 50MB (configurable via `MAX_FILE_SIZE` constant)

**Limitations:**
- ❌ No malware scanning implemented (would require ClamAV or third-party service)
- ❌ Files not encrypted at rest (relies on filesystem permissions)
- ❌ No CDN integration for scalability

---

### 5. Version Tracking Design

**Problem:** When admin replaces a PDF, future AI phases will generate notes/quizzes. Old generated content must not appear current.

**Solution:** Replacement creates new material record

**Version Chain:**
```
Material v1 (id=1, version=1, replaces_material_id=null)
    ↓ Admin replaces
Material v2 (id=2, version=2, replaces_material_id=1)
    ↓ Admin replaces
Material v3 (id=3, version=3, replaces_material_id=2)
```

**Key Properties:**
- Each replacement is a new record (separate ID)
- Version number increments automatically
- New version starts as draft (admin must publish)
- Old versions remain in database (audit trail)
- `replaces_material_id` links version chain

**Future AI Integration:**
- AI-generated content (notes, quizzes) will link to `course_material_id`
- If material replaced, generated content remains linked to old version
- UI can show "Generated from v1 (superseded by v2)"
- Students won't mistake old notes as current

**Alternative Considered:** In-place file replacement
- ❌ Rejected: Would make old AI content appear current
- ❌ Rejected: Loses audit trail
- ❌ Rejected: Can't rollback if new version has issues

---

### 6. Authorization and Security

**Admin Upload Requirements:**
- ✅ Authenticated (`auth:sanctum` middleware)
- ✅ Admin role (`admin` middleware checks `role = 'admin'`)
- ✅ Account approved (middleware refreshes user and checks `account_status`)

**Student Access Requirements:**
- ✅ Authenticated (`auth:sanctum` middleware)
- ✅ Approved status (`approved` middleware)
- ✅ Material published (`status = 'published'` enforced in queries)

**Server-Side Validation:**
- ✅ Course offering existence validated
- ✅ Uploader ID set from `auth()->id()` (not client input)
- ✅ File validation comprehensive (MIME + signature)
- ✅ Publication privileges controlled by role (students can't publish)
- ✅ Version numbers auto-incremented (not client input)

**Token Validity After Status Change:**
- ✅ Middleware calls `$user->refresh()` before authorization
- ✅ Suspended/rejected users lose access even with old tokens
- ✅ Tested in `StudentMaterialAccessTest::test_approved_student_token_loses_access_after_suspension`

**Phase 1-3 Preservation:**
- ✅ All 81 previous tests still pass
- ✅ No changes to authentication system
- ✅ No changes to catalog management
- ✅ No changes to student approval workflow

---

### 7. Reliability and Maintainability

**Database Transactions:**
- Upload: File storage + database insert wrapped in transaction
- Replace: Old material update + new material creation + file storage wrapped
- Delete: Material deletion + file deletion wrapped

**Cleanup and Rollback:**
- Database failure → File deleted from storage
- File storage failure → Transaction rolled back
- Exception handling prevents orphaned files

**Consistent Response Format:**
```json
{
  "status": "success|error",
  "message": "Human-readable message",
  "data": { ... },
  "errors": { ... }  // Only on validation failure
}
```

**HTTP Status Codes:**
- 200 OK - Successful retrieval/update
- 201 Created - Material uploaded/replaced
- 400 Bad Request - Business logic error (e.g., already published)
- 401 Unauthorized - Not authenticated
- 403 Forbidden - Authenticated but not authorized
- 404 Not Found - Material doesn't exist or not published
- 422 Unprocessable Entity - Validation failure
- 500 Internal Server Error - Server/database failure

**Error Handling:**
- Safe errors (no stack traces or internal paths exposed)
- Validation errors include field-specific messages
- File not found returns JSON (not Laravel exception page)

**Dependencies:**
- ✅ No new packages added
- ✅ Uses Laravel's built-in Storage facade
- ✅ Uses Laravel's validation rules
- ✅ Uses framework's filesystem testing

---

## Files Changed/Created

### New Files (7):

**Migration:**
- `Server/database/migrations/2026_10_10_185953_create_course_materials_table.php`

**Model:**
- `Server/app/Models/CourseMaterial.php`

**Controllers:**
- `Server/app/Http/Controllers/Api/V1/Admin/CourseMaterialController.php` (admin CRUD)
- `Server/app/Http/Controllers/Api/V1/CourseMaterialController.php` (student read-only)

**Factory:**
- `Server/database/factories/CourseMaterialFactory.php`

**Tests:**
- `Server/tests/Feature/Api/V1/Admin/CourseMaterialManagementTest.php` (14 tests)
- `Server/tests/Feature/Api/V1/StudentMaterialAccessTest.php` (12 tests)

**Documentation:**
- `Noras-API-Phase4.postman_collection.json`

### Modified Files (2):

**Models:**
- `Server/app/Models/CourseOffering.php` - Added `materials()` relationship

**Routes:**
- `Server/routes/api.php` - Added 8 admin routes, 3 student routes

---

## Automated Test Results

**Test Database:** `noras_test` (isolated, never touches development database)

**Test Execution:**
```
PHPUnit 11.4.3

Tests:    107 passed (239 assertions)
Duration: 11.47s

OK (107 tests, 239 assertions)
```

**Test Breakdown:**

### Phase 1 Regression (3 tests) ✅
- Health check endpoint
- Response format
- Database connectivity

### Phase 2 Regression (51 tests) ✅
- Authentication (registration, login, logout)
- Authorization (admin, approved middleware)
- Student approval workflow
- Token validity after status changes
- Mass assignment protection

### Phase 3 Regression (27 tests) ✅
- University/Department/Course/CourseOffering CRUD
- Student catalog access
- Filtering and search
- Safe deletion

### Phase 4 New Tests (26 tests) ✅

**Admin Material Management (14 tests):**
1. ✅ Admin can upload PDF material with metadata
2. ✅ Admin can list materials with filters (offering, status, uploader, search)
3. ✅ Admin can update material metadata (title, description)
4. ✅ Admin can publish draft material
5. ✅ Admin can unpublish published material
6. ✅ Admin can replace material creating new version
7. ✅ Admin can delete material (removes file and record)
8. ✅ Invalid file type rejected (validation error)
9. ✅ Invalid PDF signature rejected (not real PDF)
10. ✅ Invalid course offering rejected
11. ✅ Student cannot upload material (403 Forbidden)
12. ✅ Unauthenticated cannot upload material (401)
13. ✅ Cannot publish already published material (400)
14. ✅ Cannot unpublish draft material (400)

**Student Material Access (12 tests):**
1. ✅ Approved student can view published materials list
2. ✅ Approved student can view single published material
3. ✅ Student cannot view draft material (404)
4. ✅ Approved student can download published material
5. ✅ Student cannot download draft material (404)
6. ✅ Pending student cannot access materials (403)
7. ✅ Suspended student cannot access materials (403)
8. ✅ Unauthenticated user cannot access materials (401)
9. ✅ Student can filter materials by course offering
10. ✅ Student can search materials by title/filename
11. ✅ Download returns 404 if file missing from storage
12. ✅ Token loses access after suspension (middleware refresh test)

**Storage Testing:**
- ✅ All tests use `Storage::fake('local')` 
- ✅ File existence verified with `Storage::disk('local')->assertExists()`
- ✅ File cleanup verified with `Storage::disk('local')->assertMissing()`
- ✅ No actual files created in development storage

---

## Postman Collection

**File:** `Noras-API-Phase4.postman_collection.json`

**Structure:**
- Phase 1: Health (1 request)
- Phase 2: Authentication (3 requests)
- Phase 3: Academic Catalog (4 requests)
- Phase 4 Admin: Material Management (13 requests)
- Phase 4 Student: Material Access (8 requests)

**Total Requests:** 29

**Variables:**
- `{{base_url}}` - http://localhost:8000
- `{{admin_token}}` - Auto-populated from admin login
- `{{student_token}}` - Auto-populated from student login

**Coverage:**
- ✅ Success scenarios (upload, list, publish, download)
- ✅ Validation errors (invalid file, invalid offering)
- ✅ Authorization failures (student upload, unauthenticated access)
- ✅ Business logic (publish/unpublish draft/published)
- ✅ Filtering and search
- ✅ Version replacement

**Postman Execution Status:** ❌ NOT EXECUTED

Collection created but not tested against live API. Requires:
1. Run `php artisan serve`
2. Seed database with admin user
3. Create test university/department/course/offering
4. Import collection to Postman
5. Execute requests in order
6. Verify responses match expected structure

---

## Security Limitations

### Implemented ✅
- Authentication required for all operations
- Role-based authorization (admin vs student)
- Account status enforcement (pending/suspended blocked)
- PDF MIME type validation
- File signature verification (magic bytes check)
- Private file storage (outside public web root)
- Authorized download endpoint (no direct file URLs)
- Path traversal prevention (UUID filenames)
- Upload size limit (50MB)
- Transaction safety (cleanup on failure)

### Not Implemented ❌
1. **Malware Scanning**
   - No virus/malware detection
   - Would require ClamAV integration or third-party service (e.g., VirusTotal API)
   - Files assumed safe based on format validation only

2. **File Encryption at Rest**
   - Files stored in plaintext on disk
   - Relies on filesystem permissions only
   - Could add encryption using Laravel's encryption features

3. **Rate Limiting**
   - No upload rate limiting implemented
   - Could be abused for storage exhaustion
   - Should add throttle middleware for production

4. **Content Moderation**
   - No review of PDF content for inappropriate material
   - Admin trusted to upload appropriate content
   - Could add manual review workflow

5. **CDN/Scalability**
   - Files served directly from application server
   - No CDN integration for high-traffic scenarios
   - Production deployment should consider S3 + CloudFront

6. **Audit Logging**
   - Upload/replace/delete not logged to separate audit table
   - Only `uploaded_by` field tracks uploader
   - Production should log all admin actions with timestamps

### Hosting Limitations
- **Development:** Files in `storage/app/private/` work as designed
- **Shared Hosting:** May not support private storage outside web root
- **Production:** Should use S3-compatible storage for reliability

---

## What Was NOT Implemented

**Scope Control (As Required):**
- ❌ AI integration (Gemini, Groq)
- ❌ AI-generated notes or summaries
- ❌ Quiz generation
- ❌ Study task recommendations
- ❌ Bookmarks or favorites
- ❌ Vue frontend modifications
- ❌ Student upload capabilities
- ❌ Material comments or ratings
- ❌ Material categories or tags
- ❌ Batch upload
- ❌ Material analytics or view tracking

---

## Implementation Decisions

### 1. Versioning Approach
**Decision:** New material record on replacement (not in-place update)

**Alternatives Considered:**
- In-place file replacement: ❌ Rejected (AI content would appear current)
- Separate versions table: ❌ Unnecessary complexity
- Soft delete old version: ❌ Harder to query version chain

**Rationale:** 
- Preserves complete audit trail
- Prevents AI content confusion
- Simple to implement and query
- Allows rollback if needed

### 2. Storage Location
**Decision:** `storage/app/private/` (Laravel's default private disk)

**Alternatives Considered:**
- `storage/app/public/`: ❌ Rejected (no authorization possible)
- Database BLOB: ❌ Rejected (bad performance, large database)
- S3: ✅ Recommended for production, not required for Phase 4

**Rationale:**
- Private storage enforces authorization
- Separates concerns (web server vs file server)
- Easy to migrate to S3 later (change config only)

### 3. Publication Workflow
**Decision:** Materials default to draft, admin explicitly publishes

**Alternatives Considered:**
- Auto-publish on upload: ❌ Rejected (no review step)
- Scheduled publishing: ❌ Out of scope

**Rationale:**
- Admin reviews material before student access
- Prevents accidental exposure of incomplete materials
- Consistent with typical CMS workflows

### 4. File Size Limit
**Decision:** 50MB maximum

**Rationale:**
- Typical academic PDF: 1-10MB
- Large scanned books: 20-40MB
- 50MB allows flexibility without server overload
- Configurable via constant if needs change

### 5. Download Method
**Decision:** Controller endpoint (not signed URLs)

**Alternatives Considered:**
- Signed temporary URLs: ✅ Good for S3, unnecessary for local storage
- Direct file serving: ❌ Rejected (bypasses authorization)

**Rationale:**
- Authorization checked on every download
- Account status enforced in real-time
- Simple implementation
- Can add signed URLs when migrating to S3

---

## Findings Summary

### ✅ Implemented and Tested

| Feature | Status | Tests |
|---------|--------|-------|
| Database schema with relationships | ✅ | Migration ran, relationships work |
| Admin PDF upload | ✅ | 14 tests passing |
| File signature validation | ✅ | Invalid PDFs rejected |
| Private file storage | ✅ | Storage::fake() tests |
| Version tracking | ✅ | Replacement creates new record |
| Publish/unpublish | ✅ | Status changes correctly |
| Metadata update | ✅ | Title/description editable |
| Material deletion | ✅ | File + record removed |
| Student read access | ✅ | 12 tests passing |
| Student download | ✅ | Published files downloadable |
| Draft protection | ✅ | Students cannot access drafts |
| Authorization | ✅ | Admin-only write, student read-only |
| Account status enforcement | ✅ | Token refresh test passes |
| Filtering/search | ✅ | Query params work |
| Phase 1-3 regression | ✅ | All 81 previous tests pass |

### 🔄 Not Tested (Manual Verification Required)

| Item | Reason |
|------|--------|
| Postman collection | Created but not executed against live API |
| Actual file uploads | Tests use fake storage |
| Large file uploads (near 50MB limit) | Tests use small files |
| Concurrent upload race conditions | Not stress-tested |
| S3 storage driver | Development uses local disk |
| File download resume | Not tested |

### ⚠️ Limitations

| Limitation | Impact | Recommendation |
|-----------|--------|----------------|
| No malware scanning | Admins trusted | Add ClamAV for production |
| No rate limiting | Potential abuse | Add throttle middleware |
| No CDN | Scalability concern | Use S3 + CloudFront for production |
| No audit logging | Limited traceability | Add separate audit_logs table |
| No encryption at rest | Privacy risk | Enable Laravel encryption if required |

---

## Unresolved Decisions

**None.** All Phase 4 requirements implemented as specified.

**Questions for Next Phase:**
1. AI provider preference (Gemini vs Groq)?
2. Generated content storage approach?
3. Regeneration policy when material replaced?
4. Student feedback on generated content?

---

## Ready for Review?

**Yes.** Phase 4 is complete and production-ready with documented limitations.

**Completed:**
- ✅ All requirements implemented
- ✅ 107 tests passing (100% pass rate)
- ✅ Security measures in place
- ✅ Phase 1-3 functionality preserved
- ✅ Version tracking prevents AI content confusion
- ✅ Documentation comprehensive

**Recommended Before Production:**
1. Manual Postman testing against live API
2. Review malware scanning requirements
3. Configure S3 storage if deploying to cloud
4. Add rate limiting middleware
5. Set up audit logging if compliance required

**Recommended Before Phase 5:**
1. Decide on AI provider (Gemini vs Groq)
2. Review generated content storage strategy
3. Define regeneration policy for replaced materials
4. Plan student interaction with AI features

---

## ISO/IEC 25010 Quality Characteristics

**Applied Principles:**
- **Functional Suitability:** Complete CRUD operations, proper versioning
- **Security:** Authentication, authorization, file validation, private storage
- **Reliability:** Transaction safety, error handling, cleanup on failure
- **Usability:** Consistent API responses, clear error messages
- **Maintainability:** Single responsibility, documented decisions, factory pattern
- **Portability:** Standard Laravel conventions, configurable storage

**Note:** Application follows secure coding practices. No claim of ISO/IEC 25010 certification.

---

## Conclusion

Phase 4 delivers production-ready PDF course material management. All automated tests pass. Security implementation is comprehensive with documented limitations. Version tracking design prevents future AI-generated content from appearing current after material replacement. Authorization correctly enforces account status checks from Phase 2.

**Status:** ✅ READY FOR PHASE 5 APPROVAL

**Test Results:** 107/107 PASSING (239 assertions, 11.47s)  
**Code Quality:** No warnings, follows Laravel conventions  
**Security:** Comprehensive validation and authorization  
**Documentation:** Complete with implementation decisions

---

**Report Generated:** October 10, 2026  
**Phase Status:** ✅ COMPLETE  
**Next Phase:** Awaiting approval for Phase 5 (AI Integration)
