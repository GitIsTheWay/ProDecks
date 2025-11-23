<!-- includes/space_deck_modals.php - مودال‌های سیستم Decks -->

<!-- Create Deck Modal -->
<div class="modal fade modal-enhanced" id="createDeckModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ایجاد Deck جدید</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="create_deck.php" method="post" id="createDeckForm">
                <input type="hidden" name="space_id" value="<?php echo $space_id; ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="deck_name" class="form-label">نام Deck</label>
                        <input type="text" class="form-control" id="deck_name" name="name" required 
                               placeholder="مثلا: Backlog، Development، Testing">
                    </div>
                    <div class="mb-3">
                        <label for="deck_description" class="form-label">توضیحات</label>
                        <textarea class="form-control" id="deck_description" name="description" rows="2"
                                  placeholder="توضیحات اختیاری..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="deck_color" class="form-label">رنگ Deck</label>
                        <input type="color" class="form-control" id="deck_color" name="color" value="#4a5568">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-gradient">ایجاد Deck</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Deck Modal -->
<div class="modal fade modal-enhanced" id="editDeckModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ویرایش Deck</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="edit_deck.php" method="post" id="editDeckForm">
                <input type="hidden" name="space_id" value="<?php echo $space_id; ?>">
                <input type="hidden" id="edit_deck_id" name="deck_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_deck_name" class="form-label">نام Deck</label>
                        <input type="text" class="form-control" id="edit_deck_name" name="name" required 
                               placeholder="نام Deck">
                    </div>
                    <div class="mb-3">
                        <label for="edit_deck_description" class="form-label">توضیحات</label>
                        <textarea class="form-control" id="edit_deck_description" name="description" rows="2"
                                  placeholder="توضیحات اختیاری..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_deck_color" class="form-label">رنگ Deck</label>
                        <input type="color" class="form-control" id="edit_deck_color" name="color" value="#4a5568">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-gradient">ذخیره تغییرات</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Card Modal -->
<div class="modal fade modal-enhanced" id="addCardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">افزودن Card جدید</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="add_card.php" method="post" id="addCardForm">
                <input type="hidden" name="space_id" value="<?php echo $space_id; ?>">
                <input type="hidden" id="card_deck_id" name="deck_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="card_title" class="form-label">عنوان Card</label>
                        <input type="text" class="form-control" id="card_title" name="title" required 
                               placeholder="عنوان کارت...">
                    </div>
                    <div class="mb-3">
                        <label for="card_description" class="form-label">توضیحات</label>
                        <textarea class="form-control" id="card_description" name="description" rows="3"
                                  placeholder="توضیحات اختیاری..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="card_assignee" class="form-label">واگذار شده به</label>
                                <select class="form-select" id="card_assignee" name="assignee_id">
                                    <option value="">انتخاب کاربر</option>
                                    <?php
                                    $members = getSpaceMembers($space_id, $pdo);
                                    foreach ($members as $member): ?>
                                        <option value="<?php echo $member['id']; ?>">
                                            <?php echo htmlspecialchars($member['full_name'] ?: $member['username']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="card_priority" class="form-label">اولویت</label>
                                <select class="form-select" id="card_priority" name="priority">
                                    <option value="low">کم</option>
                                    <option value="medium" selected>متوسط</option>
                                    <option value="high">زیاد</option>
                                    <option value="critical">بحرانی</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="card_due_date" class="form-label">تاریخ سررسید</label>
                        <input type="date" class="form-control" id="card_due_date" name="due_date">
                    </div>
                    <div class="mb-3">
                        <label for="card_estimate" class="form-label">تخمین زمان (ساعت)</label>
                        <input type="number" class="form-control" id="card_estimate" name="time_estimate" min="0" step="0.5" placeholder="0.5">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-gradient">ایجاد Card</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Subcard Modal -->
<div class="modal fade modal-enhanced" id="addSubcardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">افزودن Subcard جدید</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="add_subcard.php" method="post" id="addSubcardForm">
                <input type="hidden" name="space_id" value="<?php echo $space_id; ?>">
                <input type="hidden" id="subcard_parent_id" name="parent_card_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="subcard_title" class="form-label">عنوان Subcard</label>
                        <input type="text" class="form-control" id="subcard_title" name="title" required 
                               placeholder="عنوان زیرکارت...">
                    </div>
                    <div class="mb-3">
                        <label for="subcard_description" class="form-label">توضیحات</label>
                        <textarea class="form-control" id="subcard_description" name="description" rows="2"
                                  placeholder="توضیحات اختیاری..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="subcard_assignee" class="form-label">واگذار شده به</label>
                                <select class="form-select" id="subcard_assignee" name="assignee_id">
                                    <option value="">انتخاب کاربر</option>
                                    <?php
                                    $members = getSpaceMembers($space_id, $pdo);
                                    foreach ($members as $member): ?>
                                        <option value="<?php echo $member['id']; ?>">
                                            <?php echo htmlspecialchars($member['full_name'] ?: $member['username']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="subcard_status" class="form-label">وضعیت</label>
                                <select class="form-select" id="subcard_status" name="status">
                                    <option value="todo">انجام نشده</option>
                                    <option value="in_progress">در حال انجام</option>
                                    <option value="done">انجام شده</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-gradient">ایجاد Subcard</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Card Modal -->
<div class="modal fade modal-enhanced" id="editCardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ویرایش Card</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="edit_card.php" method="post" id="editCardForm">
                <input type="hidden" name="space_id" value="<?php echo $space_id; ?>">
                <input type="hidden" id="edit_card_id" name="card_id">
                <div class="modal-body" id="editCardModalBody">
                    <!-- Content will be loaded via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-gradient">ذخیره تغییرات</button>
                </div>
            </form>
        </div>
    </div>
</div>