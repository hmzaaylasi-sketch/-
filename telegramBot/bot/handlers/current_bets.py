# handlers/current_bets.py
from telegram import Update
from telegram.ext import ContextTypes
from db import get_db
from handlers.start import main_menu

async def current_bets(update: Update, context: ContextTypes.DEFAULT_TYPE):
    user_id = update.message.from_user.id

    db = get_db()
    try:
        with db.cursor() as cursor:
            # نجلب جميع رهانات المستخدم التي مازالت مفتوحة
            cursor.execute("""
                SELECT b.bet_id, r.meeting_code, r.race_number,
                       b.bet_numbers, b.stake, b.potential_payout, b.status
                FROM horse_bets b
                JOIN races r ON b.race_id = r.race_id
                WHERE b.user_id = %s AND b.status = 'pending'
                ORDER BY b.bet_id DESC
            """, (user_id,))
            bets = cursor.fetchall()
    finally:
        db.close()

    if not bets:
        await update.message.reply_text("🎲 ليس لديك رهانات حالية.", reply_markup=main_menu())
        return

    total_stake = 0
    total_payout = 0
    text = "🎲 رهاناتك الحالية:\n\n"

    for bet in bets:
        total_stake += float(bet['stake'])
        total_payout += float(bet['potential_payout'])
        text += (
            f"🆔 رهان: {bet['bet_id']}\n"
            f"🏁 سباق: {bet['meeting_code']}{bet['race_number']}\n"
            f"🐎 خيول: {bet['bet_numbers']}\n"
            f"💰 المبلغ: {float(bet['stake']):.2f}\n"
            f"📊 الحالة: ⏳ قيد الانتظار\n\n"
        )

    text += "---------------------------\n"
    text += f"📉 مجموع الرهانات: {total_stake:.2f} درهم\n"
    text += f"💵 مجموع الأرباح المحتملة: {total_payout:.2f} درهم"

    await update.message.reply_text(text, reply_markup=main_menu())
