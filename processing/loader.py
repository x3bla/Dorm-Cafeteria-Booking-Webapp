import datetime
import json
import mariadb


with open("sampledata.json", encoding="utf-8") as f:
    menu = json.load(f)


def create_sql(date, day_of_week, meal_time, meal_type, dish):
    if type(dish["name"]) == list:
        name = "\\n".join(dish["name"])
    allergy = dish["allergy"]
    calories = dish["calories"] if dish["calories"] != "" else 0

    # allergen spam
    allergen_shrimp = 1 if "えび" in allergy else 0
    allergen_crab = 1 if "かに" in allergy else 0
    allergen_wheat = 1 if "小麦" in allergy else 0
    allergen_soba = 1 if "そば" in allergy else 0
    allergen_egg = 1 if "卵" in allergy else 0
    allergen_dairy = 1 if "乳" in allergy else 0
    allergen_peanuts = 1 if "落花生" in allergy else 0

    sql.append(f"INSERT INTO "
               f"weekly_menu(date, day_of_week, meal_time, meal_type, dish, calories, allergen_shrimp, allergen_crab, allergen_wheat, allergen_soba, allergen_egg, allergen_dairy, allergen_peanuts, is_available) "
               f"VALUES('{date}', {day_of_week}, '{meal_time}', '{meal_type}', '{name}', {calories}, {allergen_shrimp}, {allergen_crab}, {allergen_wheat}, {allergen_soba}, {allergen_egg}, {allergen_dairy}, {allergen_peanuts}, 1);")
    # print("sql", sql)


sql = []
for day in menu:  # date, meal_time
    day = dict(day)
    date = day["date"]
    day_of_week = datetime.datetime.strptime(
        date, '%Y-%m-%d').weekday() + 1  # sql is using enum, sql enum starts from 1

    for meal_time in day["meal_time"].keys():  # breakfast lunch dinner
        for meal_type in day["meal_time"][meal_time].keys():  # japanese, western, sides, don, noodles, set, etc
            # print("type", day["meal_time"][meal_time][meal_type])  # expecting dict with name, allergy, cal
            if type(day["meal_time"][meal_time][meal_type]) == list:  # if plate sides or bowl sides which are lists
                for dish in day["meal_time"][meal_time][meal_type]:
                    # print("dish", dish)
                    create_sql(date, day_of_week, meal_time, meal_type, dish)

            else:
                create_sql(date, day_of_week, meal_time, meal_type, day["meal_time"][meal_time][meal_type])

# print(sql)

try:
    conn = mariadb.connect(
        host="localhost",
        user="root",
        password="",
        database="student_meals_db"
    )
    cursor = conn.cursor()

    # Execute statements
    for insert in sql:
        print(insert)
        cursor.execute(insert)
    conn.commit()
    print("Successfully inserted all records!")

except mariadb.Error as e:
    print(f"Error: {e}")
    conn.rollback()

