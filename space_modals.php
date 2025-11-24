<!-- مودال ایجاد Space -->
<div class="modal fade modal-codecks" id="createSpaceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ایجاد Space جدید</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createSpaceForm">
                <div class="modal-body">
                    <div class="form-group-codecks">
                        <label class="form-label-codecks">نام Space</label>
                        <input type="text" name="name" class="form-input-codecks" required 
                               placeholder="نام Space را وارد کنید">
                    </div>
                    <div class="form-group-codecks">
                        <label class="form-label-codecks">توضیحات</label>
                        <textarea name="description" class="form-input-codecks" rows="3"
                                  placeholder="توضیحات اختیاری"></textarea>
                    </div>
                    <div class="form-group-codecks">
                        <label class="form-label-codecks">رنگ</label>
                        <input type="color" name="color" class="form-input-codecks" value="#667eea">
                    </div>
                    <div class="form-group-codecks">
                        <label class="form-label-codecks">نوع دسترسی</label>
                        <select name="access_type" class="form-input-codecks">
                            <option value="private">خصوصی</option>
                            <option value="public">عمومی</option>
                        </select>
                    </div>
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary">ایجاد Space</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- مودال ویرایش Space -->
<div class="modal fade modal-codecks" id="editSpaceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ویرایش Space</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editSpaceForm">
                <div class="modal-body">
                    <div class="form-group-codecks">
                        <label class="form-label-codecks">نام Space</label>
                        <input type="text" name="name" id="edit_space_name" class="form-input-codecks" required>
                    </div>
                    <div class="form-group-codecks">
                        <label class="form-label-codecks">توضیحات</label>
                        <textarea name="description" id="edit_space_description" class="form-input-codecks" rows="3"></textarea>
                    </div>
                    <div class="form-group-codecks">
                        <label class="form-label-codecks">رنگ</label>
                        <input type="color" name="color" id="edit_space_color" class="form-input-codecks">
                    </div>
                    <input type="hidden" name="space_id" id="edit_space_id">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- مودال پیوستن به Space -->
<div class="modal fade modal-codecks" id="joinSpaceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">پیوستن به Space</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="form-group-codecks">
                    <label class="form-label-codecks">کد دعوت Space</label>
                    <input type="text" id="space_code" class="form-input-codecks" 
                           placeholder="کد دعوت را وارد کنید" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                <button type="button" class="btn btn-primary" onclick="window.spacesManager.joinSpace()">
                    پیوستن
                </button>
            </div>
        </div>
    </div>
</div>