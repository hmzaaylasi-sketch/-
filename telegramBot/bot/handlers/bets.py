# handlers/bets.py
from telegram import Update
from telegram.ext import ContextTypes, ConversationHandler
from db import get_db
from handlers.start import main_menu

SAVE_BET = 1

# ✅ عند اختيار حصان
async def horse_selected(update: Update, context: ContextTypes.DEFAULT_TYPE):
    query = update.callback_query
    await query.answer()

    _, race_id, race_horse_id = query.data.split("_")
    context.user_data["race_id"] = race_id
    context.user_data["race_horse_id"] = race_horse_id

    await query.edit_message_text("💰 أدخل مبلغ الرهان (بالدرهم):")
    return SAVE_BET

# ✅ حفظ الرهان
async def save_bet(update: Update, context: ContextTypes.DEFAULT_TYPE):
    user_id = update.message.from_user.id
    amount_text = update.message.text

    try:
        amount = float(amount_text)
    except ValueError:
        await update.message.reply_text("⚠️ أدخل مبلغ صالح (رقم فقط).")
        return SAVE_BET

    race_id = context.user_data.get("race_id")
    race_horse_id = context.user_data.get("race_horse_id")

    db = get_db()
    with db.cursor() as cursor:
        cursor.execute("INSERT INTO bets (user_id, race_horse_id, amount, odds, status, created_at) VALUES (%s,%s,%s,1.5,'pending',NOW())",
                       (user_id, race_horse_id, amount))
        db.commit()
    db.close()

    await update.message.reply_text(f"✅ تم تسجيل رهانك بمبلغ {amount} درهم.", reply_markup=main_menu())
    return ConversationHandler.END

# ❌ إلغاء
async def cancel(update: Update, context: ContextTypes.DEFAULT_TYPE):
    await update.message.reply_text("❌ تم إلغاء الرهان.", reply_markup=main_menu())
    return ConversationHandler.END
