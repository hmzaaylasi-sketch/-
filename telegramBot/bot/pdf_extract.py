import sys, re, json
import pdfplumber

file_path = sys.argv[1]

text = ""
with pdfplumber.open(file_path) as pdf:
    for page in pdf.pages:
        text += page.extract_text() + "\n"

# 🟢 استخراج الاجتماع
meeting_match = re.search(r"Réunion\s+(\d+).+?(\d{1,2}\s\w+\s\d{4})", text)
meeting_code = f"R{meeting_match.group(1)}" if meeting_match else "R?"
meeting_date = meeting_match.group(2) if meeting_match else "??"
location = "Settat"  # لاحقا نقرأ من النص

# 🟢 استخراج السباقات
races_raw = re.findall(r"(C\d+)\s+-\s+(.+?)\((\d{2}:\d{2})\s*/\s*(\d+)m\s*/\s*([\d\.]+)\s*DH\)", text)

races = []
for race_number, title, time, distance, prize in races_raw:
    # 🐎 استخراج أسماء الأحصنة (تحتاج تحسين regex حسب شكل PDF)
    horses = re.findall(r"\d+\s+([A-Za-z0-9\s\-']+)", text)

    races.append({
        "meeting_code": meeting_code,
        "race_number": race_number,
        "title": title.strip(),
        "time": time,
        "distance": distance,
        "prize": prize,
        "location": location,
        "horses": horses
    })

print(json.dumps({"races": races}, ensure_ascii=False))
