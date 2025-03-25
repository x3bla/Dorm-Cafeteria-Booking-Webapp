import os
from pdf2image import convert_from_path

# Store Pdf with convert_from_path function
for pdf in os.listdir("./pdfs"):
    images = convert_from_path(os.path.join("./pdfs", pdf))
#                               poppler_path=r"C:\Users\xxx\Downloads\poppler-24.08.0\Library\bin")

    for i in range(len(images)):
        images[i].save(f'menus/{pdf.title()}-{i}.png', 'PNG')

