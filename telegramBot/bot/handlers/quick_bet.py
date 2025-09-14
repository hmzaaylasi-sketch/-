# handlers/quick_bet.py
import re
import logging
from telegram import Update
from telegram.ext import ContextTypes
from db import get_db
from handlers.start import main_menu

logger = logging.getLogger(__name__)

async def quick_bet(update: Update, context: ContextTypes.DEFAULT_TYPE):
    user_id = update.message.from_user.id
    text = update.message.text.strip()

    # استخراج المضاعف xN إن وُجد (مثال: x2)
    m_mult = re.search(r'\bx(\d+)\b', text, flags=re.I)
    multiplier = int(m_mult.group(1)) if m_mult else 1
    # نحذف أي xN لتسهيل التحليل
    cleaned = re.sub(r'\bx\d+\b', '', text, flags=re.I).strip()

    parts = cleaned.split()
    if len(parts) < 2:
        await update.message.reply_text(
            "⚠️ الصيغة غير صحيحة.\nمثال: `R9C8T10 1 2 3` أو مع مضاعف `R9C8T10 1 2 3 x2`",
            parse_mode="Markdown"
        )
        return

    code = parts[0]               # مثل "R9C8T10"
    horse_tokens = parts[1:]      # بقية التوكنز المفترضة أرقام خيول

    # تحليل الكود R{n}C{m}T{stake}
    m = re.match(r'(?i)^R(\d+)C(\d+)T(\d+(?:\.\d+)?)$', code)
    if not m:
        await update.message.reply_text(
            "⚠️ صيغة الكود غير صحيحة. يجب أن تكون مثل: `R9C8T10`",
            parse_mode="Markdown"
        )
        return

    meeting = f"R{m.group(1)}"
    race_number = f"C{m.group(2)}"
    # هنا نستخدم اسم واضح: stake = المبلغ الأساسي (T20 => 20)
    stake = float(m.group(3))

    # أرقام الأحصنة الصالحة (أخذ التوكِنّات الرقمية فقط)
    horses = [int(t) for t in horse_tokens if re.fullmatch(r'\d+', t)]
    if not horses:
        await update.message.reply_text("⚠️ لم تُدخل أرقام أحصنة صالحة. مثال: `R9C8T10 1 2 3`", parse_mode="Markdown")
        return

    # حساب المبلغ الكلي بعد المضاعف (سيخصم من الرصيد)
    total_stake = stake * multiplier

    # تحديد نوع الرهان بحسب عدد الأحصنة
    if len(horses) == 3:
        bet_type = "pick3"
    elif len(horses) == 5:
        bet_type = "pick5"
    elif len(horses) == 10:
        bet_type = "pick10"
    else:
        # لو عدد غير قياسي، نأخذ pick3 كتصرّف افتراضي (لتوافق enum في DB)
        bet_type = "pick3"

    # معامل الـ pick (×3 أو ×5 أو ×10)
    pick_map = {"pick3": 3, "pick5": 5, "pick10": 10}
    pick_factor = pick_map.get(bet_type, 2)

    # العائد المحتمل = total_stake * pick_factor
    potential_payout = total_stake * pick_factor

    bet_numbers = " ".join(str(x) for x in horses)

    # ===== التعامل مع قاعدة البيانات =====
    db = get_db()
    try:
        with db.cursor() as cursor:
            # تأكد من وجود السباق في جدول races
            cursor.execute(
                "SELECT race_id, status FROM races WHERE meeting_code=%s AND race_number=%s",
                (meeting, race_number)
            )
            race = cursor.fetchone()
            if not race:
                await update.message.reply_text(f"⚠️ السباق {meeting}{race_number} غير موجود.")
                return

            if race['status'] not in ('upcoming', 'running'):
                await update.message.reply_text(f"⚠️ لا يمكن الرهان على سباق بحالة: {race['status']}")
                return

            race_id = race['race_id']

            # تحقق من أرقام الخيول لهذا السباق
            invalid = []
            for hn in horses:
                cursor.execute(
                    "SELECT race_horse_id FROM race_horses WHERE race_id=%s AND horse_number=%s",
                    (race_id, hn)
                )
                if not cursor.fetchone():
                    invalid.append(str(hn))
            if invalid:
                await update.message.reply_text("⚠️ أرقام الأحصنة التالية غير صالحة لهذا السباق: " + ", ".join(invalid))
                return

            # التحقق من رصيد المستخدم
            cursor.execute("SELECT currency FROM users WHERE user_id=%s", (user_id,))
            user = cursor.fetchone()
            if not user:
                await update.message.reply_text("⚠️ لم يتم العثور على حسابك. أرسل /start للتسجيل.")
                return
            balance = float(user['currency'])
            if balance < total_stake:
                await update.message.reply_text(f"⚠️ رصيدك غير كافٍ. رصيدك: {balance:.2f} — مطلوب: {total_stake:.2f}")
                return

            # تسجِيل الرهان: نستخدم stake (المبلغ الأساسي) و total_stake و potential_payout
            cursor.execute("""
                INSERT INTO horse_bets 
                    (user_id, race_id, bet_type, bet_numbers, stake, total_stake, potential_payout, status)
                VALUES (%s, %s, %s, %s, %s, %s, %s, 'pending')
            """, (user_id, race_id, bet_type, bet_numbers, stake, total_stake, potential_payout))

            # تحديث رصيد المستخدم بطرح total_stake
            cursor.execute("UPDATE users SET currency = currency - %s WHERE user_id = %s", (total_stake, user_id))

            # جلب الرصيد الجديد
            cursor.execute("SELECT currency FROM users WHERE user_id=%s", (user_id,))
            new_balance = cursor.fetchone()['currency']

            db.commit()

    except Exception as e:
        db.rollback()
        logger.exception("quick_bet failed")
        await update.message.reply_text(f"❌ خطأ أثناء تسجيل الرهان: {e}")
        return
    finally:
        db.close()

    # رسالة التأكيد للمستخدم (نوضّح pick factor)
    await update.message.reply_text(
        f"✅ تم تسجيل رهانك!\n\n"
        f"🏁 سباق: {meeting}{race_number}\n"
        f"🐎 أحصنة: {bet_numbers}\n"
        f"💰 المبلغ الأساسي (T): {stake:.2f} درهم\n"
        f"✖️ مضاعف: {multiplier}\n"
        f"💳 المجموع المحتسب للخصم: {total_stake:.2f} درهم\n"
        f"📈 معامل pick: ×{pick_factor}\n"
        f"💵 عائد محتمل: {potential_payout:.2f} درهم\n"
        f"💳 رصيدك الجديد: {float(new_balance):.2f} درهم",
        reply_markup=main_menu()
    )
