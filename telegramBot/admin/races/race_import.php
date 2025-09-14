<?php
session_start();
include '../init/ini.php';

// تحقق من الأدمن
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

include bess_url('header','file');
include bess_url('navbar','file');
include bess_url('sidebar','file');
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h2>📂 استيراد سباقات من PDF</h2>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card shadow">
                <div class="card-body">
                    <form id="uploadForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label>📄 ملف PDF</label>
                            <input type="file" name="race_pdf" class="form-control" accept="application/pdf" required>
                        </div>
                        <button type="submit" class="btn btn-primary">📥 رفع و تحليل</button>
                    </form>

                    <hr>

                    <div id="previewArea" style="display:none;">
                        <h4>👀 معاينة السباقات المستخرجة</h4>
                        <div id="racesPreview"></div>
                        <button id="confirmImport" class="btn btn-success mt-3">✅ تأكيد الاستيراد</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include bess_url('footer','file'); ?>

<script>
document.getElementById("uploadForm").addEventListener("submit", function(e) {
    e.preventDefault();
    let formData = new FormData(this);

    fetch("<?= bess_url_v2('api/races/race_import_preview','php') ?>", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            let html = `<p><b>اجتماع:</b> ${data.meeting} - ${data.date} - ${data.location}</p>`;
            html += `<table class="table table-bordered"><thead><tr>
                <th>رقم السباق</th><th>الوقت</th><th>المسافة</th><th>الجائزة</th><th>الأحصنة</th>
            </tr></thead><tbody>`;
            data.races.forEach(r => {
                html += `<tr>
                    <td>${r.race_number}</td>
                    <td>${r.start_time}</td>
                    <td>${r.distance}m</td>
                    <td>${r.prize} DH</td>
                    <td>${r.horses.join(", ")}</td>
                </tr>`;
            });
            html += `</tbody></table>`;
            document.getElementById("racesPreview").innerHTML = html;
            document.getElementById("previewArea").style.display = "block";

            // زر تأكيد
            document.getElementById("confirmImport").onclick = function() {
                fetch("<?= bess_url_v2('api/races/race_import_save','php') ?>", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(data)
                })
                .then(res => res.json())
                .then(saveRes => {
                    if (saveRes.status === "success") {
                        showToast("✅ تم حفظ السباقات بنجاح");
                    } else {
                        showErrorModal("❌ فشل", saveRes);
                    }
                })
                .catch(err => showErrorModal("⚠️ خطأ", {message: err}));
            };
        } else {
            showErrorModal("❌ فشل التحليل", data);
        }
    })
    .catch(err => showErrorModal("⚠️ خطأ", {message: err}));
});
</script>
