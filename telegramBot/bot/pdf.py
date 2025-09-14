import sys, re, pdfplumber, pymysql, config, json

pdf_file = sys.argv[1]

# اتصال
db = pymysql.connect(
    host=config.DB_HOST,
    user=config.DB_USER,
    password=config.DB_PASS,
    database=config.DB_NAME,
    cursorclass=pymysql.cursors.DictCursor
)
cursor = db.cursor()

with pdfplumber.open(pdf_file) as pdf:
    text = "\n".join([p.extract_text() for p in pdf.pages if p.extract_text()])

# استخراج اجتماع
meeting_match = re.search(r"Réunion\s+(\d+).+?(\d{1,2}\s\w+\s\d{4})", text)
meeting_code = f"R{meeting_match.group(1)}" if meeting_match else "R?"
meeting_date = meeting_match.group(2) if meeting_match else None
location = "Settat"

# استخراج سباقات
races = re.findall(r"(C\d+)\s+-\s+(.+?)\((\d{2}:\d{2})\s*/\s*(\d+)m\s*/\s*([\d\.]+)\s*DH\)", text)

inserted = []

for race_number, title, time, distance, prize in races:
    cursor.execute("""
        INSERT INTO races (meeting_code, race_number, location, start_time, distance, prize, status)
        VALUES (%s, %s, %s, %s, %s, %s, 'upcoming')
    """, (meeting_code, race_number, location, f"{meeting_date} {time}", distance, prize))
    db.commit()
    race_id = cursor.lastrowid

    # TODO: استخراج أسماء الأحصنة من النص بشكل أدق
    horses = re.findall(r"\d+\s+([A-Za-z0-9\s\-']+)\s+[A-Z]+", text)
    for i, hname in enumerate(horses, start=1):
        cursor.execute("INSERT IGNORE INTO horses (horse_name) VALUES (%s)", (hname.strip(),))
        db.commit()
        cursor.execute("SELECT horse_id FROM horses WHERE horse_name=%s", (hname.strip(),))
        hid = cursor.fetchone()["horse_id"]

        cursor.execute("""
            INSERT INTO race_horses (race_id, horse_id, horse_number)
            VALUES (%s, %s, %s)
        """, (race_id, hid, i))
        db.commit()

    inserted.append({"race": race_number, "horses": len(horses)})

print(json.dumps({"status":"success","meeting":meeting_code,"races":inserted}, ensure_ascii=False))
