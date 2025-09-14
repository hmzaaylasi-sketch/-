import logging
from telegram.ext import (
    Application, CommandHandler, MessageHandler,
    filters, ConversationHandler, CallbackQueryHandler
)

# 🟢 Handlers
from handlers.start import (
    start, save_phone,
    main_menu, account_menu, races_menu, bets_menu, settings_menu
)
from handlers.races import scheduled_races, race_details, race_results, recent_results
from handlers.bets import horse_selected, save_bet, cancel, SAVE_BET
from handlers.account_actions import my_balance, my_transactions, deposit, withdraw, account_info
from handlers.settings import change_password, change_phone
from handlers.quick_bet import quick_bet
from handlers.my_bets import current_bets, past_bets  # ✅ تم التعديل هنا

import config

# 🟢 Logger
logging.basicConfig(
    format="%(asctime)s - %(name)s - %(levelname)s - %(message)s",
    level=logging.INFO
)
logger = logging.getLogger(__name__)

def main():
    app = Application.builder().token(config.TOKEN).build()

    # ======================
    # 🚀 أوامر البداية
    # ======================
    app.add_handler(CommandHandler("start", start))
    app.add_handler(MessageHandler(filters.CONTACT, save_phone))

    # ======================
    # 🎛️ القوائم بالأزرار
    # ======================
    app.add_handler(MessageHandler(filters.Regex("^📋 حسابي$"),
                                   lambda u, c: u.message.reply_text("📋 حسابك:", reply_markup=account_menu())))
    app.add_handler(MessageHandler(filters.Regex("^🏇 السباقات$"),
                                   lambda u, c: u.message.reply_text("🏇 السباقات:", reply_markup=races_menu())))
    app.add_handler(MessageHandler(filters.Regex("^🎲 رهاناتي$"),
                                   lambda u, c: u.message.reply_text("🎲 رهاناتك:", reply_markup=bets_menu())))
    app.add_handler(MessageHandler(filters.Regex("^⚙️ الإعدادات$"),
                                   lambda u, c: u.message.reply_text("⚙️ الإعدادات:", reply_markup=settings_menu())))
    app.add_handler(MessageHandler(filters.Regex("^⬅️ رجوع$"),
                                   lambda u, c: u.message.reply_text("⬅️ رجعت للقائمة:", reply_markup=main_menu())))

    # ======================
    # 💰 الحساب المالي
    # ======================
    app.add_handler(MessageHandler(filters.Regex("^💳 رصيدي$"), my_balance))
    app.add_handler(MessageHandler(filters.Regex("^📜 سجل العمليات$"), my_transactions))
    app.add_handler(MessageHandler(filters.Regex("^📥 إيداع$"), deposit))
    app.add_handler(MessageHandler(filters.Regex("^💸 سحب$"), withdraw))
    app.add_handler(MessageHandler(filters.Regex("^معلومات حسابي$"), account_info))

    # ======================
    # 🏇 السباقات
    # ======================
    app.add_handler(MessageHandler(filters.Regex("^📅 السباقات القادمة$"), scheduled_races))
    app.add_handler(MessageHandler(filters.Regex("^🏆 النتائج$"), race_results))
    app.add_handler(MessageHandler(filters.Regex("^📊 نتائج آخر 24 ساعة$"), recent_results))

    # ✅ تفاصيل سباق عند كتابة /R1C2
    app.add_handler(MessageHandler(filters.Regex(r"^/R\d+C\d+$"), race_details))

    # ✅ رهان سريع — يقبل: R9C8T10 1 2 3   أو  R9C8T10 1 2 3 x2
    app.add_handler(MessageHandler(filters.Regex(r"(?i)^R\d+C\d+T\d+(?:\.\d+)?(?:\s+[0-9]+)+(?:\s+x\d+)?$"), quick_bet))

    # ======================
    # 🎲 رهاناتي
    # ======================
    app.add_handler(MessageHandler(filters.Regex("^🎲 رهاني الحالي$"), current_bets))
    app.add_handler(MessageHandler(filters.Regex("^📊 رهاناتي السابقة$"), past_bets))

    # ======================
    # 🎲 محادثة الرهان التقليدية
    # ======================
    conv = ConversationHandler(
        entry_points=[CallbackQueryHandler(horse_selected, pattern="^horse_")],
        states={SAVE_BET: [MessageHandler(filters.TEXT & ~filters.COMMAND, save_bet)]},
        fallbacks=[CommandHandler("cancel", cancel)]
    )
    app.add_handler(conv)

    # ======================
    # ⚙️ الإعدادات
    # ======================
    app.add_handler(MessageHandler(filters.Regex("^🔑 تغيير كلمة المرور$"), change_password))
    app.add_handler(MessageHandler(filters.Regex("^📞 تغيير الهاتف$"), change_phone))

    # ======================
    # 🚀 تشغيل البوت
    # ======================
    logger.info("🤖 Bot started...")
    app.run_polling()

if __name__ == "__main__":
    main()
