<!-- includes/deck_modals.php - نسخه نهایی -->

<!-- Add Deck Modal -->
<div class="modal fade" id="addDeckModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">افزودن دک جدید</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="add_deck.php" method="post">
                <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="deck_name" class="form-label">نام دک</label>
                        <input type="text" class="form-control" id="deck_name" name="name" required 
                               placeholder="مثلا: ایده‌ها، توسعه، تست...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary">ایجاد دک</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Deck Modal -->
<div class="modal fade" id="editDeckModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ویرایش دک</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="edit_deck.php" method="post">
                <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                <input type="hidden" id="edit_deck_id" name="deck_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_deck_name" class="form-label">نام دک</label>
                        <input type="text" class="form-control" id="edit_deck_name" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Project Settings Modal -->
<div class="modal fade" id="projectSettingsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تنظیمات پروژه</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-grid gap-2">
                    <a href="project_members.php?id=<?php echo $project_id; ?>" class="btn btn-outline-primary text-start">
                        <i class="fas fa-users me-2"></i>مدیریت اعضا
                    </a>
                    <a href="project_settings.php?id=<?php echo $project_id; ?>" class="btn btn-outline-secondary text-start">
                        <i class="fas fa-cog me-2"></i>تنظیمات پیشرفته
                    </a>
                    <a href="dashboard.php" class="btn btn-outline-info text-start">
                        <i class="fas fa-tachometer-alt me-2"></i>بازگشت به داشبورد
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>