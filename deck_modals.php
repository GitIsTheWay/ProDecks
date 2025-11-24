<!-- مودال ایجاد Deck -->
<div class="modal fade modal-codecks" id="createDeckModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ایجاد Deck جدید</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createDeckForm">
                <div class="modal-body">
                    <div class="form-group-codecks">
                        <label class="form-label-codecks">نام Deck</label>
                        <input type="text" name="name" class="form-input-codecks" required 
                               placeholder="نام Deck را وارد کنید">
                    </div>
                    <div class="form-group-codecks">
                        <label class="form-label-codecks">توضیحات</label>
                        <textarea name="description" class="form-input-codecks" rows="3"
                                  placeholder="توضیحات اختیاری"></textarea>
                    </div>
                    <div class="form-group-codecks">
                        <label class="form-label-codecks">رنگ</label>
                        <input type="color" name="color" class="form-input-codecks" value="#4a5568">
                    </div>
                    <input type="hidden" name="space_id" id="create_deck_space_id">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary">ایجاد Deck</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- مودال ویرایش Deck -->
<div class="modal fade modal-codecks" id="editDeckModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ویرایش Deck</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editDeckForm">
                <div class="modal-body">
                    <div class="form-group-codecks">
                        <label class="form-label-codecks">نام Deck</label>
                        <input type="text" name="name" id="edit_deck_name" class="form-input-codecks" required>
                    </div>
                    <div class="form-group-codecks">
                        <label class="form-label-codecks">توضیحات</label>
                        <textarea name="description" id="edit_deck_description" class="form-input-codecks" rows="3"></textarea>
                    </div>
                    <div class="form-group-codecks">
                        <label class="form-label-codecks">رنگ</label>
                        <input type="color" name="color" id="edit_deck_color" class="form-input-codecks">
                    </div>
                    <input type="hidden" name="deck_id" id="edit_deck_id">
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

<!-- مودال افزودن کارت -->
<div class="modal fade modal-codecks" id="addCardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">افزودن کارت جدید</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addCardForm">
                <div class="modal-body">
                    <div class="form-group-codecks">
                        <label class="form-label-codecks">عنوان کارت</label>
                        <input type="text" name="title" id="card_title" class="form-input-codecks" required 
                               placeholder="عنوان کارت را وارد کنید">
                    </div>
                    <div class="form-group-codecks">
                        <label class="form-label-codecks">توضیحات</label>
                        <textarea name="description" class="form-input-codecks" rows="3"
                                  placeholder="توضیحات اختیاری"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-codecks">
                                <label class="form-label-codecks">اولویت</label>
                                <select name="priority" class="form-input-codecks">
                                    <option value="low">کم</option>
                                    <option value="medium" selected>متوسط</option>
                                    <option value="high">بالا</option>
                                    <option value="critical">بحرانی</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-codecks">
                                <label class="form-label-codecks">تاریخ سررسید</label>
                                <input type="date" name="due_date" class="form-input-codecks">
                            </div>
                        </div>
                    </div>
                    <div class="form-group-codecks">
                        <label class="form-label-codecks">زمان تخمینی (ساعت)</label>
                        <input type="number" name="time_estimate" class="form-input-codecks" step="0.5" min="0"
                               placeholder="0.5">
                    </div>
                    <input type="hidden" name="deck_id" id="card_deck_id">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary">ایجاد کارت</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- مودال ویرایش کارت -->
<div class="modal fade modal-codecks" id="editCardModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ویرایش کارت</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div id="editCardModalBody">
                <!-- محتوای دینامیک -->
            </div>
        </div>
    </div>
</div>

<!-- مودال افزودن ساب‌کارت -->
<div class="modal fade modal-codecks" id="addSubcardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">افزودن زیرکارت</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addSubcardForm">
                <div class="modal-body">
                    <div class="form-group-codecks">
                        <label class="form-label-codecks">عنوان زیرکارت</label>
                        <input type="text" name="title" id="subcard_title" class="form-input-codecks" required 
                               placeholder="عنوان زیرکارت را وارد کنید">
                    </div>
                    <div class="form-group-codecks">
                        <label class="form-label-codecks">توضیحات</label>
                        <textarea name="description" class="form-input-codecks" rows="2"
                                  placeholder="توضیحات اختیاری"></textarea>
                    </div>
                    <input type="hidden" name="parent_card_id" id="subcard_parent_id">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary">ایجاد زیرکارت</button>
                </div>
            </form>
        </div>
    </div>
</div>