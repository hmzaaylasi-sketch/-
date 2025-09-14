# handlers/start.py
from telegram import Update, ReplyKeyboardMarkup, KeyboardButton
from telegram.ext import ContextTypes
from db import get_db

# ======================
# 🎛️ القوائم (Keyboards)
# ======================

def main_menu():
    return ReplyKeyboardMarkup([
        ["📋 حسابي", "⚙️ الإعدادات"],
        ["🏇 السباقات", "🎲 رهاناتي"]
    ], resize_keyboard=True)

def account_menu():
    return ReplyKeyboardMarkup([
        ["📥 إيداع", "💸 سحب", "💳 رصيدي"],
        ["📜 سجل العمليات", "معلومات حسابي"],
        ["⬅️ رجوع"]
    ], resize_keyboard=True)

def races_menu():
    return ReplyKeyboardMarkup([
        ["📅 السباقات القادمة", "🏆 النتائج"],
        ["⬅️ رجوع"]
    ], resize_keyboard=True)

def bets_menu():
    return ReplyKeyboardMarkup([
        ["🎲 رهاني الحالي", "📊 رهاناتي السابقة"],
        ["⬅️ رجوع"]
    ], resize_keyboard=True)

def settings_menu():
    return ReplyKeyboardMarkup([
        ["🔑 تغيير كلمة المرور", "📞 تغيير الهاتف"],
        ["⬅️ رجوع"]
    ], resize_keyboard=True)


# ======================
# 🚀 أوامر أساسية
# ======================

async def start(update: Update, context: ContextTypes.DEFAULT_TYPE):
    user_id = update.message.from_user.id
    username = update.message.from_user.username or "بدون_اسم"
    first_name = update.message.from_user.first_name or ""
    last_name = update.message.from_user.last_name or ""

    db = get_db()
    with db.cursor() as cursor:
        cursor.execute("SELECT * FROM users WHERE user_id=%s", (user_id,))
        user = cursor.fetchone()

        if not user:
            cursor.execute("""
                INSERT INTO users (user_id, username, first_name, last_name, phone, currency, registration_date, status) 
                VALUES (%s,%s,%s,%s,NULL,100,NOW(),'active')
            """, (user_id, username, first_name, last_name))
            db.commit()
            db.close()

            phone_button = ReplyKeyboardMarkup([
                [KeyboardButton("📞 مشاركة رقم الهاتف", request_contact=True)]
            ], resize_keyboard=True, one_time_keyboard=True)

            await update.message.reply_text(
                "👋 أهلاً بك في منصة الرهانات!\n"
                "✅ تم تسجيلك بنجاح وحصلت على 100 درهم كبونص 🎁\n"
                "📞 من فضلك شارك رقم هاتفك لتفعيل حسابك:",
                reply_markup=phone_button
            )
        else:
            db.close()
            await update.message.reply_text(
                f"مرحباً {username} 👋\nاختر من القائمة:",
                reply_markup=main_menu()
            )

# ✅ حفظ رقم الهاتف
async def save_phone(update: Update, context: ContextTypes.DEFAULT_TYPE):
    user_id = update.message.from_user.id
    phone = update.message.contact.phone_number

    db = get_db()
    with db.cursor() as cursor:
        cursor.execute("UPDATE users SET phone=%s WHERE user_id=%s", (phone, user_id))
        db.commit()
    db.close()

    await update.message.reply_text(
        "✅ تم حفظ رقم الهاتف وتفعيل حسابك.\nاختر من القائمة:",
        reply_markup=main_menu()
    )
