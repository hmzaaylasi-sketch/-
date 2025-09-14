<!-- Footer -->
<footer class="main-footer text-center">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <strong>حقوق النشر &copy; <?= date("Y") ?> <a href="<?= bess_url('dashboard', 'url') ?>">نظام
                        المراهنات</a>.</strong>
                <span class="text-muted">جميع الحقوق محفوظة.</span>

                <!-- إضافة معلومات النظام -->
                <div class="mt-1">
                    <small class="text-muted">
                        الإصدار: 1.0.0 |
                        وقت التحميل: <?= round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 3) ?> ثانية
                    </small>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- JavaScript Libraries -->
<!-- jQuery MUST be first -->
<script src="<?= bess_url_v2('assets/plugins/jquery/jquery.min', 'js') ?>"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<!-- Bootstrap 4 Bundle (مع Popper) -->
<script src="<?= bess_url_v2('assets/plugins/bootstrap/js/bootstrap.bundle.min', 'js') ?>"></script>
<!-- AdminLTE -->
<script src="<?= bess_url_v2('assets/js/adminlte.min', 'js') ?>"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Custom Scripts -->
<script src="<?= bess_url_v2('assets/js/actions', 'js') ?>"></script>

<!-- 📌 مودال الأخطاء 
<div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel"
    aria-hidden="true">

</div>
-->
<!-- 📌 مودال التحميل 
<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-hidden="true">
     ... محتوى المودال ... 
</div>
-->
</body>

</html>

<style>
.main-footer {
    padding: 0.5rem 0;
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
}

#errorModal .modal-body pre {
    max-height: 200px;
    overflow-y: auto;
    background-color: #f8f9fa;
    padding: 10px;
    border-radius: 4px;
    border: 1px solid #e9ecef;
}

#loadingModal .modal-content {
    background: transparent;
    border: none;
}
</style>

<script>
// دالة نسخ الخطأ
document.getElementById('btnCopyError')?.addEventListener('click', function() {
    const errorContent = document.getElementById('errorTrace').textContent;
    navigator.clipboard.writeText(errorContent).then(() => {
        showToast('✅ تم نسخ التفاصيل', 'success');
    });
});

// دالة تنزيل الخطأ
document.getElementById('btnDownloadError')?.addEventListener('click', function() {
    const errorContent = document.getElementById('errorTrace').textContent;
    const blob = new Blob([errorContent], {
        type: 'text/plain'
    });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `error-log-${new Date().toISOString().slice(0, 10)}.txt`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
});

// دالة إرسال اللوج للسيرفر
document.getElementById('btnSendLog')?.addEventListener('click', function() {
    const errorContent = document.getElementById('errorTrace').textContent;

    fetch('<?= bess_url("api/logs/error_log", "url") ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                error: errorContent,
                url: window.location.href,
                timestamp: new Date().toISOString()
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showToast('✅ تم إرسال اللوج بنجاح', 'success');
            } else {
                showToast('❌ فشل في الإرسال', 'error');
            }
        })
        .catch(error => {
            showToast('❌ خطأ في الاتصال', 'error');
        });
});

// دالة عرض مودال التحميل
function showLoading() {
    $('#loadingModal').modal('show');
}

function hideLoading() {
    $('#loadingModal').modal('hide');
}

// إظهار وقت التحميل في console
console.log('⏰ وقت تحميل الصفحة:', <?= round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 3) ?> + 's');
</script>