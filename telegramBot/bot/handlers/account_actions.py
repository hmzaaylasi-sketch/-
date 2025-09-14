# handlers/account_actions.py
from telegram import Update
from telegram.ext import ContextTypes
from db import get_db
from handlers.start import main_menu
from datetime import datetime

# 💳 عرض الرصيد
async def my_balance(update: Update, context: ContextTypes.DEFAULT_TYPE):
    user_id = update.message.from_user.id
    db = get_db()
    try:
        with db.cursor() as cursor:
            cursor.execute("SELECT currency FROM users WHERE user_id=%s", (user_id,))
            user = cursor.fetchone()
    finally:
        db.close()

    if not user:
        await update.message.reply_text("⚠️ لم يتم العثور على حسابك.")
        return

    balance = user.get("currency", 0.00)
    await update.message.reply_text(
        f"💳 <b>رصيدك الحالي:</b> {balance:.2f} درهم",
        parse_mode="HTML",
        reply_markup=main_menu()
    )

# 📜 عرض سجل العمليات
async def my_transactions(update: Update, context: ContextTypes.DEFAULT_TYPE):
    user_id = update.message.from_user.id
    db = get_db()
    try:
        with db.cursor() as cursor:
            cursor.execute("""
                SELECT type, amount, currency, status, created_at
                FROM transactions
                WHERE user_id=%s
                ORDER BY created_at DESC
                LIMIT 5
            """, (user_id,))
            txs = cursor.fetchall()
    finally:
        db.close()

    if not txs:
        await update.message.reply_text("📜 لا توجد عمليات بعد.")
        return

    text = "📜 <b>آخر 5 عمليات:</b>\n\n"
    for tx in txs:
        icon = "📥" if tx["type"] == "deposit" else "💸"
        status = "⏳ قيد المعالجة" if tx["status"] == "pending" else "✅ تمت" if tx["status"] == "approved" else "❌ مرفوضة"
        created = tx["created_at"]
        if isinstance(created, datetime):
            created = created.strftime("%Y-%m-%d %H:%M")
        text += (
            f"{icon} {tx['type']} - {tx['amount']} {tx['currency']}\n"
            f"⚡ {status}\n"
            f"📅 {created}\n\n"
        )
    await update.message.reply_text(text, parse_mode="HTML", reply_markup=main_menu())

# 📥 إيداع
async def deposit(update: Update, context: ContextTypes.DEFAULT_TYPE):
    await update.message.reply_text("📥 أرسل مبلغ الإيداع (رقماً فقط):")
    # 👉 هنا ممكن نستخدم ConversationHandler لاحقاً لحفظ المبلغ

# 💸 سحب
async def withdraw(update: Update, context: ContextTypes.DEFAULT_TYPE):
    await update.message.reply_text("💸 أرسل مبلغ السحب (رقماً فقط):")

# 👤 معلومات الحساب
async def account_info(update: Update, context: ContextTypes.DEFAULT_TYPE):
    user_id = update.message.from_user.id
    db = get_db()
    with db.cursor() as cursor:
        cursor.execute("SELECT * FROM users WHERE user_id=%s", (user_id,))
        user = cursor.fetchone()
    db.close()

    if not user:
        await update.message.reply_text("⚠️ حسابك غير موجود.")
        return

    msg = (
        f"👤 <b>معلومات الحساب</b>\n\n"
        f"🆔 ID: {user['user_id']}\n"
        f"👥 الاسم: {user['first_name']} {user['last_name']}\n"
        f"🔗 @{user['username'] if user['username'] else 'بدون'}\n"
        f"📞 الهاتف: {user['phone'] or '❌ غير مسجل'}\n"
        f"💰 الرصيد: {user['currency']} درهم\n"
        f"📅 التسجيل: {user['registration_date']}\n"
        f"⚡ الحالة: {'✅ نشط' if user['status']=='active' else '❌ غير نشط'}"
    )
    await update.message.reply_text(msg, parse_mode="HTML", reply_markup=main_menu())
