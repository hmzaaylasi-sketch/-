# db.py
import pymysql
import config

def get_db():
    return pymysql.connect(
        host=config.DB_HOST,
        user=config.DB_USER,
        password=config.DB_PASS,
        database=config.DB_NAME,
        cursorclass=pymysql.cursors.DictCursor  # 🟢 مهم
    )
