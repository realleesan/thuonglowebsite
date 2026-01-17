import sys
import docx
import PyPDF2
import os

def read_docx(file_path):
    doc = docx.Document(file_path)
    fullText = []
    for para in doc.paragraphs:
        fullText.append(para.text)
    return '\n'.join(fullText)

def read_pdf(file_path):
    text = ""
    try:
        with open(file_path, 'rb') as file:
            reader = PyPDF2.PdfReader(file)
            for page_num in range(len(reader.pages)):
                page = reader.pages[page_num]
                text += page.extract_text()
    except Exception as e:
        text = f"Error reading PDF: {e}"
    return text

docs_dir = r"d:\xampp\htdocs\thuongloWebsite\docs"
output_file = r"d:\xampp\htdocs\thuongloWebsite\docs\docs_content.txt"
files = [
    "JOBS THUONGLO A SINH.docx",
    "BẢO MẬT - THUONGLO.COM.docx",
    "TÁC VỤ KỸ THUẬT -BẢO MẬT - THUONGLO.COM.docx",
    "TRIỂN KHAI PJ THUONGLO_MISTYTEAM_fx_v16126.pdf"
]

with open(output_file, "w", encoding="utf-8") as out:
    for i, filename in enumerate(files, 1):
        file_path = os.path.join(docs_dir, filename)
        out.write(f"--- START DOCUMENT {i}: {filename} ---\n")
        try:
            if filename.endswith(".docx"):
                content = read_docx(file_path)
                out.write(content + "\n")
            elif filename.endswith(".pdf"):
                content = read_pdf(file_path)
                out.write(content + "\n")
            else:
                out.write("Unsupported file format.\n")
        except Exception as e:
            out.write(f"Error reading {filename}: {e}\n")
        out.write(f"--- END DOCUMENT {i}: {filename} ---\n\n")
