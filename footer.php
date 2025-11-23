</main>

<footer class="footer-enhanced">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="d-flex align-items-center">
                    <i class="fas fa-cards fa-2x text-primary me-3"></i>
                    <div>
                        <h5 class="mb-1">ProDecks</h5>
                        <p class="mb-0 text-light opacity-75">مدیریت پروژه به سبک بازی‌وار شده</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="social-links">
                    <a href="#" class="text-light me-3">
                        <i class="fab fa-telegram"></i>
                    </a>
                    <a href="#" class="text-light me-3">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="text-light">
                        <i class="fab fa-github"></i>
                    </a>
                </div>
            </div>
        </div>
        <hr class="my-4 border-light opacity-25">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-0 text-light opacity-75">
                    &copy; 2024 ProDecks. تمام حقوق محفوظ است.
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="tutorial.php" class="text-light opacity-75 text-decoration-none me-3">
                    <i class="fas fa-graduation-cap me-1"></i>راهنما
                </a>
                <a href="#" class="text-light opacity-75 text-decoration-none">
                    <i class="fas fa-shield-alt me-1"></i>حریم خصوصی
                </a>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
<script src="js/script.js"></script>

<?php
// بارگذاری اسکریپت‌های خاص هر صفحه
$current_page = basename($_SERVER['PHP_SELF']);
if ($current_page == 'spaces_manager.php') {
    echo '<script src="js/spaces_manager.js"></script>';
} elseif ($current_page == 'space_decks.php') {
    echo '<script src="js/space_decks.js"></script>';
} elseif ($current_page == 'project_decks.php') {
    echo '<script src="js/project_decks.js"></script>';
}
?>

</body>
</html>