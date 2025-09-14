# handlers/settings.py
from telegram import Update
from telegram.ext import ContextTypes
from handlers.start import main_menu

async def change_password(update: Update, context: ContextTypes.DEFAULT_TYPE):
    await update.message.reply_text("🔑 ميزة تغيير كلمة المرور ستتم إضافتها لاحقاً.", reply_markup=main_menu())

async def change_phone(update: Update, context: ContextTypes.DEFAULT_TYPE):
    await update.message.reply_text("📞 ميزة تغيير الهاتف ستتم إضافتها لاحقاً.", reply_markup=main_menu())
