from datetime import datetime, timedelta
import json
import os
import re  # TODO: better OCR for numbers, and maybe remove re

import cv2
import easyocr
import numpy as np
import pytesseract
from matplotlib import pyplot as plt

# easy ocr
reader = easyocr.Reader(['ja'])


def showimg(image):
    # Displaying the image
    pixel_width = 1655
    pixel_height = 2340
    dpi = 100

    # Calculate figure size in inches becuz matplotlib hates just specifying pixel sizes
    fig_width = pixel_width / dpi
    fig_height = pixel_height / dpi

    # Create the figure and display the image
    fig, ax = plt.subplots(figsize=(fig_width, fig_height), dpi=dpi)
    ax.imshow(image)
    ax.axis('off')  # Turn off the axes for better visualization
    plt.show()


def showcontours(contours, x=2340, y=1655):
    blank_image = np.zeros((x, y, 3), np.uint8)  # blank black img for seeing contours
    img_contours = cv2.drawContours(blank_image, contours, -1, (0, 255, 0), 2)
    showimg(img_contours)


def cleanimg(img, brightness_threshold=221):
    # cropping and thresholding
    threshold = 20  # for grayscale like pixels, (if RGB is same of similar enough to each other, keep it)

    # Convert the image to float for precise calculations
    image_float = img.astype(np.float32)

    # Calculate the absolute differences between R, G, and B channels
    diff_rg = np.abs(image_float[:, :, 2] - image_float[:, :, 1])  # Difference between R and G
    diff_rb = np.abs(image_float[:, :, 2] - image_float[:, :, 0])  # Difference between R and B
    diff_gb = np.abs(image_float[:, :, 1] - image_float[:, :, 0])  # Difference between G and B

    # Creating mask for grayscale-like pixels
    grayscale_mask = (
            (diff_rg < threshold) &  # pixel is grayscale-like
            (diff_rb < threshold) &
            (diff_gb < threshold) &
            (image_float[:, :, 0] < brightness_threshold) &  # RGB below brightness thresh
            (image_float[:, :, 1] < brightness_threshold) &
            (image_float[:, :, 2] < brightness_threshold)
    )

    cleaned = np.ones_like(img) * 255  # White image
    cleaned[grayscale_mask] = img[grayscale_mask]  # add mask to white img
    return cleaned


def splitdish(img):  # split crop image into 3, dish name, cal and stuff, allergies
    # getting contours of img
    gray_crop = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    ret, thresh1 = cv2.threshold(gray_crop, 250, 255, cv2.THRESH_OTSU)

    threshold1 = 50  # affects... ???
    threshold2 = 150  # affects... ???
    edges = cv2.Canny(thresh1, threshold1, threshold2)

    blank_image = np.zeros((img.shape[0], img.shape[1], 3), np.uint8)  # blank black img for seeing contours
    crop_contours, hierarchy = cv2.findContours(edges, cv2.RETR_TREE, cv2.CHAIN_APPROX_NONE)
    crop_contours_img = cv2.drawContours(blank_image, crop_contours, -1, (0, 255, 0), 2)

    number_top = 0
    number_bottom = 0
    # looping through to find area to split
    for i in range(img.shape[0] - 1, -1, -1):  # -1 to loop from bottom of img to up
        sum = 0
        for j in range(0, img.shape[1]):
            sum += crop_contours_img[i][j][1] / 255  # going row by row

        if sum > 105:
            number_top = i - 36  # since allergy keeps getting detected, use -36 and -22 to get the nutrition coords
            # cv2.line(cleaned, (0, i-22), (202, i-22), (0, 255, 0))  # line below numbers
            number_bottom = i - 22
            break
    return number_top, number_bottom


def splitnutrition(img):
    xcut = []
    for j in range(img.shape[1] - 1, -1, -1):
        check = True
        for i in range(img.shape[0]):
            if img[i][j][1] != 255:
                check = False
                break
        if check:
            xcut.append(j)

    # another loop with funni logic that works, could be better if list is [201, 200, 193, 170, 169, 168, 167, 166,
    # 165, 164, 163, 139, 127, 126, 125, 124, 103, 88, 87, 86, 85, 84, 48, 47, 46, 45, 44, 8, 7, 6, 4, 3, 2,
    # 1] we are trying to rule out the weird 103, 139, 170, 193, 200, 201, by checking if there's at least 3
    # consecutive columns of white
    numbers = sorted(set(xcut))  # Sort in ascending order and remove duplicates if any
    xcut = []

    i = 0
    while i < len(numbers):
        group = [numbers[i]]

        # Find consecutive sequences
        while i + 1 < len(numbers) and numbers[i] + 1 == numbers[i + 1]:
            group.append(numbers[i + 1])
            i += 1

        # If the group has 3 or more consecutive numbers, keep only the median
        if len(group) >= 3:
            median_value = group[len(group) // 2]  # Find median whole number
            if median_value >= 5:  # Keep only numbers >= 30
                xcut.append(median_value)

        i += 1

    # adding another line for the start and end of the lines
    real_cut = xcut
    if xcut[0] > 10:
        real_cut.insert(0, 0)
    if xcut[-1] < (img.shape[1] - 10):
        real_cut.append(img.shape[1] - 1)

    # finally, splitting the img into their respective nutrients
    kcal_img = img[0:img.shape[0], real_cut[0]:real_cut[1]]
    protein_img = img[0:img.shape[0], real_cut[1]:real_cut[2]]
    fats_img = img[0:img.shape[0], real_cut[2]:real_cut[3]]
    carb_img = img[0:img.shape[0], real_cut[3]:real_cut[4]]
    salt_img = img[0:img.shape[0], real_cut[4]:real_cut[5]]

    return kcal_img, protein_img, fats_img, carb_img, salt_img


def dishOCR(crop):
    # removing very light gray and other noise
    cleaned = cleanimg(crop)  # TODO: maybe can clean the whole image before cropping
    number_top, number_bottom = splitdish(cleaned)  # split crop image into 3, dish name, cal and stuff, allergies

    if number_top == 0 and number_bottom == 0:  # if can't detect allergy bar, most likely blank
        return [""], None, None

    cleaned_dish_name = cleaned[0:number_top, 0:cleaned.shape[1]]
    cleaned_cal_count = cleaned[number_top:number_bottom, 0:cleaned.shape[1]]
    cleaned_allergy = cleaned[number_bottom:cleaned.shape[0], 0:cleaned.shape[1]]

    final_dish_name = [""]
    final_calories = None
    final_allergy = None  # TODO: detect allergy

    final_dish_name = reader.readtext(cleaned_dish_name, detail=0)
    # final_dish_name = "\n".join(final_dish_name)

    if final_dish_name == "":  # if no dish name, probably blank
        return [""], None, None

    # TODO: find a better OCR that works with individual images
    # kcal_img, protein_img, fats_img, carb_img, salt_img = splitnutrition(cleaned_cal_count)
    # easyocr = reader.readtext(kcal_img, detail=0, allowlist="0123456789kcal")
    pytess = pytesseract.image_to_string(cleaned_cal_count, config="-c tessedit_char_whitelist=0123456789kcal.")
    final_calories = re.search(r"([0-9]*)k?c?a?l?", pytess)[1]

    return final_dish_name, final_calories, final_allergy


def createdb(data, date):
    data.append(
        {
            "date": date,
            "meal_time":
                {
                    "breakfast": {
                        "japanese": {
                            "name": [],
                            "allergy": [],
                            "calories": ""
                        },
                        "western": {
                            "name": [],
                            "allergy": [],
                            "calories": ""
                        },
                        "plate_sides": [
                            {
                                "name": [],
                                "allergy": [],
                                "calories": ""
                            },
                            {
                                "name": [],
                                "allergy": [],
                                "calories": ""
                            }
                        ],
                        "bowl_sides": [
                            {
                                "name": [],
                                "allergy": [],
                                "calories": ""
                            },
                            {
                                "name": [],
                                "allergy": [],
                                "calories": ""
                            },
                            {
                                "name": [],
                                "allergy": [],
                                "calories": ""
                            },
                            {
                                "name": [],
                                "allergy": [],
                                "calories": ""
                            }
                        ]
                    },
                    "lunch": {
                        "lunch_sides": {
                            "name": [],
                            "allergy": [],
                            "calories": ""
                        },
                        "don_set": {
                            "name": [],
                            "allergy": [],
                            "calories": ""
                        },
                        "noodle_set": {
                            "name": [],
                            "allergy": [],
                            "calories": ""
                        }
                    },
                    "dinner": {
                        "dinner_sides": {
                            "name": [],
                            "allergy": [],
                            "calories": ""
                        },
                        "don_or_noodle_set": {
                            "name": [],
                            "allergy": [],
                            "calories": ""
                        },
                        "dessert": {
                            "name": [],
                            "allergy": [],
                            "calories": ""
                        }
                    }
                }
        }
    )

    return data

def correctdates(year, initial_dates):
    corrected_dates = []

    for i in range(len(initial_dates)):
        # month_day = initial_dates[i][3:]  # will break if ocr didn't detect bracket or day
        date = re.search(r"【?.?】?(\d+\.\d+)", initial_dates[i])

        try:
            month_day = datetime.strptime(f"{year}.{date.group(1)}", "%Y.%m.%d")
        except ValueError:  # invalid date
            month_day = ""
        except AttributeError:  # regex found nothing
            month_day = ""

        if month_day != "" and month_day.weekday() != i:
            month_day = ""
        corrected_dates.append(month_day)

    print("before correcting:", corrected_dates)

    for i in range(len(corrected_dates)):
        if corrected_dates[i] != "":
            if i == 0:
                for j in range(1, 7):
                    corrected_dates[j] = corrected_dates[i] + timedelta(days=j)
            elif i == 6:
                for j in range(5, -1, -1):
                    corrected_dates[j] = corrected_dates[i] - timedelta(days=abs(j-6))
            else:
                for j in range(i+1, 7):
                    corrected_dates[j] = corrected_dates[i] + timedelta(days=j-i)

                for j in range(i-1, -1, -1):
                    corrected_dates[j] = corrected_dates[i] - timedelta(days=abs(j-i))
            break

    print("after correcting:", corrected_dates)
    return [x.strftime("%Y-%m-%d") for x in corrected_dates]

def insert_to_dict(dict, dish_name: list, cal: int, allergies: list):
    for dish in dish_name:
        dict["name"].append(dish)

    if allergies is not None:
        for allergy in allergies:
            dict["allergy"].append(allergy)

    if cal is None:
        dict["calories"] = ""
    else:
        dict["calories"] = cal

    return dict


def main(png):
    # Load the image
    image_path = png
    image = cv2.imread(image_path)
    data = []
    result_date = []

    # Convert to grayscale and get the largest contour
    gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
    ret, thresh = cv2.threshold(gray, 251, 255, cv2.THRESH_OTSU)
    contours, _ = cv2.findContours(thresh, cv2.RETR_LIST, cv2.CHAIN_APPROX_SIMPLE)
    largest_contour = None

    # Finding the "Weekly Menu"'s big contour on top
    top_contour = [(contour.astype(int)) for contour in contours]
    top_contour.reverse()  # contour starts from bottom up, changing to top down
    for contour in range(1, len(top_contour)):
        # skip first few cuz it might be an outline of the whole image, literally, print it out
        if cv2.contourArea(top_contour[contour]) > 7000:  # Check contour area
            largest_contour = top_contour[contour]
            # showcontours(largest_contour)
            break

    # Get angle for rotation
    rect = cv2.minAreaRect(largest_contour)
    angle = rect[-1]

    # Sometimes angle might be too huge
    if angle > 10:
        angle -= 90
    elif angle < -10:
        angle += 90

    # very hacky
    # if angle is still too big, try get angle from largest contour instead
    if -1 > angle or angle > 1:
        largest_contour = max(contours, key=cv2.contourArea)
        rect = cv2.minAreaRect(largest_contour)
        angle = rect[-1]

        if angle > 10:
            angle -= 90
        elif angle < -10:
            angle += 90

        if -1 > angle or angle > 1:
            print("this image can't be straightened:", image_path)
            angle = 0  # disables the angle completely

    # Rotate the image
    (h, w) = image.shape[:2]
    center = (w // 2, h // 2)
    rotation_matrix = cv2.getRotationMatrix2D(center, angle, 1.0)
    straightened = cv2.warpAffine(image, rotation_matrix, (w, h), flags=cv2.INTER_CUBIC,
                                  borderMode=cv2.BORDER_REPLICATE)

    straightened_gray = cv2.cvtColor(straightened, cv2.COLOR_BGR2GRAY)
    ret, thresh1 = cv2.threshold(straightened_gray, 250, 255, cv2.THRESH_OTSU)

    # draw contours on a black background
    blank_image = np.zeros((2340, 1655, 3), np.uint8)  # blank black img for seeing contours
    contours, hierarchy = cv2.findContours(thresh1, cv2.RETR_TREE, cv2.CHAIN_APPROX_NONE)
    img_contours = cv2.drawContours(blank_image, contours, -1, (0, 255, 0), 2)

    # splitting dishes into their own cells
    xarr = []
    yarr = []

    # goes through the x-axis
    # start from 5 cuz otsu threseholding has a whole outline around the image idk why
    for i in range(5, img_contours.shape[1]):  # start at 5 cuz first few is an outline of the whole image, idk why
        # print(img_contours[i][100][1])
        sum = 0
        for j in range(220, 250):
            sum += img_contours[j][i][1] / 255
        # print(i, sum)  # i = pixel, sum = amount of countour pixels (detected lines)

        if sum > 3:  # filter for vertical line
            xarr.append(i)
            xdiff = [164, 365, 567, 770, 972, 1175, 1377, 1579]
            xarr += [j + i for j in xdiff]
            break

    # goes through the y-axis
    for i in range(5, img_contours.shape[0]):
        # print(img_contours[i][100][1])
        sum = 0
        for j in range(50, 181):
            sum += img_contours[i][j][1] / 255
        # print(i, sum)

        if sum > 30:  # filter for horizontal line
            yarr.append(i)
            ydiff = [50, 92, 197, 301, 413, 513, 613, 713, 813, 913, 956, 1139, 1322, 1506, 1548, 1734, 1920, 2024]
            yarr += [j + i for j in ydiff]
            break

    # creating format for data dictionary
    for i in range(1, len(xarr) - 1):
        date_crop = straightened[yarr[0]:yarr[1], xarr[i]:xarr[i + 1]]
        date = cleanimg(date_crop, 250)

        try:
            date = cv2.cvtColor(date, cv2.COLOR_BGR2GRAY)
            ret, date = cv2.threshold(date, 250, 255, cv2.THRESH_OTSU)
            # removing bar and weird specks
            contours, _ = cv2.findContours(date, cv2.RETR_LIST, cv2.CHAIN_APPROX_SIMPLE)

            height, width, _ = date_crop.shape
            min_area = 10  # Remove tiny specks
            max_width_ratio = 0.9  # If a contour is >10% of width, it's a sidebar
            for cnt in contours:
                x, y, w, h = cv2.boundingRect(cnt)
                area = cv2.contourArea(cnt)

                # Remove large vertical side bars
                if w > width * max_width_ratio:
                    cv2.drawContours(date, [cnt], -1, (0, 0, 0), thickness=cv2.LINE_4)

                # Remove small random noise
                if area < min_area:
                    cv2.drawContours(date, [cnt], -1, (0, 0, 0), thickness=cv2.LINE_4)
        except Exception as e:
            showimg(date)
            print(e)
        result_date += reader.readtext(date, allowlist=r'0123456789.【】月火水木金土日',
                                       detail=0)  # returns a list, so += instead of append
    print(result_date)
    dates = correctdates(year, result_date)

    for date in dates:
        data = createdb(data, date)

    # looping through all dishes
    for x in range(1, len(xarr) - 1):  # vertical lines
        current = data[x - 1]["meal_time"]
        for y in range(2, len(yarr) - 1):  # horizontal lines
            # ignore time header
            if y in [10, 14]:
                continue

            # crop img, OCR for text
            crop = straightened[yarr[y]:yarr[y + 1], xarr[x]:xarr[x + 1]]  # i don't know why it's [y, x] not [x, y]
            dish_name, calories, allergy = dishOCR(crop)

            match y:
                case 2:
                    insert_to_dict(current["breakfast"]["japanese"], dish_name, calories, allergy)
                case 3:
                    insert_to_dict(current["breakfast"]["western"], dish_name, calories, allergy)
                case 4 | 5:
                    insert_to_dict(current["breakfast"]["plate_sides"][y - 4], dish_name, calories, allergy)
                case 6 | 7 | 8 | 9:
                    insert_to_dict(current["breakfast"]["bowl_sides"][y - 6], dish_name, calories, allergy)
                case 11:
                    insert_to_dict(current["lunch"]["lunch_sides"], dish_name, calories, allergy)
                case 12:
                    insert_to_dict(current["lunch"]["don_set"], dish_name, calories, allergy)
                case 13:
                    insert_to_dict(current["lunch"]["noodle_set"], dish_name, calories, allergy)
                case 15:
                    insert_to_dict(current["dinner"]["dinner_sides"], dish_name, calories, allergy)
                case 16:
                    insert_to_dict(current["dinner"]["don_or_noodle_set"], dish_name, calories, allergy)
                case 17:
                    insert_to_dict(current["dinner"]["dessert"], dish_name, calories, allergy)

    print(png, "done", data)
    return data


meal_info = []  # json to hold all, meals data/information
for year in os.listdir("menus"):
    for png in os.listdir("menus/" + year):
        meal_info += main(f"menus/{year}/{png}")
# main("menus/2_1_Jan.Pdf-2.png")

with open("output/data.json", "w", encoding="utf8") as f:
    json.dump(meal_info, indent=2, fp=f, ensure_ascii=False)
