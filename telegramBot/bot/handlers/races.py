from telegram import Update
from telegram.ext import ContextTypes
from db import get_db

# ======================
# 📅 عرض السباقات الحالية والمجدولة
# ======================
async def scheduled_races(update: Update, context: ContextTypes.DEFAULT_TYPE):
    db = get_db()
    with db.cursor() as cursor:
        cursor.execute("""
            SELECT r.race_id, r.meeting_code, r.race_number, r.location, r.start_time, r.status,
                   COUNT(rh.horse_id) AS horse_count
            FROM races r
            LEFT JOIN race_horses rh ON r.race_id = rh.race_id
            WHERE r.status IN ('upcoming','running')
            GROUP BY r.race_id
            ORDER BY r.meeting_code, r.start_time
        """)
        races = cursor.fetchall()
    db.close()

    if not races:
        await update.message.reply_text("❌ لا توجد سباقات مجدولة حالياً.")
        return

    grouped = {}
    for r in races:
        grouped.setdefault(r['meeting_code'], []).append(r)

    text = "📅 <b>السباقات الحالية والمجدولة:</b>\n\n"
    for meet, rs in grouped.items():
        text += f"<b>{meet}:</b>\n"
        for r in rs:
            text += (
                f"{r['race_number']} | {r['location']} | "
                f"{r['start_time'].strftime('%d/%m/%Y %H:%M')} | 🐎 {r['horse_count']} "
                f"/{meet}{r['race_number']}\n"
            )
        text += "\n"

    await update.message.reply_text(text, parse_mode="HTML")

# ======================
# 📋 تفاصيل سباق محدد
# ======================
async def race_details(update: Update, context: ContextTypes.DEFAULT_TYPE):
    cmd = update.message.text.strip().lstrip("/")  # مثال R1C2
    db = get_db()
    with db.cursor() as cursor:
        cursor.execute("""
            SELECT r.race_id, r.meeting_code, r.race_number, r.location, r.start_time, r.status
            FROM races r
            WHERE CONCAT(r.meeting_code, r.race_number) = %s
        """, (cmd,))
        race = cursor.fetchone()

        if not race:
            await update.message.reply_text("⚠️ السباق غير موجود.")
            db.close()
            return

        cursor.execute("""
            SELECT rh.horse_number, h.horse_name
            FROM race_horses rh
            JOIN horses h ON rh.horse_id = h.horse_id
            WHERE rh.race_id = %s
            ORDER BY rh.horse_number
        """, (race['race_id'],))
        horses = cursor.fetchall()
    db.close()

    text = (
        f"🏆 <b>{race['meeting_code']}{race['race_number']}</b>\n"
        f"📍 {race['location']}\n"
        f"⏰ {race['start_time'].strftime('%d/%m/%Y %H:%M')}\n"
        f"🐎 عدد الأحصنة: {len(horses)}\n"
        f"📊 الحالة: {race['status']}\n\n"
    )

    if horses:
        text += "📋 <b>قائمة الأحصنة:</b>\n"
        for h in horses:
            text += f"#{h['horse_number']} - {h['horse_name']}\n"
    else:
        text += "❌ لا توجد أحصنة مسجلة."

    await update.message.reply_text(text, parse_mode="HTML")

# ======================
# 🏆 جميع النتائج
# ======================
async def race_results(update: Update, context: ContextTypes.DEFAULT_TYPE):
    db = get_db()
    with db.cursor() as cursor:
        cursor.execute("""
            SELECT r.race_id, r.meeting_code, r.race_number, r.location, r.start_time,
                   rr.final_order
            FROM races r
            JOIN race_results rr ON r.race_id = rr.race_id
            WHERE r.status = 'finished'
            ORDER BY r.start_time DESC
            LIMIT 10
        """)
        results = cursor.fetchall()
    db.close()

    if not results:
        await update.message.reply_text("❌ لا توجد نتائج متاحة.")
        return

    text = "🏆 <b>آخر النتائج:</b>\n\n"
    for r in results:
        text += (
            f"{r['meeting_code']}{r['race_number']} | {r['location']} | "
            f"{r['start_time'].strftime('%d/%m/%Y %H:%M')}\n"
            f"🥇 الترتيب: <code>{r['final_order']}</code>\n\n"
        )

    await update.message.reply_text(text, parse_mode="HTML")

# ======================
# 📊 نتائج آخر 24 ساعة
# ======================
async def recent_results(update: Update, context: ContextTypes.DEFAULT_TYPE):
    db = get_db()
    with db.cursor() as cursor:
        cursor.execute("""
            SELECT r.race_id, r.meeting_code, r.race_number, r.location, r.start_time,
                   rr.final_order
            FROM races r
            JOIN race_results rr ON r.race_id = rr.race_id
            WHERE r.status = 'finished'
              AND r.start_time >= NOW() - INTERVAL 24 HOUR
            ORDER BY r.start_time DESC
        """)
        results = cursor.fetchall()
    db.close()

    if not results:
        await update.message.reply_text("❌ لا توجد نتائج لآخر 24 ساعة.")
        return

    text = "📊 <b>نتائج آخر 24 ساعة:</b>\n\n"
    for r in results:
        text += (
            f"{r['meeting_code']}{r['race_number']} | {r['location']} | "
            f"{r['start_time'].strftime('%d/%m/%Y %H:%M')}\n"
            f"🥇 الترتيب: <code>{r['final_order']}</code>\n\n"
        )

    await update.message.reply_text(text, parse_mode="HTML")
