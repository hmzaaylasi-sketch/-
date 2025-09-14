<!-- Main Sidebar -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- الشعار -->
    <a href="<?= bess_url('dashboard', 'url') ?>" class="brand-link text-center">
        <span class="brand-text font-weight-bold">🏇 لوحة التحكم</span>
    </a>

    <!-- القائمة -->
    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                <!-- لوحة التحكم -->
                <li class="nav-item">
                    <a href="<?= bess_url('dashboard', 'url') ?>" class="nav-link">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>📊 لوحة التحكم</p>
                    </a>
                </li>

                <!-- المستخدمين -->
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            👥 المستخدمين
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= bess_url('users/user_list', 'url') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>قائمة المستخدمين</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= bess_url('users/user_add', 'url') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>إضافة مستخدم</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- سباق الأحصنة -->
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-horse"></i>
                        <p>
                            🐴 الأحصنة
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= bess_url('horses/horse_list', 'url') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>قائمة الأحصنة</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= bess_url('horses/horse_add', 'url') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>إضافة حصان</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- السباقات -->
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-flag-checkered"></i>
                        <p>
                            🏇 السباقات
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= bess_url('races/race_list', 'url') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>قائمة السباقات</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= bess_url('races/race_add', 'url') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>إضافة سباق</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= bess_url('races/race_import', 'url') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>استيراد سباقات</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- الرهانات -->
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-coins"></i>
                        <p>
                            💰 الرهانات
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= bess_url('bets/bet_list', 'url') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>قائمة الرهانات</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= bess_url('bets/bet_view', 'url') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>عرض الرهانات</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- المعاملات -->
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-exchange-alt"></i>
                        <p>
                            💳 المعاملات
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= bess_url('transactions/transactions_list', 'url') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>قائمة المعاملات</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= bess_url('transactions/methods_list', 'url') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>طرق الدفع</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= bess_url('transactions/transactions_add', 'url') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>إضافة معاملة</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- التقارير -->
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-chart-bar"></i>
                        <p>
                            📈 التقارير
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= bess_url('reports/financial', 'url') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>التقارير المالية</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= bess_url('reports/betting', 'url') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>تقارير الرهانات</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- الإعدادات -->
                <li class="nav-item">
                    <a href="<?= bess_url('settings/general', 'url') ?>" class="nav-link">
                        <i class="nav-icon fas fa-cog"></i>
                        <p>⚙️ الإعدادات</p>
                    </a>
                </li>

                <!-- تسجيل الخروج -->
                <li class="nav-item">
                    <a href="<?= bess_url('logout', 'url') ?>" class="nav-link text-danger">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>🚪 تسجيل الخروج</p>
                    </a>
                </li>

            </ul>
        </nav>
    </div>
</aside>
<!-- /.sidebar -->

<script>
// تفعيل القائمة المنسدلة تلقائياً
document.addEventListener('DOMContentLoaded', function() {
    // إضافة active للقسم الحالي
    const currentPage = window.location.pathname.split('/').pop();
    document.querySelectorAll('.nav-link').forEach(link => {
        if (link.href.includes(currentPage)) {
            link.classList.add('active');
            // فتح القسم الرئيسي إذا كان في قائمة فرعية
            const parentMenu = link.closest('.nav-treeview');
            if (parentMenu) {
                parentMenu.style.display = 'block';
                parentMenu.previousElementSibling.classList.add('active');
            }
        }
    });
});
</script>