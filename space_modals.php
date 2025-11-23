<!-- includes/space_modals.php - مودال‌های سیستم Spaces -->

<!-- Create Space Modal -->
<div class="modal fade modal-enhanced" id="createSpaceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ایجاد Space جدید</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="create_space.php" method="post">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="space_name" class="form-label">نام Space</label>
                                <input type="text" class="form-control" id="space_name" name="name" required 
                                       placeholder="مثلا: پروژه بازی جدید">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="space_color" class="form-label">رنگ Space</label>
                                <input type="color" class="form-control" id="space_color" name="color" value="#667eea">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="space_description" class="form-label">توضیحات</label>
                        <textarea class="form-control" id="space_description" name="description" rows="3"
                                  placeholder="توضیحات درباره Space جدید..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">دسترسی</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="access" id="access_private" value="private" checked>
                            <label class="form-check-label" for="access_private">
                                خصوصی - فقط اعضای دعوت شده
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="access" id="access_public" value="public">
                            <label class="form-check-label" for="access_public">
                                عمومی - همه می‌توانند ببینند
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-gradient">ایجاد Space</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Join Space Modal -->
<div class="modal fade modal-enhanced" id="joinSpaceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">پیوستن به Space</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="join_space.php" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="space_code" class="form-label">کد دعوت Space</label>
                        <input type="text" class="form-control" id="space_code" name="invite_code" required 
                               placeholder="کد دعوت را وارد کنید">
                        <div class="form-text">کد دعوت را از مدیر Space دریافت کنید</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-gradient">پیوستن</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Space Settings Modal -->
<div class="modal fade modal-enhanced" id="spaceSettingsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تنظیمات Space</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-light text-start" onclick="showSpaceMembers()">
                        <i class="fas fa-users me-2"></i>مدیریت اعضا
                    </button>
                    <button type="button" class="btn btn-outline-light text-start" onclick="generateInviteCode()">
                        <i class="fas fa-share-alt me-2"></i>ایجاد کد دعوت
                    </button>
                    <button type="button" class="btn btn-outline-light text-start" onclick="exportSpaceData()">
                        <i class="fas fa-download me-2"></i>خروجی داده‌ها
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>