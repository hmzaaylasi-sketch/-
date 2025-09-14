<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
  <!-- Left navbar controls -->
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <a href="dashboard.php" class="nav-link">الرئيسية</a>
    </li>
  </ul>

  <!-- Search Form -->
  <div class="navbar-search ml-auto mr-3" style="width: 300px;">
    <div class="input-group input-group-sm">
      <input class="form-control form-control-navbar" type="search" placeholder="🔍 ابحث عن مستخدم، حصان، سباق..." aria-label="Search" id="globalSearch">
      <div class="input-group-append">
        <button class="btn btn-navbar" type="button" id="searchBtn">
          <i class="fas fa-search"></i>
        </button>
      </div>
    </div>
    <!-- Search Results Dropdown -->
    <div class="dropdown-menu w-100" id="searchResults" style="display: none;">
      <div class="search-loading text-center p-2">
        <div class="spinner-border spinner-border-sm" role="status">
          <span class="sr-only">جاري البحث...</span>
        </div>
      </div>
      <div class="search-content"></div>
    </div>
  </div>

  <!-- Right navbar links -->
  <ul class="navbar-nav ml-auto">
    <!-- Messages Dropdown Menu -->
    <li class="nav-item dropdown">
      <a class="nav-link" data-toggle="dropdown" href="#">
        <i class="far fa-comments"></i>
        <span class="badge badge-danger navbar-badge">3</span>
      </a>
      <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        <span class="dropdown-item dropdown-header">📬 3 رسائل جديدة</span>
        <div class="dropdown-divider"></div>
        <a href="messages.php" class="dropdown-item">
          <div class="media">
            <img src="<?= bess_url_v2('assets/img/user1-128x128','jpg'); ?>" alt="User Avatar" class="img-size-50 mr-3 img-circle">
            <div class="media-body">
              <h3 class="dropdown-item-title">
                محمد أحمد
                <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
              </h3>
              <p class="text-sm">هل يمكنني سحب رصيدي؟</p>
              <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> منذ 4 دقائق</p>
            </div>
          </div>
        </a>
        <div class="dropdown-divider"></div>
        <a href="messages.php" class="dropdown-item dropdown-footer">📋 مشاهدة جميع الرسائل</a>
      </div>
    </li>

    <!-- Notifications Dropdown Menu -->
    <li class="nav-item dropdown">
      <a class="nav-link" data-toggle="dropdown" href="#">
        <i class="far fa-bell"></i>
        <span class="badge badge-warning navbar-badge">8</span>
      </a>
      <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        <span class="dropdown-item dropdown-header">🔔 8 إشعارات</span>
        <div class="dropdown-divider"></div>
        <a href="#" class="dropdown-item">
          <i class="fas fa-user-plus mr-2"></i> 3 مستخدمين جدد
          <span class="float-right text-muted text-sm">3 دقائق</span>
        </a>
        <div class="dropdown-divider"></div>
        <a href="#" class="dropdown-item">
          <i class="fas fa-coins mr-2"></i> إيداع جديد 500 درهم
          <span class="float-right text-muted text-sm">12 دقيقة</span>
        </a>
        <div class="dropdown-divider"></div>
        <a href="notifications.php" class="dropdown-item dropdown-footer">📋 مشاهدة جميع الإشعارات</a>
      </div>
    </li>

    <!-- Messages Send Dropdown Menu -->
    <li class="nav-item dropdown">
      <a class="nav-link" data-toggle="dropdown" href="#">
        <i class="far fa-paper-plane"></i>
      </a>
      <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        <span class="dropdown-item dropdown-header">📤 إرسال</span>
        <div class="dropdown-divider"></div>
        <a href="broadcast.php" class="dropdown-item">
          <i class="fas fa-bullhorn mr-2"></i> إعلان عام
        </a>
        <a href="messages_send.php" class="dropdown-item">
          <i class="fas fa-envelope mr-2"></i> رسالة خاصة
        </a>
        <a href="notifications_send.php" class="dropdown-item">
          <i class="fas fa-bell mr-2"></i> إشعار للمستخدمين
        </a>
      </div>
    </li>

    <!-- User Account Dropdown Menu -->
    <li class="nav-item dropdown">
      <a class="nav-link" data-toggle="dropdown" href="#">
        <img src="<?= bess_url_v2('assets/img/user2-160x160','jpg'); ?>" class="img-circle elevation-2" alt="User Image" style="width: 32px; height: 32px;">
        <span class="d-none d-md-inline"><?= $_SESSION['admin_username'] ?? 'المدير' ?></span>
      </a>
      <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        <span class="dropdown-item dropdown-header">
          <div class="text-center">
            <img src="<?= bess_url_v2('assets/img/user2-160x160','jpg'); ?>" class="img-circle elevation-2" alt="User Image" style="width: 80px; height: 80px;">
            <p class="mt-2 mb-0"><?= $_SESSION['admin_username'] ?? 'المدير' ?></p>
            <small class="text-muted">مدير النظام</small>
          </div>
        </span>
        <div class="dropdown-divider"></div>
        <a href="profile.php" class="dropdown-item">
          <i class="fas fa-user mr-2"></i> الملف الشخصي
        </a>
        <a href="settings.php" class="dropdown-item">
          <i class="fas fa-cog mr-2"></i> الإعدادات
        </a>
        <a href="activity_log.php" class="dropdown-item">
          <i class="fas fa-history mr-2"></i> سجل النشاط
        </a>
        <div class="dropdown-divider"></div>
        <a href="logout.php" class="dropdown-item dropdown-footer text-danger">
          <i class="fas fa-sign-out-alt mr-2"></i> تسجيل الخروج
        </a>
      </div>
    </li>


  </ul>
</nav>
<!-- /.navbar -->

<style>
.navbar-search {
  position: relative;
}

.navbar-search .dropdown-menu {
  width: 100%;
  max-height: 300px;
  overflow-y: auto;
}

.img-circle {
  border-radius: 50%;
}

.dropdown-menu-lg {
  min-width: 280px;
}

.search-loading {
  display: none;
}

.search-content .search-item {
  padding: 0.5rem 1rem;
  border-bottom: 1px solid #f8f9fa;
  cursor: pointer;
}

.search-content .search-item:hover {
  background-color: #f8f9fa;
}

.search-content .search-type {
  font-size: 0.8em;
  color: #6c757d;
  background: #f8f9fa;
  padding: 0.1rem 0.4rem;
  border-radius: 0.25rem;
}

.navbar-nav .nav-link > .img-circle {
  margin-right: 3px;
}

@media (max-width: 768px) {
  .navbar-search {
    width: 200px;
  }
  
  .navbar-nav .nav-link span.d-none {
    display: none !important;
  }
}
</style>

<script>
// محرك البحث المتقدم (تجريبي)
document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('globalSearch');
  const searchResults = document.getElementById('searchResults');
  const searchContent = searchResults.querySelector('.search-content');
  const searchLoading = searchResults.querySelector('.search-loading');
  const searchBtn = document.getElementById('searchBtn');

  searchInput.addEventListener('focus', function() {
    searchResults.style.display = 'block';
  });

  searchInput.addEventListener('blur', function() {
    setTimeout(() => {
      searchResults.style.display = 'none';
    }, 200);
  });

  searchInput.addEventListener('input', function(e) {
    const query = e.target.value.trim();
    
    if (query.length < 2) {
      searchContent.innerHTML = '<div class="p-2 text-center text-muted">اكتب至少 2 حروف للبحث</div>';
      return;
    }

    // محاكاة البحث (تجريبي)
    searchLoading.style.display = 'block';
    searchContent.style.display = 'none';

    setTimeout(() => {
      searchLoading.style.display = 'none';
      searchContent.style.display = 'block';
      
      // نتائج بحث تجريبية
      searchContent.innerHTML = `
        <div class="search-item">
          <div class="d-flex justify-content-between align-items-center">
            <strong>محمد أحمد</strong>
            <span class="search-type">👤 مستخدم</span>
          </div>
          <small class="text-muted">ID: 12345 - الرصيد: 500 درهم</small>
        </div>
        <div class="search-item">
          <div class="d-flex justify-content-between align-items-center">
            <strong>نايدين</strong>
            <span class="search-type">🐴 حصان</span>
          </div>
          <small class="text-muted">السباق: R1C6 - المدرب: ADERCHI</small>
        </div>
        <div class="search-item">
          <div class="d-flex justify-content-between align-items-center">
            <strong>سباق الرباط</strong>
            <span class="search-type">🏇 سباق</span>
          </div>
          <small class="text-muted">13/09/2025 - الجائزة: 12,000 درهم</small>
        </div>
        <div class="search-item">
          <div class="d-flex justify-content-between align-items-center">
            <strong>رهان #456</strong>
            <span class="search-type">🎲 رهان</span>
          </div>
          <small class="text-muted">المبلغ: 200 درهم - الحالة: انتظار</small>
        </div>
      `;
    }, 500);
  });

  searchBtn.addEventListener('click', function() {
    const query = searchInput.value.trim();
    if (query.length > 1) {
      // سيتم تطوير وظيفة البحث الكاملة لاحقاً
      showToast('🔍 سيتم تطوير البحث المتقدم لاحقاً: ' + query, 'info');
    }
  });

  searchInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
      const query = searchInput.value.trim();
      if (query.length > 1) {
        showToast('🔍 سيتم تطوير البحث المتقدم لاحقاً: ' + query, 'info');
      }
    }
  });

  // إغلاق نتائج البحث عند النقر خارجها
  document.addEventListener('click', function(e) {
    if (!searchResults.contains(e.target) && e.target !== searchInput) {
      searchResults.style.display = 'none';
    }
  });
});
</script>