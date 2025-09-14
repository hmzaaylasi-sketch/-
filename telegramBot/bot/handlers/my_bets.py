from telegram import Update
from telegram.ext import ContextTypes
from db import get_db

# 🎲 رهاناتي الحالية
async def current_bets(update: Update, context: ContextTypes.DEFAULT_TYPE):
    user_id = update.message.from_user.id
    db = get_db()
    with db.cursor() as cursor:
        cursor.execute("""
            SELECT hb.bet_id, hb.bet_numbers, hb.stake, hb.multiplier, hb.total_stake, hb.potential_payout, hb.status,
                   r.meeting_code, r.race_number, r.status AS race_status
            FROM horse_bets hb
            JOIN races r ON hb.race_id = r.race_id
            WHERE hb.user_id=%s AND hb.status='pending'
            ORDER BY hb.bet_date DESC
        """, (user_id,))
        bets = cursor.fetchall()
    db.close()

    if not bets:
        await update.message.reply_text("⚠️ لا توجد رهانات حالية.")
        return

    total_stake = 0
    total_potential = 0
    text = "🎲 <b>رهاناتك الحالية:</b>\n\n"
    for b in bets:
        total_stake += float(b['total_stake'])
        total_potential += float(b['potential_payout'])
        text += (
            f"🆔 رهان: {b['bet_id']}\n"
            f"🏁 سباق: {b['meeting_code']}{b['race_number']}\n"
            f"🐎 خيول: {b['bet_numbers']}\n"
            f"💰 المبلغ: {b['stake']:.2f}\n"
            f"✖️ مضاعف: {b['multiplier']}\n"
            f"💳 المجموع المحتسب: {b['total_stake']:.2f}\n"
            f"💵 عائد محتمل: {b['potential_payout']:.2f}\n"
            f"📊 الحالة: ⏳ قيد الانتظار\n\n"
        )

    text += (
        "---------------------------\n"
        f"📊 مجموع الرهانات: {total_stake:.2f}\n"
        f"💵 مجموع العوائد المحتملة: {total_potential:.2f}"
    )

    await update.message.reply_text(text, parse_mode="HTML")


# 📊 رهاناتي السابقة
async def past_bets(update: Update, context: ContextTypes.DEFAULT_TYPE):
    user_id = update.message.from_user.id
    db = get_db()
    with db.cursor() as cursor:
        cursor.execute("""
            SELECT hb.bet_id, hb.bet_numbers, hb.stake, hb.multiplier, hb.total_stake, hb.potential_payout, hb.status,
                   r.meeting_code, r.race_number, r.status AS race_status
            FROM horse_bets hb
            JOIN races r ON hb.race_id = r.race_id
            WHERE hb.user_id=%s AND hb.status IN ('won','lost','cancelled')
            ORDER BY hb.bet_date DESC
            LIMIT 10
        """, (user_id,))
        bets = cursor.fetchall()
    db.close()

    if not bets:
        await update.message.reply_text("⚠️ لا توجد رهانات سابقة.")
        return

    text = "📊 <b>رهاناتك السابقة:</b>\n\n"
    for b in bets:
        status_emoji = "✅ ربح" if b['status'] == "won" else "❌ خسارة" if b['status'] == "lost" else "🚫 ملغي"
        text += (
            f"🆔 رهان: {b['bet_id']}\n"
            f"🏁 سباق: {b['meeting_code']}{b['race_number']}\n"
            f"🐎 خيول: {b['bet_numbers']}\n"
            f"💰 المبلغ: {b['stake']:.2f}\n"
            f"✖️ مضاعف: {b['multiplier']}\n"
            f"💳 المجموع: {b['total_stake']:.2f}\n"
            f"💵 عائد محتمل: {b['potential_payout']:.2f}\n"
            f"📊 النتيجة: {status_emoji}\n\n"
        )

    await update.message.reply_text(text, parse_mode="HTML")
